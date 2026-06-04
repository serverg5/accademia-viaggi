<?php

namespace App\Filament\Resources\CompanySettings\Pages;

use App\Filament\Resources\CompanySettings\CompanySettingResource;
use App\Models\CompanySetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanySettings extends ListRecords
{
    protected static string $resource = CompanySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuove impostazioni')
                ->visible(fn (): bool => CompanySetting::query()->doesntExist()),
        ];
    }
}
