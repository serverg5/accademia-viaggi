<?php

namespace App\Filament\Resources\TravelSelectOptions\Schemas;

use App\Models\TravelColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TravelSelectOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Opzione')
                    ->schema([
                        Select::make('travel_column_id')
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
                            ->preload()
                            ->required(),
                        TextInput::make('label')
                            ->label('Etichetta')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('value')
                            ->label('Valore')
                            ->required()
                            ->maxLength(255)
                            ->scopedUnique(
                                modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                                    ->where('travel_column_id', $get('travel_column_id')),
                                ignoreRecord: true,
                            ),
                        TextInput::make('sort_order')
                            ->label('Ordinamento')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Attiva')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
