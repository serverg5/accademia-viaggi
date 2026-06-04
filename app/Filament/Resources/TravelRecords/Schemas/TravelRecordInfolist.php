<?php

namespace App\Filament\Resources\TravelRecords\Schemas;

use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Services\TravelRecordService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TravelRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati viaggio')
                    ->schema(static::dynamicEntries())
                    ->columns(2),
                Section::make('Audit')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Creato da')
                            ->placeholder('-'),
                        TextEntry::make('updater.name')
                            ->label('Aggiornato da')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Creato il')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Aggiornato il')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('Eliminato il')
                            ->dateTime('d/m/Y H:i')
                            ->visible(fn (TravelRecord $record): bool => $record->trashed()),
                    ])
                    ->columns(2),
            ]);
    }

    private static function dynamicEntries(): array
    {
        return app(TravelRecordService::class)
            ->columns()
            ->map(fn (TravelColumn $column) => TextEntry::make("dynamic_{$column->key}")
                ->label($column->label)
                ->placeholder('-')
                ->getStateUsing(fn (TravelRecord $record): string => app(TravelRecordService::class)->formattedValueForColumn($record, $column)))
            ->all();
    }
}
