<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\EventFieldValue;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract event field data and store it temporarily
        $eventFieldData = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'event_field_')) {
                $eventFieldData[$key] = $value;
                unset($data[$key]); // Remove from main data to avoid database errors
            }
        }
        
        // Store in class property for later use
        $this->eventFieldData = $eventFieldData;
        
        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Create the event record first
        $record = parent::handleRecordCreation($data);

        // Process event field values if they exist
        if (isset($this->eventFieldData)) {
            foreach ($this->eventFieldData as $fieldKey => $value) {
                if (str_starts_with($fieldKey, 'event_field_') && $value !== null && $value !== '') {
                    $fieldId = str_replace('event_field_', '', $fieldKey);
                    
                    EventFieldValue::create([
                        'event_id' => $record->id,
                        'event_field_id' => $fieldId,
                        'value' => $value,
                    ]);
                }
            }
        }

        return $record;
    }

    protected $eventFieldData = [];
}
