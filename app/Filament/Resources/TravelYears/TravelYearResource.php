<?php

namespace App\Filament\Resources\TravelYears;

use App\Filament\Resources\TravelYears\Pages\CreateTravelYear;
use App\Filament\Resources\TravelYears\Pages\EditTravelYear;
use App\Filament\Resources\TravelYears\Pages\ListTravelYears;
use App\Filament\Resources\TravelYears\Schemas\TravelYearForm;
use App\Filament\Resources\TravelYears\Tables\TravelYearsTable;
use App\Models\TravelYear;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TravelYearResource extends Resource
{
    protected static ?string $model = TravelYear::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Configurazione';

    protected static ?string $navigationLabel = 'Anni';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'anno';

    protected static ?string $pluralModelLabel = 'anni';

    protected static ?string $recordTitleAttribute = 'year';

    public static function form(Schema $schema): Schema
    {
        return TravelYearForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravelYearsTable::configure($table);
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
            'index' => ListTravelYears::route('/'),
            'create' => CreateTravelYear::route('/create'),
            'edit' => EditTravelYear::route('/{record}/edit'),
        ];
    }
}
