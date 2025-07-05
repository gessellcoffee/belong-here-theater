<?php

namespace App\Filament\Components;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;

class MediaUpload
{
    /**
     * Create a media upload component for photos
     *
     * @param string $name The name of the field
     * @param string $label The label for the field
     * @param string $collection The media collection name
     * @param string $directory The directory to store files in
     * @param int $maxFiles The maximum number of files allowed
     * @return \Filament\Forms\Components\FileUpload
     */
    public static function photos(
        string $name = 'media_photos',
        string $label = 'Photos',
        string $collection = 'photos',
        string $directory = 'photos',
        int $maxFiles = 10
    ): FileUpload {
        return FileUpload::make($name)
            ->label($label)
            ->multiple()
            ->maxFiles($maxFiles)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->directory($directory)
            ->visibility('public')
            ->maxSize(300000) // 5MB max size per file
            ->imageResizeMode('cover')
            ->imageCropAspectRatio('16:9')
            ->imageResizeTargetWidth('1920')
            ->imageResizeTargetHeight('1080')
            ->saveUploadedFileUsing(function ($file, $record) use ($collection, $directory) {
                try {
                    if ($record) {
                        // Get file info but don't create a new UploadedFile instance
                        // which can cause memory/performance issues
                        $tempPath = $file->getRealPath();
                        $originalName = $file->getClientOriginalName();
                        $mimeType = $file->getMimeType();
                        
                        // Generate a unique filename to avoid collisions
                        $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . $file->getExtension();
                        $filamentPath = $directory . '/' . $uniqueName;
                        
                        // Store the file directly in the public disk
                        Storage::disk('public')->put($filamentPath, file_get_contents($tempPath));
                        
                        // Create the media record directly without using addMedia
                        // This is more efficient for large files
                        $media = new Media([
                            'file_name' => $originalName,
                            'file_path' => $filamentPath,
                            'mime_type' => $mimeType,
                            'disk' => 'public',
                            'file_size' => filesize($tempPath),
                            'collection_name' => $collection,
                        ]);
                        
                        $record->media()->save($media);
                        
                        // Return the path for Filament
                        return $filamentPath;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error saving {$collection}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                return null;
            });
    }

    /**
     * Create a media upload component for documents
     *
     * @param string $name The name of the field
     * @param string $label The label for the field
     * @param string $collection The media collection name
     * @param string $directory The directory to store files in
     * @param int $maxFiles The maximum number of files allowed
     * @param int $maxSize The maximum file size in KB
     * @return \Filament\Forms\Components\FileUpload
     */
    public static function documents(
        string $name = 'media_documents',
        string $label = 'Documents',
        string $collection = 'documents',
        string $directory = 'documents',
        int $maxFiles = 5,
        int $maxSize = 300000
    ): FileUpload {
        return FileUpload::make($name)
            ->label($label)
            ->multiple()
            ->maxFiles($maxFiles)
            ->acceptedFileTypes([
                'application/pdf', 
                'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ])
            ->directory($directory)
            ->visibility('public')
            ->maxSize($maxSize) // Default 10MB max size per file
            ->saveUploadedFileUsing(function ($file, $record) use ($collection, $directory) {
                try {
                    if ($record) {
                        // Get file info but don't create a new UploadedFile instance
                        // which can cause memory/performance issues
                        $tempPath = $file->getRealPath();
                        $originalName = $file->getClientOriginalName();
                        $mimeType = $file->getMimeType();
                        
                        // Generate a unique filename to avoid collisions
                        $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . $file->getExtension();
                        $filamentPath = $directory . '/' . $uniqueName;
                        
                        // Store the file directly in the public disk
                        Storage::disk('public')->put($filamentPath, file_get_contents($tempPath));
                        
                        // Create the media record directly without using addMedia
                        // This is more efficient for large files
                        $media = new Media([
                            'file_name' => $originalName,
                            'file_path' => $filamentPath,
                            'mime_type' => $mimeType,
                            'disk' => 'public',
                            'file_size' => filesize($tempPath),
                            'collection_name' => $collection,
                        ]);
                        
                        $record->media()->save($media);
                        
                        // Return the path for Filament
                        return $filamentPath;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error saving {$collection}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                return null;
            });
    }
}
