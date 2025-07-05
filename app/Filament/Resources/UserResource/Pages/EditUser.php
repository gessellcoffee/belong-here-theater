<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
