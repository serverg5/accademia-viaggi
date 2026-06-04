<?php

namespace App\Filament\Resources\TravelSelectOptions\Tables;

use App\Models\TravelColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TravelSelectOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('travelColumn.label')
                    ->label('Colonna')
                    ->searchable(),
                TextColumn::make('label')
                    ->label('Etichetta')
                    ->searchable(),
                TextColumn::make('value')
                    ->label('Valore')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean(),
                TextColumn::make('creator.name')
                    ->label('Creata da')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updater.name')
                    ->label('Aggiornata da')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creata il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aggiornata il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('travel_column_id')
                    ->label('Colonna')
                    ->relationship(
                        'travelColumn',
                        'label',
                        fn (Builder $query): Builder => $query
                            ->where('type', TravelColumn::TYPE_SELECT)
                            ->orderBy('sort_order')
                            ->orderBy('label'),
                    )
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Attiva'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifica'),
                DeleteAction::make()
                    ->label('Elimina'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Elimina selezionati'),
                ]),
            ]);
    }
}
