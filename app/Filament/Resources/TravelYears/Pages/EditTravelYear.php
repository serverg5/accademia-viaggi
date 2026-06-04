<?php

namespace App\Filament\Resources\TravelYears\Pages;

use App\Filament\Resources\TravelYears\TravelYearResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTravelYear extends EditRecord
{
    protected static string $resource = TravelYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
