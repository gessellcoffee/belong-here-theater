<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasMedia
{
    /**
     * Get all media for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get media from a specific collection.
     *
     * @param string $collectionName
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMedia(string $collectionName = null)
    {
        $query = $this->media();
        
        if ($collectionName) {
            $query->where('collection_name', $collectionName);
        }
        
        return $query->get();
    }

    /**
     * Add a media file to this model.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $collectionName
     * @param array $customProperties
     * @return \App\Models\Media
     */
    public function addMedia(UploadedFile $file, string $collectionName = null, array $customProperties = [])
    {
        try {
            Log::info('Starting media upload process', [
                'model_type' => get_class($this),
                'model_id' => $this->getKey(),
                'file_name' => $file->getClientOriginalName(),
                'collection_name' => $collectionName
            ]);
            
            $fileName = $file->getClientOriginalName();
            $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeFileName = Str::slug($fileNameWithoutExtension) . '_' . time() . '.' . $extension;
            
            // Define the path where the file will be stored
            $path = $this->getMediaDirectory() . '/' . $safeFileName;
            Log::info('File path generated', ['path' => $path]);
            
            // Store the file
            $disk = config('filesystems.default', 'public');
            Log::info('Using storage disk', ['disk' => $disk]);
            
            try {
                Storage::disk($disk)->put($path, file_get_contents($file));
                Log::info('File stored successfully on disk', ['disk' => $disk, 'path' => $path]);
            } catch (\Exception $e) {
                Log::error('Failed to store file on disk', [
                    'disk' => $disk,
                    'path' => $path,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
            // Create the media record
            try {
                Log::info('Creating media record in database', [
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'disk' => $disk,
                    'file_size' => $file->getSize(),
                    'collection_name' => $collectionName
                ]);
                
                $media = $this->media()->create([
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'disk' => $disk,
                    'file_size' => $file->getSize(),
                    'collection_name' => $collectionName,
                    'custom_properties' => $customProperties,
                ]);
                
                Log::info('Media record created successfully', [
                    'media_id' => $media->id,
                    'model_type' => get_class($this),
                    'model_id' => $this->getKey()
                ]);
                
                return $media;
            } catch (\Exception $e) {
                Log::error('Failed to create media record in database', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // If we failed to create the database record, attempt to clean up the file
                try {
                    Storage::disk($disk)->delete($path);
                    Log::info('Cleaned up file after database record creation failure', ['path' => $path]);
                } catch (\Exception $cleanupException) {
                    Log::error('Failed to clean up file after database record creation failure', [
                        'path' => $path,
                        'error' => $cleanupException->getMessage()
                    ]);
                }
                
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Uncaught exception in addMedia method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get the directory where media files for this model should be stored.
     *
     * @return string
     */
    protected function getMediaDirectory(): string
    {
        return 'media/' . Str::plural(Str::snake(class_basename($this))) . '/' . $this->getKey();
    }

    /**
     * Delete all media in a collection.
     *
     * @param string|null $collectionName
     * @return void
     */
    public function clearMediaCollection(string $collectionName = null)
    {
        $query = $this->media();
        
        if ($collectionName) {
            $query->where('collection_name', $collectionName);
        }
        
        $query->get()->each->delete();
    }
}
