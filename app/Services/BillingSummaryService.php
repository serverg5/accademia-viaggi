<?php

namespace App\Services;

use App\Models\TravelColumn;
use App\Models\TravelRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BillingSummaryService
{
    public const PRACTICE_CODE_KEY = 'practice_code';
    public const COSTS_KEY = 'costs';
    public const SALE_KEY = 'sale';

    private const REQUIRED_BILLING_KEYS = [
        self::PRACTICE_CODE_KEY,
        self::COSTS_KEY,
        self::SALE_KEY,
    ];

    public function query(int $year, int $month): Builder
    {
        return TravelRecord::query()
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('record_date')
            ->orderBy('practice_code')
            ->orderBy('id');
    }

    public function columns(): Collection
    {
        return TravelColumn::query()
            ->where(function (Builder $query): void {
                $query
                    ->where('is_visible_in_billing', true)
                    ->orWhereIn('key', self::REQUIRED_BILLING_KEYS);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function totals(int $year, int $month): array
    {
        $totals = [
            'costs' => 0.0,
            'sale' => 0.0,
        ];

        TravelRecord::query()
            ->where('year', $year)
            ->where('month', $month)
            ->select(['id', 'values'])
            ->chunkById(500, function (Collection $records) use (&$totals): void {
                foreach ($records as $record) {
                    $totals['costs'] += $this->numericValue($record, self::COSTS_KEY);
                    $totals['sale'] += $this->numericValue($record, self::SALE_KEY);
                }
            });

        return $totals;
    }

    public function formattedTotals(int $year, int $month): array
    {
        $totals = $this->totals($year, $month);
        $travelRecordService = app(TravelRecordService::class);

        return [
            'costs' => $travelRecordService->formatMoney($totals['costs']),
            'sale' => $travelRecordService->formatMoney($totals['sale']),
        ];
    }

    public function labelFor(string $key, string $fallback): string
    {
        return TravelColumn::query()
            ->where('key', $key)
            ->value('label') ?? $fallback;
    }

    public function numericValue(TravelRecord $record, string $key): float
    {
        $value = data_get($record->values ?? [], $key);

        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return (float) str_replace(['.', ','], ['', '.'], (string) $value);
    }
}
