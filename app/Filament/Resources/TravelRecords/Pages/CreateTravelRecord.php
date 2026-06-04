<?php

namespace App\Filament\Resources\TravelRecords\Pages;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Services\TravelRecordService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTravelRecord extends CreateRecord
{
    protected static string $resource = TravelRecordResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = app(TravelRecordService::class);
        $prepared = $service->prepareForPersistence($data, Auth::user());

        if (! $service->ensureRecordCanBeChangedForYear(Auth::user(), $prepared['year'])) {
            Notification::make()
                ->danger()
                ->title('Anno bloccato')
                ->body('Non puoi creare record in un anno bloccato.')
                ->send();

            $this->halt(true);
        }

        return [
            ...$prepared,
            'created_by' => Auth::id(),
        ];
    }
}
