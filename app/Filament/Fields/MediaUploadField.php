<?php

namespace App\Filament\Fields;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MediaUploadField
{
    /**
     * Create a media upload field for Filament forms.
     *
     * @param  string  $name  The field name
     * @param  string  $label  The field label
     * @param  string|null  $collection  The media collection name
     * @param  array  $acceptedFileTypes  Accepted file types
     * @param  int  $maxFiles  Maximum number of files
     */
    public static function make(
        string $name = 'media',
        string $label = 'Media',
        ?string $collection = null,
        array $acceptedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
        int $maxFiles = 5
    ): Section {
        return Section::make($label)
            ->schema([
                FileUpload::make($name)
                    ->label('Upload Files')
                    ->multiple($maxFiles > 1)
                    ->maxFiles($maxFiles)
                    ->acceptedFileTypes($acceptedFileTypes)
                    ->directory('temp-uploads')
                    ->visibility('public')
                    ->live()
                    ->afterStateUpdated(function (Model $record, $state, Set $set, Get $get) use ($collection) {
                        if (! $state || ! method_exists($record, 'addMedia')) {
                            return;
                        }

                        // Process each uploaded file
                        $files = is_array($state) ? $state : [$state];
                        $mediaIds = [];

                        foreach ($files as $file) {
                            if (! $file) {
                                continue;
                            }

                            // Get the temporary uploaded file
                            $tempFile = storage_path('app/public/'.$file);
                            $uploadedFile = new \Illuminate\Http\UploadedFile(
                                $tempFile,
                                basename($file),
                                null,
                                null,
                                true
                            );

                            // Add the media to the record
                            $media = $record->addMedia($uploadedFile, $collection);
                            $mediaIds[] = $media->id;
                        }

                        // Clear the temporary uploads
                        $set($name, []);

                        // Refresh the media list
                        $set('media_list', $record->getMedia($collection)->pluck('id')->toArray());
                    }),

                ViewField::make('media_list')
                    ->label('Current Media')
                    ->view('filament.fields.media-list')
                    ->afterStateHydrated(function (Model $record, Set $set) use ($collection) {
                        if (method_exists($record, 'getMedia')) {
                            $set('media_list', $record->getMedia($collection)->pluck('id')->toArray());
                        }
                    }),
            ]);
    }
}
