<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntityResource\Pages;
use App\Filament\Resources\EntityResource\RelationManagers;
use App\Filament\Traits\HasMediaResource;
use App\Models\Entity;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;

class EntityResource extends Resource
{
    use HasMediaResource;

    protected static ?string $model = Entity::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Entity Management';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    /**
     * Generate dynamic section label based on selected entity type
     */
    protected static function getDynamicSectionLabel(callable $get, string $sectionName, string $fallbackPrefix = 'Entity'): string
    {
        $entityTypeId = $get('entity_type_id');
        if ($entityTypeId) {
            $entityType = \App\Models\EntityType::find($entityTypeId);
            return $entityType ? $entityType->name . ' ' . $sectionName : $sectionName;
        }
        return $fallbackPrefix . ' ' . $sectionName;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('entity_type_id')
                            ->relationship('entity_type', 'name')
                            ->required()
                            ->label('Entity Type')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear any cached entity type name when selection changes
                                $set('_entity_type_name', null);
                            }),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->label('Owner/Creator'),
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
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

                Forms\Components\Section::make(fn (callable $get) => static::getDynamicSectionLabel($get, 'Contact Information'))
                    ->collapsible()
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

                Forms\Components\Section::make(fn (callable $get) => static::getDynamicSectionLabel($get, 'Profile'))
                    ->collapsible()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('entity_logo')
                            ->label('Entity Logo')
                            ->image()                       // enables preview & client-side image checks
                            ->acceptedFileTypes([
                                'image/jpeg', 'image/png', 'image/gif',
                            ])
                            // ->disk('s3')                 // uncomment if you store media on S3
                            // ->visibility('public')       // for S3/public disks; Spatie respects disk visibility
                            ->downloadable()                // show download button in the UI
                            ->preserveFilenames(),
//                        Forms\Components\FileUpload::make('logo')
//                            ->image()
//                            ->directory('entity-logos')
//                            ->maxSize(1024)
//                            ->imageResizeMode('cover')
//                            ->imageCropAspectRatio('1:1')
//                            ->imageResizeTargetWidth('300')
//                            ->imageResizeTargetHeight('300'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(65535),
                    ])->columns(2),

                Forms\Components\Section::make(fn (callable $get) => static::getDynamicSectionLabel($get, 'Values'))
                    ->collapsible()
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
                Forms\Components\Section::make(fn (callable $get) => static::getDynamicSectionLabel($get, 'Media Files'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('media_logos')
                            ->label('Entity Logos')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'])
                            ->directory('entity-logos')
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

                        SpatieMediaLibraryFileUpload::make('media_photos')
                            ->label('Entity Photos')
                            ->collection('entity_photos')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->image()                       // enables preview & client-side image checks
                            ->acceptedFileTypes([
                                'image/jpeg', 'image/png', 'image/gif',
                            ])
                            // ->disk('s3')                 // uncomment if you store media on S3
                            // ->visibility('public')       // for S3/public disks; Spatie respects disk visibility
                            ->downloadable()                // show download button in the UI
                            ->preserveFilenames(),

//                        Forms\Components\FileUpload::make('media_photos')
//                            ->label('Entity Photos')
//                            ->multiple()
//                            ->maxFiles(10)
//                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
//                            ->directory('entity-photos')
//                            ->visibility('public')
//                            ->saveUploadedFileUsing(function ($file, $record) {
//                                if ($record) {
//                                    // Create a proper UploadedFile instance
//                                    $tempPath = $file->getRealPath();
//                                    $originalName = $file->getClientOriginalName();
//                                    $mimeType = $file->getMimeType();
//                                    $error = null;
//                                    $test = true;
//
//                                    $uploadedFile = new \Illuminate\Http\UploadedFile(
//                                        $tempPath,
//                                        $originalName,
//                                        $mimeType,
//                                        $error,
//                                        $test
//                                    );
//
//                                    // Add the media and return the path for Filament
//                                    $media = $record->addMedia($uploadedFile, 'photos');
//
//                                    return $media->file_path;
//                                }
//
//                                return null;
//                            }),

                        Forms\Components\FileUpload::make('media_documents')
                            ->label('Entity Documents')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->directory('entity-documents')
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
                    ->defaultImageUrl(fn () => asset('images/default-entity-logo.png'))
                    ->label('Logo'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable()
                    ->url(fn (Entity $record): ?string => $record->website ? 'https://'.$record->website : null)
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
                    ->relationship('location', 'name'),
                Tables\Filters\SelectFilter::make('owner')
                    ->relationship('user', 'name'),
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
                ]),
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
            'index' => Pages\ListEntities::route('/'),
            'create' => Pages\CreateEntity::route('/create'),
            'edit' => Pages\EditEntity::route('/{record}/edit'),
            'view' => Pages\ViewEntity::route('/{record}'),
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
