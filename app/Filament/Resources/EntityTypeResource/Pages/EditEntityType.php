<?php

namespace App\Filament\Resources\EntityTypeResource\Pages;

use App\Filament\Resources\EntityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEntityType extends EditRecord
{
    protected static string $resource = EntityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
