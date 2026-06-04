<?php

namespace App\Filament\Resources\TravelSelectOptions\Pages;

use App\Filament\Resources\TravelSelectOptions\TravelSelectOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelSelectOptions extends ListRecords
{
    protected static string $resource = TravelSelectOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
