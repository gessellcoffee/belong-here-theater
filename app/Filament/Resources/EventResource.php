<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\EventFields;
use App\Models\EventFieldValue;
use Filament\Forms;
use Filament\Forms\Form;    
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Get;
use App\Models\User;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Events';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('event_type_id')
                            ->relationship('eventType', 'name')
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(),
                        Forms\Components\Textarea::make('description')
                            ->required(),
                        Forms\Components\Repeater::make('affiliations')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('entity_id')
                                    ->relationship('entity', 'name')
                                    ->nullable(),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->nullable(),
                                Forms\Components\Select::make('role')
                                    ->options([
                                        'organizer' => 'Organizer',
                                        'venue' => 'Venue',
                                        'sponsor' => 'Sponsor',
                                        'performer' => 'Performer',
                                        'attendee' => 'Attendee',
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'B2B' => 'Business to Business',
                                        'U2U' => 'User to User',
                                        'B2U' => 'Business to User',
                                        'U2B' => 'User to Business',
                                    ])
                                    ->required(),
                                Forms\Components\Toggle::make('confirmation_status')
                                    ->label('Confirmed')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                ($state['role'] ?? 'Affiliation') . 
                                (isset($state['entity_id']) ? ' (Entity)' : '') .
                                (isset($state['user_id']) ? ' (User)' : '')
                            ),
                        Forms\Components\DatePicker::make('date')
                            ->required(),
                    ]),
                
                Section::make('Event Fields')
                    ->collapsible()
                    ->schema(function (Get $get, $record) {
                        $eventTypeId = $get('event_type_id');
                        
                        if (!$eventTypeId) {
                            return [];
                        }
                        
                        $eventFields = EventFields::where('event_type_id', $eventTypeId)->get();
                        $schema = [];
                        
                        foreach ($eventFields as $field) {
                            $fieldName = 'event_field_' . $field->id;
                            
                            // Get existing value if editing
                            $defaultValue = null;
                            if ($record && $record->exists) {
                                $defaultValue = $record->getFieldValue($field->id);
                            }
                            
                            $component = match ($field->type) {
                                'text' => TextInput::make($fieldName)
                                    ->label($field->label)
                                    ->default($defaultValue),
                                'textarea' => Textarea::make($fieldName)
                                    ->label($field->label)
                                    ->default($defaultValue),
                                'select' => Select::make($fieldName)
                                    ->label($field->label)
                                    ->options([]) // You may want to define options based on field configuration
                                    ->default($defaultValue),
                                'date' => DatePicker::make($fieldName)
                                    ->label($field->label)
                                    ->default($defaultValue),
                                'user' => Select::make($fieldName)
                                    ->label($field->label)
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->default($defaultValue),
                                default => TextInput::make($fieldName)
                                    ->label($field->label)
                                    ->default($defaultValue),
                                
                            };
                            
                            $schema[] = $component;
                        }
                        
                        return $schema;
                    })
                    ->visible(fn (Get $get) => $get('event_type_id') !== null)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('venue')
                    ->getStateUsing(fn ($record) => $record->getVenue()?->name ?? 'No venue')
                    ->label('Venue')
                    ->searchable(),
                Tables\Columns\TextColumn::make('organizer')
                    ->getStateUsing(fn ($record) => $record->getOrganizer()?->name ?? 'No organizer')
                    ->label('Organizer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type_id')
                    ->relationship('eventType', 'name')
                    ->label('Event Type'),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
