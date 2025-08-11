<?php

namespace App\Filament\Resources\EventFieldsResource\Pages;

use App\Filament\Resources\EventFieldsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventFields extends EditRecord
{
    protected static string $resource = EventFieldsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
