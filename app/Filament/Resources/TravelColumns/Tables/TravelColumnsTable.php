<?php

namespace App\Filament\Resources\TravelColumns\Tables;

use App\Models\TravelColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TravelColumnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('key')
                    ->label('Chiave')
                    ->searchable(),
                TextColumn::make('label')
                    ->label('Etichetta')
                    ->searchable(),
                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        TravelColumn::TYPE_TEXT => 'Testo',
                        TravelColumn::TYPE_NUMBER => 'Numero',
                        TravelColumn::TYPE_SELECT => 'Selezione',
                        TravelColumn::TYPE_DATE => 'Data',
                        TravelColumn::TYPE_MONEY => 'Valuta',
                        TravelColumn::TYPE_BOOLEAN => "S\u{00EC}/No",
                        default => $state,
                    }),
                IconColumn::make('is_required')
                    ->label('Obbl.')
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label('Visibile')
                    ->boolean(),
                IconColumn::make('is_visible_in_billing')
                    ->label('Fatt.')
                    ->boolean(),
                IconColumn::make('is_system')
                    ->label('Sistema')
                    ->boolean(),
                IconColumn::make('is_deletable')
                    ->label('Eliminabile')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        TravelColumn::TYPE_TEXT => 'Testo',
                        TravelColumn::TYPE_NUMBER => 'Numero',
                        TravelColumn::TYPE_SELECT => 'Selezione',
                        TravelColumn::TYPE_DATE => 'Data',
                        TravelColumn::TYPE_MONEY => 'Valuta',
                        TravelColumn::TYPE_BOOLEAN => "S\u{00EC}/No",
                    ]),
                TernaryFilter::make('is_visible')
                    ->label('Visibile'),
                TernaryFilter::make('is_visible_in_billing')
                    ->label('In fatturazione'),
                TernaryFilter::make('is_system')
                    ->label('Sistema'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifica'),
                DeleteAction::make()
                    ->label('Elimina')
                    ->visible(fn (Model $record): bool => $record->is_deletable && ! $record->is_system),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
