<?php

namespace App\Filament\Resources\EventFieldsResource\Pages;

use App\Filament\Resources\EventFieldsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventFields extends ListRecords
{
    protected static string $resource = EventFieldsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
