<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Filament\Traits\HasMediaResource;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    use HasMediaResource;
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationGroup = 'Company Management';
    
    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('requested_by_user', 'name')
                            ->required()
                            ->label('Owner/Creator'),
                        Forms\Components\Select::make('location_id')
                            ->relationship('locations', 'name')
                            ->label('Location')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('city')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('state')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('zip')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('country')
                                    ->maxLength(100),
                            ]),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('website')
                            ->maxLength(255)
                            ->prefix('https://')
                            ->helperText('Enter the domain name only (e.g., example.com)'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(15),
                        Forms\Components\TextInput::make('extension')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Company Profile')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('company-logos')
                            ->maxSize(1024)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(65535),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Company Values')
                    ->schema([
                        Forms\Components\Textarea::make('vision')
                            ->rows(3)
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('mission')
                            ->rows(3)
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('values')
                            ->rows(3)
                            ->maxLength(65535),
                    ])->columns(1),
                Forms\Components\Section::make('Media Files')
                    ->schema([
                        Forms\Components\FileUpload::make('media_logos')
                            ->label('Company Logos')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'])
                            ->directory('company-logos')
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
                                    $media = $record->addMedia($uploadedFile, 'logos');
                                    return $media->file_path;
                                }
                                
                                return null;
                            }),
                            
                        Forms\Components\FileUpload::make('media_photos')
                            ->label('Company Photos')
                            ->multiple()
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->directory('company-photos')
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
                                    $media = $record->addMedia($uploadedFile, 'photos');
                                    return $media->file_path;
                                }
                                
                                return null;
                            }),
                            
                        Forms\Components\FileUpload::make('media_documents')
                            ->label('Company Documents')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->directory('company-documents')
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
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->defaultImageUrl(fn () => asset('images/default-company-logo.png'))
                    ->label('Logo'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('requested_by_user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('locations.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable()
                    ->url(fn (Company $record): ?string => $record->website ? 'https://' . $record->website : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('locations', 'name'),
                Tables\Filters\SelectFilter::make('owner')
                    ->relationship('requested_by_user', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Archive'),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('Delete Permanently')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Archive Selected'),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete Selected Permanently')
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\AffiliationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
            'view' => Pages\ViewCompany::route('/{record}'),
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
