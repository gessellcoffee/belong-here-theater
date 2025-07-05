<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Locations;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
        $this->middleware('auth');
    }

    /**
     * Upload media for a model.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // Log that we've entered the upload method
        Log::info('MediaController: upload method called', [
            'request_data' => $request->except(['file']),
            'has_file' => $request->hasFile('file'),
            'user_id' => Auth::id()
        ]);
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
            'model_type' => 'required|string|in:user,company,location',
            'model_id' => 'required|integer',
            'collection_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('MediaController: validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            Log::info('MediaController: getting model', [
                'model_type' => $request->model_type,
                'model_id' => $request->model_id
            ]);
            
            $model = $this->getModel($request->model_type, $request->model_id);
            
            if (!$model) {
                Log::warning('MediaController: model not found', [
                    'model_type' => $request->model_type,
                    'model_id' => $request->model_id
                ]);
                return response()->json(['error' => 'Model not found'], 404);
            }

            // Check permissions
            Log::info('MediaController: checking permissions');
            if (!$this->canUploadMedia($model)) {
                Log::warning('MediaController: unauthorized upload attempt', [
                    'user_id' => Auth::id(),
                    'model_type' => $request->model_type,
                    'model_id' => $request->model_id
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            Log::info('MediaController: calling mediaService->uploadMedia', [
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'collection_name' => $request->collection_name
            ]);
            
            $media = $this->mediaService->uploadMedia(
                $model,
                $request->file('file'),
                $request->collection_name,
                $request->input('custom_properties', [])
            );

            Log::info('MediaController: media uploaded successfully', [
                'media_id' => $media->id,
                'file_name' => $media->file_name
            ]);
            
            return response()->json([
                'message' => 'Media uploaded successfully',
                'media' => [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'url' => $media->url,
                    'mime_type' => $media->mime_type,
                    'collection_name' => $media->collection_name,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('MediaController: exception during upload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'model_type' => $request->model_type ?? null,
                'model_id' => $request->model_id ?? null
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete media.
     *
     * @param Media $media
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Media $media)
    {
        try {
            // Check permissions
            if (!$this->canDeleteMedia($media)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $this->mediaService->deleteMedia($media);

            return response()->json(['message' => 'Media deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all media for a model.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_type' => 'required|string|in:user,company,location',
            'model_id' => 'required|integer',
            'collection_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $model = $this->getModel($request->model_type, $request->model_id);
            
            if (!$model) {
                return response()->json(['error' => 'Model not found'], 404);
            }

            // Check permissions
            if (!$this->canViewMedia($model)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $query = $model->media();
            
            if ($request->has('collection_name')) {
                $query->where('collection_name', $request->collection_name);
            }

            $media = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'file_name' => $item->file_name,
                    'url' => $item->url,
                    'mime_type' => $item->mime_type,
                    'collection_name' => $item->collection_name,
                    'created_at' => $item->created_at,
                ];
            });

            return response()->json(['media' => $media]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the model instance based on type and ID.
     *
     * @param string $modelType
     * @param int $modelId
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getModel(string $modelType, int $modelId)
    {
        switch ($modelType) {
            case 'user':
                return User::find($modelId);
            case 'company':
                return Company::find($modelId);
            case 'location':
                return Locations::find($modelId);
            default:
                return null;
        }
    }

    /**
     * Check if the authenticated user can upload media to the model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    protected function canUploadMedia($model)
    {
        $user = Auth::user();

        // Admin can upload to any model
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can upload to their own profile
        if ($model instanceof User && $model->id === $user->id) {
            return true;
        }

        // Company owners/admins can upload to their company
        if ($model instanceof Company) {
            // Check if user is associated with the company
            return $model->users()->where('user_id', $user->id)->exists();
        }

        // Location permissions - this would depend on your business logic
        if ($model instanceof Locations) {
            // For example, if the user is associated with a company at this location
            return $model->companies()
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Check if the authenticated user can delete the media.
     *
     * @param Media $media
     * @return bool
     */
    protected function canDeleteMedia(Media $media)
    {
        $user = Auth::user();
        $model = $media->mediable;

        // Admin can delete any media
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can delete their own media
        if ($model instanceof User && $model->id === $user->id) {
            return true;
        }

        // Company owners/admins can delete company media
        if ($model instanceof Company) {
            return $model->users()->where('user_id', $user->id)->exists();
        }

        // Location permissions
        if ($model instanceof Locations) {
            return $model->companies()
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Check if the authenticated user can view media for the model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    protected function canViewMedia($model)
    {
        // In this example, we're allowing anyone to view media
        // You can implement more restrictive permissions as needed
        return true;
    }
}
