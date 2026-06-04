<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Models\TravelYear;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TravelRecordImportExport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Viaggi';

    protected static ?string $navigationLabel = 'Import / Export';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'import-export-records';

    protected string $view = 'filament.pages.travel-record-import-export';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function getTitle(): string
    {
        return 'Import / Export Record';
    }

    public function yearOptions(): array
    {
        return TravelYear::query()
            ->orderByDesc('year')
            ->pluck('year', 'year')
            ->all();
    }

    public function monthOptions(): array
    {
        return TravelRecordResource::monthOptions();
    }

    public function preview(): ?array
    {
        $preview = session('travel_record_import_preview');

        return is_array($preview) ? $preview : null;
    }
}
