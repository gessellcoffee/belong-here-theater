<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'name',
        'file_name',
        'file_path',
        'mime_type',
        'disk',
        'conversions_disk',
        'file_size',
        'collection_name',
        'custom_properties',
        'manipulations',
        'generated_conversions',
        'responsive_images',
        'uuid',
        'order_column',
        'model_type',
        'model_id',
    ];

    protected $casts = [
        'custom_properties' => 'array',
        'manipulations' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
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
