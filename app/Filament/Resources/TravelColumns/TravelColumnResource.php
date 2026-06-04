<?php

namespace App\Filament\Resources\TravelColumns;

use App\Filament\Resources\TravelColumns\Pages\CreateTravelColumn;
use App\Filament\Resources\TravelColumns\Pages\EditTravelColumn;
use App\Filament\Resources\TravelColumns\Pages\ListTravelColumns;
use App\Filament\Resources\TravelColumns\Schemas\TravelColumnForm;
use App\Filament\Resources\TravelColumns\Tables\TravelColumnsTable;
use App\Models\TravelColumn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TravelColumnResource extends Resource
{
    protected static ?string $model = TravelColumn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|UnitEnum|null $navigationGroup = 'Configurazione';

    protected static ?string $navigationLabel = 'Colonne';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'colonna';

    protected static ?string $pluralModelLabel = 'colonne';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return TravelColumnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravelColumnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTravelColumns::route('/'),
            'create' => CreateTravelColumn::route('/create'),
            'edit' => EditTravelColumn::route('/{record}/edit'),
        ];
    }
}
