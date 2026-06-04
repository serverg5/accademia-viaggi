<?php

namespace App\Filament\Resources\TravelRecords\Tables;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelYear;
use App\Services\TravelRecordService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TravelRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('record_date', 'desc')
            ->paginated([10, 25, 50, 100])
            ->columns([
                ...static::dynamicColumns(),
                TextColumn::make('creator.name')
                    ->label('Creato da')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updater.name')
                    ->label('Aggiornato da')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Aggiornato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Eliminato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Anno')
                    ->options(fn (): array => TravelYear::query()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->all())
                    ->default(now()->year),
                SelectFilter::make('month')
                    ->label('Mese')
                    ->options(TravelRecordResource::monthOptions())
                    ->default(now()->month),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizza'),
                EditAction::make()
                    ->label('Modifica'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Elimina selezionati'),
                    ForceDeleteBulkAction::make()
                        ->label('Elimina definitivamente'),
                    RestoreBulkAction::make()
                        ->label('Ripristina'),
                ]),
            ]);
    }

    private static function dynamicColumns(): array
    {
        return app(TravelRecordService::class)
            ->columns()
            ->map(fn (TravelColumn $column) => static::columnForTravelColumn($column))
            ->all();
    }

    private static function columnForTravelColumn(TravelColumn $column): TextColumn
    {
        $service = app(TravelRecordService::class);
        $name = $service->isTechnicalColumn($column) ? $column->key : "dynamic_{$column->key}";

        $tableColumn = TextColumn::make($name)
            ->label($column->label)
            ->getStateUsing(fn (TravelRecord $record): string => $service->formattedValueForColumn($record, $column));

        if (in_array($column->key, ['year', 'month', 'record_date', 'practice_code'], true)) {
            $tableColumn->sortable([$column->key]);
        }

        if ($column->key === 'practice_code') {
            $tableColumn->searchable(['practice_code']);
        }

        return $tableColumn;
    }
}
