<?php

namespace App\Filament\Resources\EntityTypeResource\Pages;

use App\Filament\Resources\EntityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEntityTypes extends ListRecords
{
    protected static string $resource = EntityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
