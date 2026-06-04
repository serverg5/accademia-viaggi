<?php

namespace App\Filament\Resources\TravelSelectOptions\Pages;

use App\Filament\Resources\TravelSelectOptions\TravelSelectOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTravelSelectOption extends EditRecord
{
    protected static string $resource = TravelSelectOptionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Elimina'),
        ];
    }
}
