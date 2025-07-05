<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Upload a file and attach it to a model.
     *
     * @param Model $model
     * @param UploadedFile $file
     * @param string|null $collectionName
     * @param array $customProperties
     * @return Media
     */
    public function uploadMedia(Model $model, UploadedFile $file, string $collectionName = null, array $customProperties = [])
    {
        if (!method_exists($model, 'addMedia')) {
            throw new \Exception('Model does not use HasMedia trait');
        }
        
        return $model->addMedia($file, $collectionName, $customProperties);
    }
    
    /**
     * Delete a media item.
     *
     * @param Media $media
     * @return bool
     */
    public function deleteMedia(Media $media)
    {
        return $media->delete();
    }
    
    /**
     * Delete all media in a collection for a model.
     *
     * @param Model $model
     * @param string|null $collectionName
     * @return void
     */
    public function clearMediaCollection(Model $model, string $collectionName = null)
    {
        if (!method_exists($model, 'clearMediaCollection')) {
            throw new \Exception('Model does not use HasMedia trait');
        }
        
        $model->clearMediaCollection($collectionName);
    }
}
