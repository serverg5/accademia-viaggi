<?php

namespace App\Services;

use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelYear;
use App\Models\User;
use App\Support\Permissions;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TravelRecordService
{
    public const FIELD_STATE_PREFIX = 'dynamic';

    private const TECHNICAL_KEYS = [
        'year',
        'month',
        'record_date',
        'practice_code',
    ];

    public function formStateForRecord(TravelRecord $record): array
    {
        $state = [
            'year' => $record->year,
            'month' => $record->month,
            'record_date' => $record->record_date?->toDateString(),
            'practice_code' => $record->practice_code,
        ];

        foreach ($this->columns() as $column) {
            $state[self::FIELD_STATE_PREFIX][$column->key] = $this->valueForColumn($record, $column);
        }

        return $state;
    }

    public function prepareForPersistence(array $data, ?User $user = null): array
    {
        $dynamicValues = $data[self::FIELD_STATE_PREFIX] ?? [];

        $year = (int) ($dynamicValues['year'] ?? $data['year'] ?? now()->year);
        $month = (int) ($dynamicValues['month'] ?? $data['month'] ?? now()->month);
        $recordDate = $this->normalizeDate($dynamicValues['record_date'] ?? $data['record_date'] ?? null);
        $practiceCode = $this->normalizeNullableString($dynamicValues['practice_code'] ?? $data['practice_code'] ?? null);

        $travelYear = TravelYear::query()->firstOrCreate(
            ['year' => $year],
            [
                'status' => TravelYear::STATUS_ACTIVE,
                'is_locked' => false,
            ],
        );

        return [
            'travel_year_id' => $travelYear->getKey(),
            'year' => $year,
            'month' => $month,
            'record_date' => $recordDate,
            'practice_code' => $practiceCode,
            'values' => $this->normalizeDynamicValues($dynamicValues),
            'updated_by' => $user?->getKey(),
        ];
    }

    public function ensureRecordCanBeChangedForYear(?User $user, int $year): bool
    {
        if ($user === null) {
            return false;
        }

        $travelYear = TravelYear::query()->where('year', $year)->first();

        if ($travelYear === null || ! $travelYear->is_locked) {
            return true;
        }

        return $user->can(Permissions::UNLOCK_YEARS);
    }

    public function valueForColumn(TravelRecord $record, TravelColumn $column): mixed
    {
        return match ($column->key) {
            'year' => $record->year,
            'month' => $record->month,
            'record_date' => $record->record_date?->toDateString(),
            'practice_code' => $record->practice_code,
            default => data_get($record->values ?? [], $column->key),
        };
    }

    public function formattedValueForColumn(TravelRecord $record, TravelColumn $column): string
    {
        $value = $this->valueForColumn($record, $column);

        if ($value === null || $value === '') {
            return '';
        }

        return match ($column->type) {
            TravelColumn::TYPE_DATE => $this->formatDate($value),
            TravelColumn::TYPE_MONEY => $this->formatMoney($value),
            TravelColumn::TYPE_BOOLEAN => $value ? "S\u{00EC}" : 'No',
            default => (string) $value,
        };
    }

    public function formatMoney(mixed $value): string
    {
        $number = is_numeric($value)
            ? (float) $value
            : (float) str_replace(['.', ','], ['', '.'], (string) $value);

        return "\u{20AC} " . number_format($number, 2, ',', '.');
    }

    public function formatDate(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('d/m/Y');
        }

        return Carbon::parse($value)->format('d/m/Y');
    }

    public function columns(): Collection
    {
        return TravelColumn::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function isTechnicalColumn(TravelColumn $column): bool
    {
        return in_array($column->key, self::TECHNICAL_KEYS, true);
    }

    private function normalizeDynamicValues(array $values): array
    {
        $normalized = [];

        foreach ($this->columns() as $column) {
            if ($this->isTechnicalColumn($column)) {
                continue;
            }

            $value = $values[$column->key] ?? null;

            $normalized[$column->key] = match ($column->type) {
                TravelColumn::TYPE_BOOLEAN => (bool) $value,
                TravelColumn::TYPE_DATE => $this->normalizeDate($value),
                TravelColumn::TYPE_MONEY => $this->normalizeMoney($value),
                TravelColumn::TYPE_NUMBER => $value === null || $value === '' ? null : (float) $value,
                default => $value,
            };
        }

        return $normalized;
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toDateString();
        }

        return Carbon::parse($value)->toDateString();
    }

    private function normalizeMoney(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return (float) str_replace(['.', ','], ['', '.'], (string) $value);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
