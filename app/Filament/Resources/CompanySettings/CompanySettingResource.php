<?php

namespace App\Filament\Resources\CompanySettings;

use App\Filament\Resources\CompanySettings\Pages\CreateCompanySetting;
use App\Filament\Resources\CompanySettings\Pages\EditCompanySetting;
use App\Filament\Resources\CompanySettings\Pages\ListCompanySettings;
use App\Filament\Resources\CompanySettings\Schemas\CompanySettingForm;
use App\Filament\Resources\CompanySettings\Tables\CompanySettingsTable;
use App\Models\CompanySetting;
use App\Support\Permissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CompanySettingResource extends Resource
{
    protected static ?string $model = CompanySetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'Configurazione';

    protected static ?string $navigationLabel = 'Azienda';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'impostazione azienda';

    protected static ?string $pluralModelLabel = 'impostazioni azienda';

    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Schema $schema): Schema
    {
        return CompanySettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanySettingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return parent::canCreate()
            && Auth::user()?->can(Permissions::MANAGE_COMPANY_SETTINGS)
            && CompanySetting::query()->doesntExist();
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
            'index' => ListCompanySettings::route('/'),
            'create' => CreateCompanySetting::route('/create'),
            'edit' => EditCompanySetting::route('/{record}/edit'),
        ];
    }
}
