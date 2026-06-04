<?php

namespace App\Services;

use App\Filament\Resources\TravelRecords\TravelRecordResource;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use Illuminate\Support\Collection;

class EmailSummaryService
{
    private const MAILTO_MAX_LENGTH = 1800;

    public function summary(int $year, int $month): array
    {
        $billingSummaryService = app(BillingSummaryService::class);
        $columns = $billingSummaryService->columns();
        $records = $billingSummaryService->query($year, $month)->get();
        $totals = $billingSummaryService->formattedTotals($year, $month);

        $subject = sprintf('Riepilogo fatturazione %s %d', strtolower($this->monthName($month)), $year);
        $body = $this->body($year, $month, $columns, $records, $totals);
        $mailtoUrl = $this->mailtoUrl($subject, $body);

        return [
            'subject' => $subject,
            'body' => $body,
            'mailto_url' => $mailtoUrl,
            'mailto_length' => strlen($mailtoUrl),
            'is_too_long' => strlen($mailtoUrl) > self::MAILTO_MAX_LENGTH,
            'max_length' => self::MAILTO_MAX_LENGTH,
        ];
    }

    private function body(int $year, int $month, Collection $columns, Collection $records, array $totals): string
    {
        $lines = [
            sprintf('Riepilogo fatturazione - %s %d', $this->monthName($month), $year),
            '',
            'Righe fatturazione:',
        ];

        if ($records->isEmpty()) {
            $lines[] = 'Nessun record presente per il periodo selezionato.';
        }

        foreach ($records as $index => $record) {
            $lines[] = sprintf('%d. %s', $index + 1, $this->recordLine($record, $columns));
        }

        $lines[] = '';
        $lines[] = 'Totali:';
        $lines[] = sprintf(
            '%s: %s',
            $this->totalLabel($columns, BillingSummaryService::COSTS_KEY, 'Costi'),
            $totals['costs'],
        );
        $lines[] = sprintf(
            '%s: %s',
            $this->totalLabel($columns, BillingSummaryService::SALE_KEY, 'Vendita'),
            $totals['sale'],
        );

        return implode(PHP_EOL, $lines);
    }

    private function recordLine(TravelRecord $record, Collection $columns): string
    {
        $travelRecordService = app(TravelRecordService::class);

        return $columns
            ->map(fn (TravelColumn $column): string => sprintf(
                '%s: %s',
                $column->label,
                $travelRecordService->formattedValueForColumn($record, $column) ?: '-',
            ))
            ->implode(' | ');
    }

    private function totalLabel(Collection $columns, string $key, string $fallback): string
    {
        $label = $columns
            ->firstWhere('key', $key)
            ?->label ?? $fallback;

        return 'Totale ' . $label;
    }

    private function mailtoUrl(string $subject, string $body): string
    {
        return sprintf('mailto:?subject=%s&body=%s', rawurlencode($subject), rawurlencode($body));
    }

    private function monthName(int $month): string
    {
        return TravelRecordResource::monthOptions()[$month] ?? (string) $month;
    }
}
