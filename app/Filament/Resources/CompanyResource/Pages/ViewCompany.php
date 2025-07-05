<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCompany extends ViewRecord
{
    protected static string $resource = CompanyResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Company')
                ->icon('heroicon-o-pencil-square'),
            
            Actions\DeleteAction::make()
                ->label('Archive Company'),
            
            Actions\RestoreAction::make(),
            
            Actions\ForceDeleteAction::make()
                ->label('Delete Permanently')
                ->requiresConfirmation()
                ->modalHeading('Delete Company Permanently')
                ->modalDescription('Are you sure you want to permanently delete this company? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, Delete Permanently')
                ->action(function () {
                    $name = $this->record->name;
                    $this->record->forceDelete();
                    
                    Notification::make()
                        ->title("{$name} has been permanently deleted")
                        ->danger()
                        ->send();
                        
                    $this->redirect(CompanyResource::getUrl());
                }),
        ];
    }
}
