<?php

namespace App\Filament\Resources\TravelRecords\Schemas;

use App\Models\TravelColumn;
use App\Models\TravelSelectOption;
use App\Services\TravelRecordService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TravelRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dati viaggio')
                    ->schema(static::dynamicFields())
                    ->columns(2),
            ]);
    }

    private static function dynamicFields(): array
    {
        return app(TravelRecordService::class)
            ->columns()
            ->map(fn (TravelColumn $column) => static::fieldForColumn($column))
            ->all();
    }

    private static function fieldForColumn(TravelColumn $column)
    {
        $field = match ($column->type) {
            TravelColumn::TYPE_NUMBER => TextInput::make("dynamic.{$column->key}")
                ->numeric(),
            TravelColumn::TYPE_SELECT => Select::make("dynamic.{$column->key}")
                ->options(fn (): array => TravelSelectOption::query()
                    ->where('travel_column_id', $column->id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->pluck('label', 'value')
                    ->all())
                ->searchable()
                ->preload(),
            TravelColumn::TYPE_DATE => DatePicker::make("dynamic.{$column->key}")
                ->displayFormat('d/m/Y'),
            TravelColumn::TYPE_MONEY => TextInput::make("dynamic.{$column->key}")
                ->numeric()
                ->step('0.01')
                ->prefix("\u{20AC}"),
            TravelColumn::TYPE_BOOLEAN => Toggle::make("dynamic.{$column->key}"),
            default => TextInput::make("dynamic.{$column->key}")
                ->maxLength(255),
        };

        $field
            ->label($column->label)
            ->required($column->is_required)
            ->validationAttribute(strtolower($column->label));

        if ($column->key === 'year') {
            $field
                ->default(now()->year)
                ->minValue(2000)
                ->maxValue(2100);
        }

        if ($column->key === 'month') {
            $field
                ->default(now()->month)
                ->minValue(1)
                ->maxValue(12);
        }

        if ($column->key === 'practice_code') {
            $field->maxLength(255);
        }

        return $field;
    }
}
