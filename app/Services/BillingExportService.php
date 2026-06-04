<?php

namespace App\Services;

use App\Exports\BillingSummaryExport;
use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Models\CompanySetting;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class BillingExportService
{
    public function downloadPdf(int $year, int $month): Response
    {
        $billingSummaryService = app(BillingSummaryService::class);

        $company = CompanySetting::query()->first();
        $columns = $billingSummaryService->columns();
        $records = $billingSummaryService->query($year, $month)->get();
        $totals = $billingSummaryService->formattedTotals($year, $month);

        $pdf = Pdf::loadView('pdf.billing-summary', [
            'company' => $company,
            'logoDataUri' => $this->logoDataUri($company?->logo_path),
            'columns' => $columns,
            'rows' => $this->rows($records, $columns),
            'year' => $year,
            'monthName' => TravelRecordResource::monthOptions()[$month] ?? (string) $month,
            'totalCostsLabel' => $billingSummaryService->labelFor(BillingSummaryService::COSTS_KEY, 'Costi'),
            'totalSalesLabel' => $billingSummaryService->labelFor(BillingSummaryService::SALE_KEY, 'Vendita'),
            'totalCosts' => $totals['costs'],
            'totalSales' => $totals['sale'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->filename($year, $month));
    }

    public function downloadExcel(int $year, int $month): BinaryFileResponse
    {
        $this->ensureExcelMemoryLimit();

        $billingSummaryService = app(BillingSummaryService::class);

        $company = CompanySetting::query()->first();
        $columns = $billingSummaryService->columns();
        $records = $billingSummaryService->query($year, $month)->get();
        $totals = $billingSummaryService->totals($year, $month);

        return ExcelFacade::download(
            new BillingSummaryExport(
                year: $year,
                month: $month,
                company: $company,
                columns: $columns,
                records: $records,
                totals: [
                    'raw' => $totals,
                    'formatted' => $billingSummaryService->formattedTotals($year, $month),
                ],
                logoPath: $this->logoAbsolutePath($company?->logo_path),
            ),
            $this->filename($year, $month, 'xlsx'),
            Excel::XLSX,
        );
    }

    private function rows($records, $columns): array
    {
        $travelRecordService = app(TravelRecordService::class);

        return $records
            ->map(fn (TravelRecord $record): array => [
                'values' => $columns
                    ->mapWithKeys(fn (TravelColumn $column): array => [
                        $column->key => $travelRecordService->formattedValueForColumn($record, $column),
                    ])
                    ->all(),
            ])
            ->all();
    }

    private function logoDataUri(?string $logoPath): ?string
    {
        $absolutePath = $this->logoAbsolutePath($logoPath);

        if ($absolutePath === null) {
            return null;
        }

        $mimeType = mime_content_type($absolutePath) ?: 'image/png';

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode((string) file_get_contents($absolutePath)));
    }

    private function logoAbsolutePath(?string $logoPath): ?string
    {
        if (blank($logoPath)) {
            return null;
        }

        if (Storage::disk('public')->exists($logoPath)) {
            return Storage::disk('public')->path($logoPath);
        }

        if (is_file(public_path($logoPath)) && is_readable(public_path($logoPath))) {
            return public_path($logoPath);
        }

        if (is_file($logoPath) && is_readable($logoPath)) {
            return $logoPath;
        }

        return null;
    }

    private function filename(int $year, int $month, string $extension = 'pdf'): string
    {
        $monthName = strtolower(TravelRecordResource::monthOptions()[$month] ?? (string) $month);

        return sprintf('riepilogo-fatturazione-%s-%d.%s', $monthName, $year, $extension);
    }

    private function ensureExcelMemoryLimit(): void
    {
        $currentLimit = ini_get('memory_limit');

        if ($currentLimit === false || $currentLimit === '-1') {
            return;
        }

        if ($this->memoryToBytes($currentLimit) < 512 * 1024 * 1024) {
            ini_set('memory_limit', '512M');
        }
    }

    private function memoryToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
