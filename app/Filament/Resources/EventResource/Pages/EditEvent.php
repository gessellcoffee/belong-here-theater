<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\EventFieldValue;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Update the event record first
        $record = parent::handleRecordUpdate($record, $data);

        // Process event field values if they exist
        if (isset($this->eventFieldData)) {
            foreach ($this->eventFieldData as $fieldKey => $value) {
                if (str_starts_with($fieldKey, 'event_field_')) {
                    $fieldId = str_replace('event_field_', '', $fieldKey);
                    
                    if ($value !== null && $value !== '') {
                        // Update or create the field value
                        EventFieldValue::updateOrCreate(
                            [
                                'event_id' => $record->id,
                                'event_field_id' => $fieldId,
                            ],
                            [
                                'value' => $value,
                            ]
                        );
                    } else {
                        // Delete the field value if it's empty
                        EventFieldValue::where('event_id', $record->id)
                            ->where('event_field_id', $fieldId)
                            ->delete();
                    }
                }
            }
        }

        return $record;
    }

    protected $eventFieldData = [];
}
