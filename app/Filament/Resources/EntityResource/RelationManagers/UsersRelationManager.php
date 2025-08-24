<?php

namespace App\Filament\Resources\EntityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'user';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Placeholder::make('email_verified_status')
                    ->label('Email Verification Status')
                    ->content(function ($record) {
                        if (! $record) {
                            return 'Email will not be verified on creation';
                        }

                        if ($record->email_verified_at) {
                            return '✅ Verified on '.date('F j, Y, g:i a', strtotime($record->email_verified_at));
                        } else {
                            return '❌ Not Verified';
                        }
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Show Archived Users')
                    ->queries(
                        true: fn (Builder $query) => $query->withTrashed(),
                        false: fn (Builder $query) => $query,
//                        trashed: fn (Builder $query) => $query->onlyTrashed(),
                    ),
                Tables\Filters\Filter::make('verified')
                    ->label('Email Verified')
                    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('unverified')
                    ->label('Email Not Verified')
                    ->query(fn (Builder $query) => $query->whereNull('email_verified_at')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Associate User'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DetachAction::make()
                    ->label('Remove Association'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Remove Selected Associations'),
                ]),
            ]);
    }
}
