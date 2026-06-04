<?php

namespace App\Filament\Resources\TravelSelectOptions;

use App\Filament\Resources\TravelSelectOptions\Pages\CreateTravelSelectOption;
use App\Filament\Resources\TravelSelectOptions\Pages\EditTravelSelectOption;
use App\Filament\Resources\TravelSelectOptions\Pages\ListTravelSelectOptions;
use App\Filament\Resources\TravelSelectOptions\Schemas\TravelSelectOptionForm;
use App\Filament\Resources\TravelSelectOptions\Tables\TravelSelectOptionsTable;
use App\Models\TravelColumn;
use App\Models\TravelSelectOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TravelSelectOptionResource extends Resource
{
    protected static ?string $model = TravelSelectOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|UnitEnum|null $navigationGroup = 'Configurazione';

    protected static ?string $navigationLabel = 'Opzioni';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'opzione';

    protected static ?string $pluralModelLabel = 'opzioni';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return TravelSelectOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravelSelectOptionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('travelColumn', fn (Builder $query): Builder => $query->where('type', TravelColumn::TYPE_SELECT));
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
            'index' => ListTravelSelectOptions::route('/'),
            'create' => CreateTravelSelectOption::route('/create'),
            'edit' => EditTravelSelectOption::route('/{record}/edit'),
        ];
    }
}
