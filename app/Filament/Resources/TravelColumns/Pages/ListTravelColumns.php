<?php

namespace App\Filament\Resources\TravelColumns\Pages;

use App\Filament\Resources\TravelColumns\TravelColumnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelColumns extends ListRecords
{
    protected static string $resource = TravelColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuova colonna'),
        ];
    }
}
