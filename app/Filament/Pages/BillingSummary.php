<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelYear;
use App\Services\BillingSummaryService;
use App\Services\EmailSummaryService;
use App\Services\TravelRecordService;
use App\Services\TravelYearService;
use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class BillingSummary extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Fatturazione';

    protected static ?string $navigationLabel = 'Riepilogo Fatturazione';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'fatturazione';

    protected string $view = 'filament.pages.billing-summary';

    public static function canAccess(): bool
    {
        return Auth::user()?->can(Permissions::VIEW_BILLING) ?? false;
    }

    public function getTitle(): string
    {
        return 'Riepilogo Fatturazione';
    }

    public function mount(): void
    {
        app(TravelYearService::class)->ensureCurrentYear();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Esporta PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->can(Permissions::EXPORT_BILLING) ?? false)
                ->url(fn (): string => route('billing.exports.pdf', [
                    'year' => $this->selectedYear(),
                    'month' => $this->selectedMonth(),
                ])),
            Action::make('exportExcel')
                ->label('Esporta Excel')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->can(Permissions::EXPORT_BILLING) ?? false)
                ->url(fn (): string => route('billing.exports.excel', [
                    'year' => $this->selectedYear(),
                    'month' => $this->selectedMonth(),
                ])),
            Action::make('prepareEmail')
                ->label('Prepara email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->can(Permissions::VIEW_BILLING) ?? false)
                ->modalHeading('Riepilogo email')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Chiudi')
                ->modalWidth('4xl')
                ->modalContent(fn () => view('filament.pages.partials.email-summary-modal', [
                    'summary' => app(EmailSummaryService::class)->summary(
                        $this->selectedYear(),
                        $this->selectedMonth(),
                    ),
                ])),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => app(BillingSummaryService::class)->query(
                $this->selectedYear(),
                $this->selectedMonth(),
            ))
            ->columns($this->billingColumns())
            ->filters([
                SelectFilter::make('year')
                    ->label('Anno')
                    ->options(fn (): array => TravelYear::query()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->all())
                    ->default(now()->year),
                SelectFilter::make('month')
                    ->label('Mese')
                    ->options(TravelRecordResource::monthOptions())
                    ->default(now()->month),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultSort('record_date');
    }

    public function selectedYear(): int
    {
        return (int) data_get($this->getTableFilterState('year'), 'value', now()->year);
    }

    public function selectedMonth(): int
    {
        return (int) data_get($this->getTableFilterState('month'), 'value', now()->month);
    }

    public function formattedTotalCosts(): string
    {
        return app(BillingSummaryService::class)->formattedTotals(
            $this->selectedYear(),
            $this->selectedMonth(),
        )['costs'];
    }

    public function formattedTotalSales(): string
    {
        return app(BillingSummaryService::class)->formattedTotals(
            $this->selectedYear(),
            $this->selectedMonth(),
        )['sale'];
    }

    public function costLabel(): string
    {
        return app(BillingSummaryService::class)->labelFor(BillingSummaryService::COSTS_KEY, 'Costi');
    }

    public function saleLabel(): string
    {
        return app(BillingSummaryService::class)->labelFor(BillingSummaryService::SALE_KEY, 'Vendita');
    }

    public function monthName(): string
    {
        return TravelRecordResource::monthOptions()[$this->selectedMonth()] ?? (string) $this->selectedMonth();
    }

    private function billingColumns(): array
    {
        return app(BillingSummaryService::class)
            ->columns()
            ->map(fn (TravelColumn $column): TextColumn => $this->columnForTravelColumn($column))
            ->all();
    }

    private function columnForTravelColumn(TravelColumn $column): TextColumn
    {
        $travelRecordService = app(TravelRecordService::class);

        return TextColumn::make("billing_{$column->key}")
            ->label($column->label)
            ->getStateUsing(fn (TravelRecord $record): string => $travelRecordService->formattedValueForColumn($record, $column));
    }
}
