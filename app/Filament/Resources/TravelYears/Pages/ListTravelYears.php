<?php

namespace App\Filament\Resources\TravelYears\Pages;

use App\Filament\Resources\TravelYears\TravelYearResource;
use App\Services\TravelYearService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelYears extends ListRecords
{
    protected static string $resource = TravelYearResource::class;

    public function mount(): void
    {
        app(TravelYearService::class)->ensureCurrentYear();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuovo anno'),
        ];
    }
}
