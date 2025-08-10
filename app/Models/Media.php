<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'file_name',
        'file_path',
        'mime_type',
        'disk',
        'file_size',
        'collection_name',
        'custom_properties',
    ];

    protected $casts = [
        'custom_properties' => 'array',
    ];

    /**
     * Get the parent mediable model.
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL to the media file.
     */
    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    /**
     * Delete the file from storage when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($media) {
            if (! $media->isForceDeleting()) {
                return;
            }

            Storage::disk($media->disk)->delete($media->file_path);
        });
    }
}
