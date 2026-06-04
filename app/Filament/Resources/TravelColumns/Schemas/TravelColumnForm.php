<?php

namespace App\Filament\Resources\TravelColumns\Schemas;

use App\Models\TravelColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class TravelColumnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Colonna')
                    ->schema([
                        TextInput::make('key')
                            ->label('Chiave')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (?Model $record): bool => $record?->is_system ?? false)
                            ->dehydrated(fn (?Model $record): bool => ! ($record?->is_system ?? false))
                            ->helperText('Identificativo tecnico usato per salvare i valori dinamici.'),
                        TextInput::make('label')
                            ->label('Etichetta')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipo')
                            ->required()
                            ->options([
                                TravelColumn::TYPE_TEXT => 'Testo',
                                TravelColumn::TYPE_NUMBER => 'Numero',
                                TravelColumn::TYPE_SELECT => 'Selezione',
                                TravelColumn::TYPE_DATE => 'Data',
                                TravelColumn::TYPE_MONEY => 'Valuta',
                                TravelColumn::TYPE_BOOLEAN => "S\u{00EC}/No",
                            ])
                            ->disabled(fn (?Model $record): bool => $record?->is_system ?? false)
                            ->dehydrated(fn (?Model $record): bool => ! ($record?->is_system ?? false)),
                        TextInput::make('sort_order')
                            ->label('Ordinamento')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
                Section::make('Visibilita e regole')
                    ->schema([
                        Toggle::make('is_required')
                            ->label('Obbligatoria'),
                        Toggle::make('is_visible')
                            ->label('Visibile')
                            ->default(true),
                        Toggle::make('is_visible_in_billing')
                            ->label('Visibile in fatturazione'),
                        Toggle::make('is_system')
                            ->label('Sistema')
                            ->disabled()
                            ->dehydrated(false),
                        Toggle::make('is_deletable')
                            ->label('Eliminabile')
                            ->default(true)
                            ->disabled(fn (?Model $record): bool => $record?->is_system ?? false)
                            ->dehydrated(fn (?Model $record): bool => ! ($record?->is_system ?? false)),
                    ])
                    ->columns(3),
            ]);
    }
}
