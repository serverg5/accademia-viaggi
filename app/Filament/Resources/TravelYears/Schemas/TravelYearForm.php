<?php

namespace App\Filament\Resources\TravelYears\Schemas;

use App\Models\TravelYear;
use App\Support\Permissions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TravelYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gestione anno')
                    ->schema([
                        TextInput::make('year')
                            ->label('Anno')
                            ->required()
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->label('Stato')
                            ->options([
                                TravelYear::STATUS_ACTIVE => 'Attivo',
                                TravelYear::STATUS_ARCHIVED => 'Archiviato',
                            ])
                            ->required()
                            ->default(TravelYear::STATUS_ACTIVE),
                        Toggle::make('is_locked')
                            ->label('Bloccato')
                            ->helperText('Gli anni bloccati restano visibili, ma sono protetti da modifiche accidentali.')
                            ->disabled(fn (): bool => ! Auth::user()?->can(Permissions::UNLOCK_YEARS))
                            ->dehydrated(fn (): bool => Auth::user()?->can(Permissions::UNLOCK_YEARS) ?? false),
                    ])
                    ->columns(2),
            ]);
    }
}
