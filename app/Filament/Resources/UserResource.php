<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Traits\HasMediaResource;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;    

class UserResource extends Resource
{
    use HasMediaResource;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Checkbox::make('verify_email')
                            ->label('Verify Email Immediately')
                            ->helperText('If checked, the email will be marked as verified upon creation.')
                            ->default(false)
                            ->dehydrated(false)
                            ->visible(fn (string $context): bool => $context === 'create'),
                        Forms\Components\Placeholder::make('email_verified_status')
                            ->label('Email Verification Status')
                            ->content(function (?string $state, $record) {
                                if (! $record) {
                                    return 'Email will not be verified on creation';
                                }

                                if ($record->email_verified_at) {
                                    return '✅ Verified on '.date('F j, Y, g:i a', strtotime($record->email_verified_at));
                                } else {
                                    return '❌ Not Verified';
                                }
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Password')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->confirmed()
                            ->minLength(8)
                            ->rules([
                                'regex:/[A-Z]/', // At least one uppercase letter
                                'regex:/[a-z]/', // At least one lowercase letter
                                'regex:/[0-9]/', // At least one number
                            ])
                            ->helperText('Password must contain at least 8 characters, including uppercase, lowercase letters and numbers.')
                            ->autocomplete('new-password'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->label('Confirm Password')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->dehydrated(false)
                            ->autocomplete('new-password'),
                    ])->columns(2),
                Forms\Components\Section::make('Media Files')
                    ->schema([
                        Forms\Components\FileUpload::make('media_avatars')
                            ->label('Profile Pictures')
                            ->maxFiles(1)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->directory('user-avatars')
                            ->visibility('public')
                            ->saveUploadedFileUsing(function ($file, $record) {
                                // Clear previous avatars when uploading a new one
                                if ($record) {
                                    $record->clearMediaCollection('avatars');

                                    // Create a proper UploadedFile instance
                                    $tempPath = $file->getRealPath();
                                    $originalName = $file->getClientOriginalName();
                                    $mimeType = $file->getMimeType();
                                    $error = null;
                                    $test = true;

                                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                                        $tempPath,
                                        $originalName,
                                        $mimeType,
                                        $error,
                                        $test
                                    );

                                    // Add the media and return the path for Filament
                                    $media = $record->addMedia($uploadedFile, 'avatars');

                                    return $media->file_path;
                                }

                                return null;
                            }),

                        Forms\Components\FileUpload::make('media_documents')
                            ->label('User Documents')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->directory('user-documents')
                            ->visibility('public')
                            ->saveUploadedFileUsing(function ($file, $record) {
                                if ($record) {
                                    // Create a proper UploadedFile instance
                                    $tempPath = $file->getRealPath();
                                    $originalName = $file->getClientOriginalName();
                                    $mimeType = $file->getMimeType();
                                    $error = null;
                                    $test = true;

                                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                                        $tempPath,
                                        $originalName,
                                        $mimeType,
                                        $error,
                                        $test
                                    );

                                    // Add the media and return the path for Filament
                                    $media = $record->addMedia($uploadedFile, 'documents');

                                    return $media->file_path;
                                }

                                return null;
                            }),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('verified')
                    ->label('Email Verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('unverified')
                    ->label('Email Unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('verifyEmail')
                        ->label('Verify Email')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verify User Email')
                        ->modalDescription('Are you sure you want to mark this user\'s email as verified?')
                        ->modalSubmitActionLabel('Yes, Verify Email')
                        ->visible(fn ($record) => $record->email_verified_at === null)
                        ->action(function ($record) {
                            $record->email_verified_at = now();
                            $record->save();
                            Notification::make()
                                ->title('Email Verified')
                                ->success()
                                ->body('User email has been marked as verified.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('unverifyEmail')
                        ->label('Unverify Email')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Unverify User Email')
                        ->modalDescription('Are you sure you want to mark this user\'s email as unverified?')
                        ->modalSubmitActionLabel('Yes, Unverify Email')
                        ->visible(fn ($record) => $record->email_verified_at !== null)
                        ->action(function ($record) {
                            $record->email_verified_at = null;
                            $record->save();
                            Notification::make()
                                ->title('Email Unverified')
                                ->warning()
                                ->body('User email has been marked as unverified.')
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->label('Archive')
                        ->modalHeading('Archive User'),
                    Tables\Actions\RestoreAction::make()
                        ->label('Restore User'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Delete Permanently')
                        ->modalDescription('This action cannot be undone. This will permanently delete the user from the database.'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
