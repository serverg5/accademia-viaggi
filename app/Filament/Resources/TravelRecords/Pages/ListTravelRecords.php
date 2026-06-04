<?php

namespace App\Filament\Resources\TravelRecords\Pages;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Services\TravelYearService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelRecords extends ListRecords
{
    protected static string $resource = TravelRecordResource::class;

    public function mount(): void
    {
        app(TravelYearService::class)->ensureCurrentYear();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuovo record'),
        ];
    }
}
