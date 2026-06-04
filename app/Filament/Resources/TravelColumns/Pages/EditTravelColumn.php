<?php

namespace App\Filament\Resources\TravelColumns\Pages;

use App\Filament\Resources\TravelColumns\TravelColumnResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTravelColumn extends EditRecord
{
    protected static string $resource = TravelColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Elimina')
                ->visible(fn (Model $record): bool => $record->is_deletable && ! $record->is_system),
        ];
    }
}
