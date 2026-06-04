<?php

namespace App\Services;

use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelSelectOption;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TravelRecordImportExportService
{
    public const FORMAT_XLSX = 'xlsx';
    public const FORMAT_CSV = 'csv';

    public function downloadTemplate(string $format = self::FORMAT_XLSX): BinaryFileResponse
    {
        $format = $this->normalizeFormat($format);

        return $this->downloadSpreadsheet(
            $this->spreadsheetForRows([]),
            sprintf('modello-record-viaggi.%s', $format),
            $format,
        );
    }

    public function downloadRecords(?int $year = null, ?int $month = null, string $format = self::FORMAT_XLSX): BinaryFileResponse
    {
        $format = $this->normalizeFormat($format);

        $query = TravelRecord::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderBy('record_date')
            ->orderBy('practice_code');

        if ($year !== null) {
            $query->where('year', $year);
        }

        if ($month !== null) {
            $query->where('month', $month);
        }

        $filenameParts = ['record-viaggi'];

        if ($year !== null) {
            $filenameParts[] = (string) $year;
        }

        if ($month !== null) {
            $filenameParts[] = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        }

        return $this->downloadSpreadsheet(
            $this->spreadsheetForRows($query->get()),
            implode('-', $filenameParts) . ".{$format}",
            $format,
        );
    }

    public function preview(UploadedFile $file): array
    {
        $path = $file->store('imports/travel-records');
        $absolutePath = Storage::path($path);

        try {
            return $this->previewRows($this->readRows($absolutePath, $file->getClientOriginalExtension()));
        } finally {
            Storage::delete($path);
        }
    }

    public function confirm(array $preview, User $user): array
    {
        $rows = collect($preview['rows'] ?? []);

        if ($rows->contains(fn (array $row): bool => ! empty($row['errors']))) {
            return [
                'created' => 0,
                'updated' => 0,
                'errors' => ['Correggi gli errori prima di confermare l\'import.'],
            ];
        }

        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $prepared = $row['prepared'];
            $record = TravelRecord::withTrashed()
                ->where('year', $prepared['year'])
                ->where('practice_code', $prepared['practice_code'])
                ->first();

            if ($record) {
                $record->fill([
                    ...$prepared,
                    'updated_by' => $user->getKey(),
                ]);
                $record->restore();
                $record->save();
                $updated++;

                continue;
            }

            TravelRecord::query()->create([
                ...$prepared,
                'created_by' => $user->getKey(),
                'updated_by' => $user->getKey(),
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => [],
        ];
    }

    private function spreadsheetForRows(iterable $records): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $columns = $this->columns();

        foreach ($columns as $index => $column) {
            $columnIndex = $index + 1;
            $sheet->setCellValue([$columnIndex, 1], $column->label);
            $sheet->setCellValue([$columnIndex, 2], $column->key);
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setWidth($this->widthForColumn($column));
        }

        $sheet->getRowDimension(2)->setVisible(false);
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $rowIndex = 3;
        $recordService = app(TravelRecordService::class);

        foreach ($records as $record) {
            foreach ($columns as $columnIndex => $column) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex], $this->exportValue($recordService->valueForColumn($record, $column), $column));
            }

            $rowIndex++;
        }

        return $spreadsheet;
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename, string $format): BinaryFileResponse
    {
        $format = $this->normalizeFormat($format);
        $path = storage_path('app/private/exports/' . uniqid('travel-records-', true) . ".{$format}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $writer = $format === self::FORMAT_CSV
            ? new CsvWriter($spreadsheet)
            : new Xlsx($spreadsheet);

        if ($writer instanceof CsvWriter) {
            $spreadsheet->getActiveSheet()->removeRow(2);
            $writer->setDelimiter(';');
            $writer->setUseBOM(true);
        }

        $writer->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    private function readRows(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === self::FORMAT_CSV) {
            $reader = new Csv();
            $reader->setDelimiter(';');
            $reader->setInputEncoding('UTF-8');
            $spreadsheet = $reader->load($path);
        } else {
            $spreadsheet = IOFactory::load($path);
        }

        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }

    private function previewRows(array $rows): array
    {
        $columns = $this->columns();
        $keys = $this->columnKeys($rows, $columns);
        $dataStartRow = $this->hasTechnicalKeyRow($rows[1] ?? [], $columns) ? 2 : 1;
        $seen = [];
        $previewRows = [];

        foreach (array_slice($rows, $dataStartRow) as $offset => $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $rowNumber = $offset + $dataStartRow + 1;
            $dynamic = $this->dynamicValuesFromRow($row, $keys, $columns);
            $errors = $this->validateDynamicValues($dynamic);
            $duplicateKey = ($dynamic['year'] ?? '') . '|' . ($dynamic['practice_code'] ?? '');

            if (($dynamic['year'] ?? null) && ($dynamic['practice_code'] ?? null)) {
                if (isset($seen[$duplicateKey])) {
                    $errors[] = 'Codice pratica duplicato nel file per lo stesso anno.';
                }

                $seen[$duplicateKey] = true;
            }

            $prepared = null;
            $action = 'errore';

            if ($errors === []) {
                $prepared = app(TravelRecordService::class)->prepareForPersistence([
                    TravelRecordService::FIELD_STATE_PREFIX => $dynamic,
                ]);

                $action = TravelRecord::withTrashed()
                    ->where('year', $prepared['year'])
                    ->where('practice_code', $prepared['practice_code'])
                    ->exists()
                        ? 'aggiorna'
                        : 'crea';
            }

            $previewRows[] = [
                'row_number' => $rowNumber,
                'action' => $action,
                'errors' => $errors,
                'year' => $dynamic['year'] ?? null,
                'month' => $dynamic['month'] ?? null,
                'practice_code' => $dynamic['practice_code'] ?? null,
                'record_date' => $dynamic['record_date'] ?? null,
                'prepared' => $prepared,
            ];
        }

        return [
            'rows' => $previewRows,
            'summary' => [
                'create' => collect($previewRows)->where('action', 'crea')->count(),
                'update' => collect($previewRows)->where('action', 'aggiorna')->count(),
                'errors' => collect($previewRows)->filter(fn (array $row): bool => ! empty($row['errors']))->count(),
            ],
        ];
    }

    private function dynamicValuesFromRow(array $row, array $keys, Collection $columns): array
    {
        $values = [];

        foreach ($keys as $index => $key) {
            if (! $columns->firstWhere('key', $key)) {
                continue;
            }

            $values[$key] = $this->normalizeValue($row[$index] ?? null, $columns->firstWhere('key', $key));
        }

        return $values;
    }

    private function validateDynamicValues(array $values): array
    {
        $errors = [];

        if (blank($values['year'] ?? null)) {
            $errors[] = 'Anno mancante.';
        }

        if (blank($values['month'] ?? null)) {
            $errors[] = 'Mese mancante.';
        }

        if (blank($values['practice_code'] ?? null)) {
            $errors[] = 'Codice pratica mancante.';
        }

        if (($values['month'] ?? null) && ((int) $values['month'] < 1 || (int) $values['month'] > 12)) {
            $errors[] = 'Mese non valido.';
        }

        return $errors;
    }

    private function columnKeys(array $rows, Collection $columns): array
    {
        $headings = $rows[0] ?? [];
        $technicalRow = $rows[1] ?? [];

        if ($this->hasTechnicalKeyRow($technicalRow, $columns)) {
            return array_map(fn ($value): string => (string) $value, $technicalRow);
        }

        return array_map(fn ($heading): ?string => $this->keyForHeading((string) $heading, $columns), $headings);
    }

    private function hasTechnicalKeyRow(array $row, Collection $columns): bool
    {
        $validKeys = $columns->pluck('key')->all();
        $filled = array_filter($row, fn ($value): bool => filled($value));

        if ($filled === []) {
            return false;
        }

        return collect($filled)->every(fn ($value): bool => in_array((string) $value, $validKeys, true));
    }

    private function keyForHeading(string $heading, Collection $columns): ?string
    {
        $normalizedHeading = str($heading)->lower()->squish()->toString();

        return $columns
            ->first(fn (TravelColumn $column): bool => str($column->label)->lower()->squish()->toString() === $normalizedHeading)
            ?->key;
    }

    private function normalizeValue(mixed $value, TravelColumn $column): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($column->type) {
            TravelColumn::TYPE_DATE => $this->normalizeDateValue($value),
            TravelColumn::TYPE_MONEY => $this->normalizeMoneyValue($value),
            TravelColumn::TYPE_NUMBER => (int) $value,
            TravelColumn::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            TravelColumn::TYPE_SELECT => $this->normalizeSelectValue((string) $value, $column),
            default => (string) $value,
        };
    }

    private function normalizeDateValue(mixed $value): ?string
    {
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        $value = trim((string) $value);

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            $date = \DateTime::createFromFormat($format, $value);

            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return $value;
    }

    private function normalizeMoneyValue(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = str_replace(['€', ' '], ['', ''], (string) $value);

        if (str_contains($value, ',')) {
            return (float) str_replace(',', '.', str_replace('.', '', $value));
        }

        return (float) $value;
    }

    private function normalizeSelectValue(string $value, TravelColumn $column): string
    {
        $option = TravelSelectOption::query()
            ->where('travel_column_id', $column->getKey())
            ->where(fn ($query) => $query
                ->where('value', $value)
                ->orWhere('label', $value))
            ->first();

        return $option?->value ?? str($value)->slug()->toString();
    }

    private function exportValue(mixed $value, TravelColumn $column): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($column->type === TravelColumn::TYPE_DATE) {
            return app(TravelRecordService::class)->formatDate($value);
        }

        if ($column->type === TravelColumn::TYPE_SELECT) {
            return TravelSelectOption::query()
                ->where('travel_column_id', $column->getKey())
                ->where('value', $value)
                ->value('label') ?? $value;
        }

        return $value;
    }

    private function isEmptyRow(array $row): bool
    {
        return collect($row)->every(fn ($value): bool => blank($value));
    }

    private function widthForColumn(TravelColumn $column): int
    {
        return match ($column->type) {
            TravelColumn::TYPE_DATE => 14,
            TravelColumn::TYPE_MONEY, TravelColumn::TYPE_NUMBER => 16,
            default => 22,
        };
    }

    private function columns(): Collection
    {
        return app(TravelRecordService::class)->columns();
    }

    private function normalizeFormat(string $format): string
    {
        return strtolower($format) === self::FORMAT_CSV ? self::FORMAT_CSV : self::FORMAT_XLSX;
    }
}
