<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventFieldsResource\Pages;
use App\Filament\Resources\EventFieldsResource\RelationManagers;
use App\Models\EventFields;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventFieldsResource extends Resource
{
    protected static ?string $model = EventFields::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Events';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_type_id')
                    ->relationship('eventType', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'text' => 'Text',
                        'number' => 'Number',
                        'date' => 'Date',
                        'time' => 'Time',
                        'datetime' => 'Datetime',
                        'select' => 'Select',
                        'checkbox' => 'Checkbox',
                        'radio' => 'Radio',
                        'textarea' => 'Textarea',
                        'file' => 'File',
                        'image' => 'Image',
                        'file' => 'File',
                        'image' => 'Image',
                        'user' => 'User (Single User)',
                        'users' => 'Users (Multiple Users)',
                        'document' => 'Document',
                        'documents' => 'Documents (Multiple Documents)',
                        'location' => 'Location',
                        'locations' => 'Locations (Multiple Locations)',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_type.name')
                    ->label('Event Type'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('label')
                    ->label('Label'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListEventFields::route('/'),
            'create' => Pages\CreateEventFields::route('/create'),
            'edit' => Pages\EditEventFields::route('/{record}/edit'),
        ];
    }
}
