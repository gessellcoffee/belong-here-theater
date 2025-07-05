<?php

namespace App\Filament\Traits;

use App\Filament\Fields\MediaUploadField;
use App\Models\Media;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

trait HasMediaResource
{
    /**
     * Add media upload fields to a form.
     *
     * @param Form $form
     * @param array $collections Array of collection configurations [name => [label, maxFiles, acceptedFileTypes]]
     * @return Form
     */
    protected function addMediaFields(Form $form, array $collections = []): Form
    {
        // If no collections specified, add a default one
        if (empty($collections)) {
            $collections = [
                'default' => [
                    'label' => 'Media Files',
                    'maxFiles' => 5,
                    'acceptedFileTypes' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'],
                ],
            ];
        }
        
        // Add each collection as a separate field
        foreach ($collections as $name => $config) {
            $label = $config['label'] ?? ucfirst($name);
            $maxFiles = $config['maxFiles'] ?? 5;
            $acceptedFileTypes = $config['acceptedFileTypes'] ?? ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            
            $form->schema([
                MediaUploadField::make("media_{$name}", $label, $name, $acceptedFileTypes, $maxFiles),
            ]);
        }
        
        return $form;
    }
    
    /**
     * Handle media deletion from Livewire component.
     *
     * @param int $mediaId
     * @return void
     */
    public function deleteMedia(int $mediaId): void
    {
        $media = Media::find($mediaId);
        
        if (!$media) {
            return;
        }
        
        // Check if the media belongs to the current record
        $record = $this->getRecord();
        
        if ($media->mediable_id !== $record->id || $media->mediable_type !== get_class($record)) {
            return;
        }
        
        // Delete the media
        $media->delete();
        
        // Refresh the form
        $this->fillForm();
        
        // Show notification
        $this->notify('success', 'Media deleted successfully');
    }
}
