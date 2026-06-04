<?php

namespace App\Filament\Resources\TravelYears\Tables;

use App\Models\TravelYear;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TravelYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Anno')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Stato')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        TravelYear::STATUS_ACTIVE => 'Attivo',
                        TravelYear::STATUS_ARCHIVED => 'Archiviato',
                        default => $state,
                    })
                    ->colors([
                        'success' => TravelYear::STATUS_ACTIVE,
                        'gray' => TravelYear::STATUS_ARCHIVED,
                    ]),
                IconColumn::make('is_locked')
                    ->label('Bloccato')
                    ->boolean(),
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
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        TravelYear::STATUS_ACTIVE => 'Attivo',
                        TravelYear::STATUS_ARCHIVED => 'Archiviato',
                    ]),
                TernaryFilter::make('is_locked')
                    ->label('Bloccato'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifica'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Elimina selezionati'),
                ]),
            ]);
    }
}
