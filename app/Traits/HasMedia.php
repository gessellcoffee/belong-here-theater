<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
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
        $fileName = $file->getClientOriginalName();
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeFileName = Str::slug($fileNameWithoutExtension) . '_' . time() . '.' . $extension;
        
        // Define the path where the file will be stored
        $path = $this->getMediaDirectory() . '/' . $safeFileName;
        
        // Store the file
        $disk = config('filesystems.default', 'public');
        Storage::disk($disk)->put($path, file_get_contents($file));
        
        // Create the media record
        return $this->media()->create([
            'file_name' => $fileName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'disk' => $disk,
            'file_size' => $file->getSize(),
            'collection_name' => $collectionName,
            'custom_properties' => $customProperties,
        ]);
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
