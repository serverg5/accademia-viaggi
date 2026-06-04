<?php

namespace App\Exports;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Models\CompanySetting;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Services\BillingSummaryService;
use App\Services\TravelRecordService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BillingSummaryExport implements FromArray, WithColumnFormatting, WithColumnWidths, WithDrawings, WithStyles, WithTitle
{
    private int $headerRow = 8;

    private int $totalsStartRow;

    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly ?CompanySetting $company,
        private readonly Collection $columns,
        private readonly Collection $records,
        private readonly array $totals,
        private readonly ?string $logoPath = null,
    ) {
        $this->totalsStartRow = $this->headerRow + $this->records->count() + 2;
    }

    public function array(): array
    {
        $rows = [
            ['Azienda', $this->company?->company_name ?? ''],
            ['Indirizzo', $this->company?->address ?? ''],
            ['Contatti', $this->contacts()],
            ['Dettagli fiscali', $this->fiscalDetails()],
            [' ', ' '],
            [sprintf('Riepilogo Fatturazione - %s %d', $this->monthName(), $this->year)],
            [' ', ' '],
            $this->headings(),
        ];

        foreach ($this->records as $record) {
            $rows[] = $this->recordRow($record);
        }

        $rows[] = [' ', ' '];
        $rows[] = [$this->totalLabel(BillingSummaryService::COSTS_KEY, 'Costi'), $this->totals['raw']['costs'] ?? 0.0];
        $rows[] = [$this->totalLabel(BillingSummaryService::SALE_KEY, 'Vendita'), $this->totals['raw']['sale'] ?? 0.0];
        $rows[] = [' ', ' '];
        $rows[] = ['Dati bancari'];
        $rows[] = ['Banca', $this->company?->bank_name ?? ''];
        $rows[] = ['IBAN', $this->company?->iban ?? ''];
        $rows[] = ['Intestatario', $this->company?->bank_account_holder ?? ''];

        if (filled($this->company?->footer_notes)) {
            $rows[] = ['Note', $this->company->footer_notes];
        }

        return $rows;
    }

    public function columnFormats(): array
    {
        $formats = [];
        $currencyFormat = '"' . "\u{20AC}" . '" #.##0,00';

        foreach ($this->columns as $index => $column) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);

            if ($column->type === TravelColumn::TYPE_DATE) {
                $formats[$letter] = 'dd/mm/yyyy';
            }

            if ($column->type === TravelColumn::TYPE_MONEY) {
                $formats[$letter] = $currencyFormat;
            }
        }

        $formats['B'] = $formats['B'] ?? NumberFormat::FORMAT_GENERAL;
        $formats['B' . $this->totalsStartRow] = $currencyFormat;
        $formats['B' . ($this->totalsStartRow + 1)] = $currencyFormat;

        return $formats;
    }

    public function columnWidths(): array
    {
        $widths = [];

        foreach ($this->columns as $index => $column) {
            $widths[Coordinate::stringFromColumnIndex($index + 1)] = match ($column->type) {
                TravelColumn::TYPE_DATE => 14,
                TravelColumn::TYPE_MONEY, TravelColumn::TYPE_NUMBER => 16,
                default => 22,
            };
        }

        return $widths;
    }

    public function drawings(): BaseDrawing|array
    {
        if ($this->logoPath === null || ! is_readable($this->logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo aziendale');
        $drawing->setPath($this->logoPath);
        $drawing->setCoordinates('D1');
        $drawing->setHeight(72);

        return $drawing;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(max(1, $this->columns->count()));
        $lastTableRow = $this->headerRow + $this->records->count();

        $sheet->mergeCells("A6:{$lastColumn}6");
        $sheet->getStyle('B1:B4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(15);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$this->headerRow}:{$lastColumn}{$this->headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A{$this->headerRow}:{$lastColumn}{$lastTableRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->getStyle("A{$this->totalsStartRow}:B" . ($this->totalsStartRow + 1))->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
        ]);
        $sheet->getStyle('A' . ($this->totalsStartRow + 3))->getFont()->setBold(true);
        $sheet->freezePane("A" . ($this->headerRow + 1));

        return [];
    }

    public function title(): string
    {
        return 'Fatturazione';
    }

    private function headings(): array
    {
        return $this->columns
            ->map(fn (TravelColumn $column): string => $column->label)
            ->all();
    }

    private function recordRow(TravelRecord $record): array
    {
        $travelRecordService = app(TravelRecordService::class);

        return $this->columns
            ->map(fn (TravelColumn $column): mixed => $this->excelValueForColumn($record, $column, $travelRecordService))
            ->all();
    }

    private function excelValueForColumn(TravelRecord $record, TravelColumn $column, TravelRecordService $travelRecordService): mixed
    {
        $value = $travelRecordService->valueForColumn($record, $column);

        if ($value === null || $value === '') {
            return null;
        }

        return match ($column->type) {
            TravelColumn::TYPE_DATE => ExcelDate::PHPToExcel($value),
            TravelColumn::TYPE_MONEY, TravelColumn::TYPE_NUMBER => $this->numericValue($value),
            TravelColumn::TYPE_BOOLEAN => $value ? "S\u{00EC}" : 'No',
            default => (string) $value,
        };
    }

    private function numericValue(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return (float) str_replace(['.', ','], ['', '.'], (string) $value);
    }

    private function contacts(): string
    {
        return collect([
            $this->company?->email,
            $this->company?->phone,
        ])->filter()->implode(' - ');
    }

    private function fiscalDetails(): string
    {
        return collect([
            filled($this->company?->vat_number) ? 'P. IVA: ' . $this->company->vat_number : null,
            filled($this->company?->tax_code) ? 'Codice fiscale: ' . $this->company->tax_code : null,
        ])->filter()->implode(' - ');
    }

    private function monthName(): string
    {
        return TravelRecordResource::monthOptions()[$this->month] ?? (string) $this->month;
    }

    private function totalLabel(string $key, string $fallback): string
    {
        $label = $this->columns
            ->firstWhere('key', $key)
            ?->label ?? $fallback;

        return 'Totale ' . $label;
    }
}
