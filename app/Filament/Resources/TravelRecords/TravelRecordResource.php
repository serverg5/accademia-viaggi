<?php

namespace App\Filament\Resources\TravelRecords;

use App\Filament\Resources\TravelRecords\Pages\CreateTravelRecord;
use App\Filament\Resources\TravelRecords\Pages\EditTravelRecord;
use App\Filament\Resources\TravelRecords\Pages\ListTravelRecords;
use App\Filament\Resources\TravelRecords\Pages\ViewTravelRecord;
use App\Filament\Resources\TravelRecords\Schemas\TravelRecordForm;
use App\Filament\Resources\TravelRecords\Schemas\TravelRecordInfolist;
use App\Filament\Resources\TravelRecords\Tables\TravelRecordsTable;
use App\Models\TravelRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TravelRecordResource extends Resource
{
    protected static ?string $model = TravelRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Viaggi';

    protected static ?string $navigationLabel = 'Record viaggi';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'record viaggio';

    protected static ?string $pluralModelLabel = 'record viaggi';

    protected static ?string $recordTitleAttribute = 'practice_code';

    public static function monthOptions(): array
    {
        return [
            1 => 'Gennaio',
            2 => 'Febbraio',
            3 => 'Marzo',
            4 => 'Aprile',
            5 => 'Maggio',
            6 => 'Giugno',
            7 => 'Luglio',
            8 => 'Agosto',
            9 => 'Settembre',
            10 => 'Ottobre',
            11 => 'Novembre',
            12 => 'Dicembre',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return TravelRecordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TravelRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TravelRecordsTable::configure($table);
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
            'index' => ListTravelRecords::route('/'),
            'create' => CreateTravelRecord::route('/create'),
            'view' => ViewTravelRecord::route('/{record}'),
            'edit' => EditTravelRecord::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
