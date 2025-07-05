<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit User')
                ->icon('heroicon-o-pencil-square'),
                
            Actions\Action::make('verifyEmail')
                ->label('Verify Email')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verify User Email')
                ->modalDescription('Are you sure you want to mark this user\'s email as verified?')
                ->modalSubmitActionLabel('Yes, Verify Email')
                ->visible(fn () => $this->record->email_verified_at === null)
                ->action(function () {
                    $this->record->email_verified_at = now();
                    $this->record->save();
                    
                    Notification::make()
                        ->title('Email verified successfully')
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('unverifyEmail')
                ->label('Unverify Email')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Unverify User Email')
                ->modalDescription('Are you sure you want to mark this user\'s email as unverified?')
                ->modalSubmitActionLabel('Yes, Unverify Email')
                ->visible(fn () => $this->record->email_verified_at !== null)
                ->action(function () {
                    $this->record->email_verified_at = null;
                    $this->record->save();
                    
                    Notification::make()
                        ->title('Email unverified successfully')
                        ->warning()
                        ->send();
                }),
        ];
    }
}
