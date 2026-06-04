<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelSelectOption;
use App\Models\TravelYear;
use App\Models\User;
use App\Services\TravelRecordService;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use LogicException;

class DemoDataSeeder extends Seeder
{
    private array $stats = [
        'years' => 0,
        'select_options' => 0,
        'records' => 0,
        'company_settings' => 0,
    ];

    public function run(): void
    {
        $this->guardEnvironment();

        $this->call([
            RoleAndPermissionSeeder::class,
            TravelColumnSeeder::class,
            TravelYearSeeder::class,
        ]);

        $years = $this->seedYears();
        $this->seedCompanySettings();
        $this->seedSelectOptions();
        $this->seedTravelRecords($years);

        $this->command?->info(sprintf(
            'Demo data: %d anni, %d opzioni, %d record viaggio, %d impostazioni azienda.',
            $this->stats['years'],
            $this->stats['select_options'],
            $this->stats['records'],
            $this->stats['company_settings'],
        ));
    }

    private function guardEnvironment(): void
    {
        if (app()->isProduction() && ! config('demo.allow_production_seed', false)) {
            throw new LogicException('DemoDataSeeder non puo essere eseguito in produzione senza consenso esplicito.');
        }
    }

    private function seedYears(): array
    {
        $currentYear = now()->year;
        $years = [
            'current' => [
                'year' => $currentYear,
                'status' => TravelYear::STATUS_ACTIVE,
                'is_locked' => false,
            ],
            'previous' => [
                'year' => $currentYear - 1,
                'status' => TravelYear::STATUS_ARCHIVED,
                'is_locked' => true,
            ],
            'older' => [
                'year' => $currentYear - 2,
                'status' => TravelYear::STATUS_ARCHIVED,
                'is_locked' => true,
            ],
        ];

        foreach ($years as $key => $data) {
            $years[$key] = TravelYear::query()->updateOrCreate(
                ['year' => $data['year']],
                [
                    'status' => $data['status'],
                    'is_locked' => $data['is_locked'],
                ],
            );
            $this->stats['years']++;
        }

        return $years;
    }

    private function seedCompanySettings(): void
    {
        if (CompanySetting::query()->exists()) {
            return;
        }

        CompanySetting::factory()->create();
        $this->stats['company_settings'] = 1;
    }

    private function seedSelectOptions(): void
    {
        $optionSets = [
            'transport' => [
                'Aereo' => 'aereo',
                'Treno' => 'treno',
                'Bus' => 'bus',
                'Auto' => 'auto',
                'Nave' => 'nave',
            ],
            'departure_from' => [
                'Milano' => 'milano',
                'Roma' => 'roma',
                'Napoli' => 'napoli',
                'Torino' => 'torino',
                'Bologna' => 'bologna',
            ],
            'arrival' => [
                'Parigi' => 'parigi',
                'Londra' => 'londra',
                'Madrid' => 'madrid',
                'Berlino' => 'berlino',
                'Amsterdam' => 'amsterdam',
            ],
        ];

        foreach ($optionSets as $columnKey => $options) {
            $column = TravelColumn::query()
                ->where('key', $columnKey)
                ->where('type', TravelColumn::TYPE_SELECT)
                ->first();

            if (! $column) {
                continue;
            }

            $sortOrder = 10;

            foreach ($options as $label => $value) {
                TravelSelectOption::query()->updateOrCreate(
                    [
                        'travel_column_id' => $column->getKey(),
                        'value' => $value,
                    ],
                    [
                        'label' => $label,
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                    ],
                );
                $sortOrder += 10;
                $this->stats['select_options']++;
            }
        }
    }

    private function seedTravelRecords(array $years): void
    {
        $currentYear = $years['current'];
        $previousYear = $years['previous'];
        $currentMonth = now()->month;
        $otherMonth = $currentMonth === 1 ? 2 : $currentMonth - 1;
        $nextMonth = $currentMonth === 12 ? 11 : $currentMonth + 1;

        $records = [
            [
                'year' => $currentYear->year,
                'month' => $currentMonth,
                'day' => 3,
                'practice_code' => sprintf('DEMO-%d-%02d-001', $currentYear->year, $currentMonth),
                'departure_from' => 'Milano',
                'arrival' => 'Parigi',
                'transport' => 'aereo',
                'costs' => 1000.00,
                'sale' => 1400.00,
            ],
            [
                'year' => $currentYear->year,
                'month' => $currentMonth,
                'day' => 12,
                'practice_code' => sprintf('DEMO-%d-%02d-002', $currentYear->year, $currentMonth),
                'departure_from' => 'Roma',
                'arrival' => 'Londra',
                'transport' => 'treno',
                'costs' => 250.00,
                'sale' => 400.00,
            ],
            [
                'year' => $currentYear->year,
                'month' => $currentMonth,
                'day' => 21,
                'practice_code' => sprintf('DEMO-%d-%02d-003', $currentYear->year, $currentMonth),
                'departure_from' => 'Bologna',
                'arrival' => 'Madrid',
                'transport' => 'bus',
                'costs' => 500.00,
                'sale' => 700.00,
            ],
            [
                'year' => $currentYear->year,
                'month' => $otherMonth,
                'day' => 8,
                'practice_code' => sprintf('DEMO-%d-%02d-001', $currentYear->year, $otherMonth),
                'departure_from' => 'Napoli',
                'arrival' => 'Berlino',
                'transport' => 'auto',
                'costs' => 320.00,
                'sale' => 520.00,
            ],
            [
                'year' => $currentYear->year,
                'month' => $nextMonth,
                'day' => 16,
                'practice_code' => sprintf('DEMO-%d-%02d-001', $currentYear->year, $nextMonth),
                'departure_from' => 'Torino',
                'arrival' => 'Amsterdam',
                'transport' => 'nave',
                'costs' => 780.00,
                'sale' => 1120.00,
            ],
            [
                'year' => $previousYear->year,
                'month' => $currentMonth,
                'day' => 7,
                'practice_code' => sprintf('DEMO-%d-%02d-LOCK-001', $previousYear->year, $currentMonth),
                'departure_from' => 'Milano',
                'arrival' => 'Londra',
                'transport' => 'aereo',
                'costs' => 900.00,
                'sale' => 1250.00,
            ],
            [
                'year' => $previousYear->year,
                'month' => $otherMonth,
                'day' => 19,
                'practice_code' => sprintf('DEMO-%d-%02d-LOCK-001', $previousYear->year, $otherMonth),
                'departure_from' => 'Roma',
                'arrival' => 'Parigi',
                'transport' => 'treno',
                'costs' => 450.00,
                'sale' => 650.00,
            ],
        ];

        foreach ($records as $record) {
            $this->upsertTravelRecord($record);
        }
    }

    private function upsertTravelRecord(array $record): void
    {
        $service = app(TravelRecordService::class);
        $prepared = $service->prepareForPersistence([
            TravelRecordService::FIELD_STATE_PREFIX => [
                'year' => $record['year'],
                'month' => $record['month'],
                'record_date' => sprintf('%d-%02d-%02d', $record['year'], $record['month'], $record['day']),
                'practice_code' => $record['practice_code'],
                'departure_from' => $record['departure_from'],
                'arrival' => $record['arrival'],
                'transport' => $record['transport'],
                'costs' => $record['costs'],
                'sale' => $record['sale'],
            ],
        ], $this->demoUser());

        TravelRecord::query()->updateOrCreate(
            ['practice_code' => $record['practice_code']],
            [
                ...$prepared,
                'created_by' => $this->demoUser()?->getKey(),
            ],
        );

        $this->stats['records']++;
    }

    private function demoUser(): ?User
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', Roles::ADMIN))
            ->first();
    }
}
