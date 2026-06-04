<?php

namespace App\Filament\Resources\TravelRecords\Pages;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Services\TravelRecordService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTravelRecord extends EditRecord
{
    protected static string $resource = TravelRecordResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [
            ...$data,
            ...app(TravelRecordService::class)->formStateForRecord($this->getRecord()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(TravelRecordService::class);
        $prepared = $service->prepareForPersistence($data, Auth::user());

        if (! $service->ensureRecordCanBeChangedForYear(Auth::user(), $prepared['year'])) {
            Notification::make()
                ->danger()
                ->title('Anno bloccato')
                ->body('Non puoi modificare record in un anno bloccato.')
                ->send();

            $this->halt(true);
        }

        return $prepared;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Visualizza'),
            DeleteAction::make()
                ->label('Elimina'),
            ForceDeleteAction::make()
                ->label('Elimina definitivamente'),
            RestoreAction::make()
                ->label('Ripristina'),
        ];
    }
}
