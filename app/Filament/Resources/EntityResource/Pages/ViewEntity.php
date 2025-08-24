<?php

namespace App\Filament\Resources\EntityResource\Pages;

use App\Filament\Resources\EntityResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEntity extends ViewRecord
{
    protected static string $resource = EntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Entity')
                ->icon('heroicon-o-pencil-square'),

            Actions\DeleteAction::make()
                ->label('Archive Entity'),

            Actions\RestoreAction::make(),

            Actions\ForceDeleteAction::make()
                ->label('Delete Permanently')
                ->requiresConfirmation()
                ->modalHeading('Delete Entity Permanently')
                ->modalDescription('Are you sure you want to permanently delete this entity? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, Delete Permanently')
                ->action(function () {
                    $name = $this->record->name;
                    $this->record->forceDelete();

                    Notification::make()
                        ->title("{$name} has been permanently deleted")
                        ->danger()
                        ->send();

                    $this->redirect(EntityResource::getUrl());
                }),
        ];
    }
}
