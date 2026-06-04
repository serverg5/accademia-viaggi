<?php

namespace App\Filament\Resources\TravelSelectOptions\Pages;

use App\Filament\Resources\TravelSelectOptions\TravelSelectOptionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTravelSelectOption extends CreateRecord
{
    protected static string $resource = TravelSelectOptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }
}
