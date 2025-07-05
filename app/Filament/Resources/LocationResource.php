<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Filament\Traits\HasMediaResource;
use App\Models\Locations;
use App\Services\GeocoderService;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;

class LocationResource extends Resource
{
    use HasMediaResource;
    protected static ?string $model = Locations::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationGroup = 'Management';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Location Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                            
                            Forms\Components\TextInput::make('address')
                            ->label('Address')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete(false)
                            ->debounce('500ms')
                            ->suffixAction(
                                Action::make('verifyAddress')
                                    ->icon('heroicon-m-check-circle')
                                    ->tooltip('Verify Address')
                                    ->action(function (Get $get, Set $set) {
                                        $address = $get('address');
                                        
                                        if (empty($address)) {
                                            return;
                                        }
                                        
                                        $geocoder = App::make(GeocoderService::class);
                                        $result = $geocoder->geocode($address);
                                        
                                        if ($result) {
                                            $set('address', $result['formatted_address']);
                                            $set('city', $result['city']);
                                            $set('state', $result['state']);
                                            $set('zip', $result['postal_code']);
                                            $set('country', $result['country']);
                                            $set('latitude', $result['latitude']);
                                            $set('longitude', $result['longitude']);
                                        }
                                    })
                            )
                            ->datalist(function (Get $get) {
                                $query = $get('address');
                                
                                if (strlen($query) < 3) {
                                    return [];
                                }
                                
                                $geocoder = App::make(GeocoderService::class);
                                $suggestions = $geocoder->getAddressSuggestions($query);
                                
                                return array_column($suggestions, 'value');
                            }),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('state')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('zip')
                                    ->label('ZIP/Postal Code')
                                    ->required()
                                    ->maxLength(20),
                                    
                                Forms\Components\TextInput::make('country')
                                    ->required()
                                    ->default('United States')
                                    ->maxLength(255),
                            ]),
                            
                        Forms\Components\Hidden::make('latitude'),
                        Forms\Components\Hidden::make('longitude'),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Media Files')
                    ->schema([
                        Forms\Components\FileUpload::make('media_photos')
                            ->label('Location Photos')
                            ->multiple()
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->directory('location-photos')
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
                            ->label('Location Documents')
                            ->multiple()
                            ->maxFiles(5)
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->directory('location-documents')
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
                    
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('state')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable(),
                    
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
                Tables\Filters\SelectFilter::make('country'),
                Tables\Filters\SelectFilter::make('state'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
