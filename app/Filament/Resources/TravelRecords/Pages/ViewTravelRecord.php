<?php

namespace App\Filament\Resources\TravelRecords\Pages;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTravelRecord extends ViewRecord
{
    protected static string $resource = TravelRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifica'),
        ];
    }
}
