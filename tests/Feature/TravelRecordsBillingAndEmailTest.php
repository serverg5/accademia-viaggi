<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelYear;
use App\Services\BillingSummaryService;
use App\Services\EmailSummaryService;
use App\Services\TravelRecordService;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TravelRecordsBillingAndEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTravelDomain();
    }

    public function test_record_permissions_and_locked_year_rules_are_enforced(): void
    {
        $admin = $this->adminUser();
        $operator = $this->operatorUser();
        $travelYear = TravelYear::query()->create([
            'year' => 2035,
            'status' => TravelYear::STATUS_ACTIVE,
            'is_locked' => false,
        ]);
        $record = $this->travelRecord(2035, 3, 'LOCK-001', 100, 200, $travelYear);

        $this->assertTrue(Gate::forUser($admin)->allows('create', TravelRecord::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $record));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $record));
        $this->assertTrue(Gate::forUser($operator)->allows('create', TravelRecord::class));
        $this->assertTrue(Gate::forUser($operator)->allows('update', $record));
        $this->assertFalse(Gate::forUser($operator)->allows('delete', $record));

        $travelYear->update(['is_locked' => true]);
        $this->assertFalse(Gate::forUser($operator)->allows('update', $record->refresh()));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $record));
    }

    public function test_dynamic_values_are_saved_in_json_and_technical_fields_stay_synchronized(): void
    {
        $operator = $this->operatorUser();
        $payload = app(TravelRecordService::class)->prepareForPersistence([
            TravelRecordService::FIELD_STATE_PREFIX => [
                'year' => 2036,
                'month' => 4,
                'record_date' => '2036-04-12',
                'practice_code' => 'SYNC-001',
                'departure_from' => 'Padova',
                'arrival' => 'Roma',
                'transport' => 'treno',
                'costs' => '1250,50',
                'sale' => '1800.75',
            ],
        ], $operator);

        $record = TravelRecord::query()->create($payload + ['created_by' => $operator->getKey()]);

        $this->assertSame(2036, $record->year);
        $this->assertSame(4, $record->month);
        $this->assertSame('2036-04-12', $record->record_date->toDateString());
        $this->assertSame('SYNC-001', $record->practice_code);
        $this->assertSame('Padova', $record->values['departure_from']);
        $this->assertSame('Roma', $record->values['arrival']);
        $this->assertSame('treno', $record->values['transport']);
        $this->assertSame(1250.50, $record->values['costs']);
        $this->assertSame(1800.75, $record->values['sale']);
        $this->assertArrayNotHasKey('year', $record->values);
        $this->assertArrayNotHasKey('month', $record->values);
        $this->assertArrayNotHasKey('record_date', $record->values);
        $this->assertArrayNotHasKey('practice_code', $record->values);
    }

    public function test_money_values_are_formatted_with_italian_euro_style(): void
    {
        $this->assertSame("\u{20AC} 1.250,00", app(TravelRecordService::class)->formatMoney(1250));
        $this->assertSame("\u{20AC} 1.250,50", app(TravelRecordService::class)->formatMoney('1.250,50'));
    }

    public function test_billing_filters_columns_required_fields_labels_and_totals(): void
    {
        $summary = app(BillingSummaryService::class);
        TravelColumn::query()->where('key', 'practice_code')->update([
            'label' => 'Codice dossier',
            'is_visible_in_billing' => false,
        ]);
        TravelColumn::query()->where('key', 'costs')->update(['label' => 'Costo pratica']);
        TravelColumn::query()->where('key', 'sale')->update(['label' => 'Ricavo pratica']);
        TravelColumn::query()->where('key', 'arrival')->update(['is_visible_in_billing' => true]);
        TravelColumn::query()->where('key', 'departure_from')->update(['is_visible_in_billing' => false]);

        $this->travelRecord(2037, 5, 'BILL-001', 100.10, 200.20, null, ['arrival' => 'Milano']);
        $this->travelRecord(2037, 5, 'BILL-002', 50.40, 70.30, null, ['arrival' => 'Torino']);
        $this->travelRecord(2037, 6, 'BILL-003', 900, 1000, null, ['arrival' => 'Napoli']);

        $this->assertSame(['BILL-001', 'BILL-002'], $summary->query(2037, 5)->pluck('practice_code')->all());

        $columns = $summary->columns();
        $this->assertContains('practice_code', $columns->pluck('key')->all());
        $this->assertContains('costs', $columns->pluck('key')->all());
        $this->assertContains('sale', $columns->pluck('key')->all());
        $this->assertContains('arrival', $columns->pluck('key')->all());
        $this->assertNotContains('departure_from', $columns->pluck('key')->all());
        $this->assertSame('Codice dossier', $columns->firstWhere('key', 'practice_code')->label);
        $this->assertSame('Costo pratica', $columns->firstWhere('key', 'costs')->label);
        $this->assertSame('Ricavo pratica', $columns->firstWhere('key', 'sale')->label);

        $totals = $summary->totals(2037, 5);
        $this->assertEquals(150.50, $totals['costs']);
        $this->assertEquals(270.50, $totals['sale']);
        $this->assertArrayNotHasKey('profit', $totals);
        $this->assertArrayNotHasKey('difference', $totals);
    }

    public function test_email_summary_uses_filters_dynamic_labels_totals_and_no_company_footer(): void
    {
        CompanySetting::query()->create([
            'company_name' => 'Azienda da non includere',
            'logo_path' => 'logo.png',
            'bank_name' => 'Banca Test',
            'iban' => 'IT00TEST',
            'footer_notes' => 'Note footer',
        ]);
        TravelColumn::query()->where('key', 'practice_code')->update(['label' => 'Codice dossier']);
        TravelColumn::query()->where('key', 'costs')->update(['label' => 'Costo pratica']);
        TravelColumn::query()->where('key', 'sale')->update(['label' => 'Ricavo pratica']);

        $this->travelRecord(2038, 7, 'EMAIL-001', 1250, 1800);
        $this->travelRecord(2038, 8, 'EMAIL-002', 999, 1999);

        $summary = app(EmailSummaryService::class)->summary(2038, 7);

        $this->assertSame('Riepilogo fatturazione luglio 2038', $summary['subject']);
        $this->assertFalse($summary['is_too_long']);
        $this->assertStringStartsWith('mailto:?subject=', $summary['mailto_url']);
        $this->assertStringContainsString('Luglio 2038', $summary['body']);
        $this->assertStringContainsString('Codice dossier: EMAIL-001', $summary['body']);
        $this->assertStringNotContainsString('EMAIL-002', $summary['body']);
        $this->assertStringContainsString('Costo pratica: ' . "\u{20AC} 1.250,00", $summary['body']);
        $this->assertStringContainsString('Ricavo pratica: ' . "\u{20AC} 1.800,00", $summary['body']);
        $this->assertStringContainsString('Totale Costo pratica: ' . "\u{20AC} 1.250,00", $summary['body']);
        $this->assertStringContainsString('Totale Ricavo pratica: ' . "\u{20AC} 1.800,00", $summary['body']);
        $this->assertStringNotContainsString('Azienda da non includere', $summary['body']);
        $this->assertStringNotContainsString('logo.png', $summary['body']);
        $this->assertStringNotContainsString('IT00TEST', $summary['body']);
        $this->assertStringNotContainsString('Note footer', $summary['body']);
    }

    public function test_email_summary_fallback_is_triggered_for_long_summaries(): void
    {
        for ($index = 1; $index <= 45; $index++) {
            $this->travelRecord(2039, 9, sprintf('LONG-%03d', $index), 10, 20);
        }

        $summary = app(EmailSummaryService::class)->summary(2039, 9);

        $this->assertTrue($summary['is_too_long']);
        $this->assertGreaterThan($summary['max_length'], $summary['mailto_length']);
    }

    private function travelRecord(
        int $year,
        int $month,
        string $practiceCode,
        float $costs,
        float $sale,
        ?TravelYear $travelYear = null,
        array $values = [],
    ): TravelRecord {
        $travelYear ??= TravelYear::query()->firstOrCreate([
            'year' => $year,
        ], [
            'status' => TravelYear::STATUS_ACTIVE,
            'is_locked' => false,
        ]);

        return TravelRecord::query()->create([
            'travel_year_id' => $travelYear->getKey(),
            'year' => $year,
            'month' => $month,
            'record_date' => sprintf('%d-%02d-10', $year, $month),
            'practice_code' => $practiceCode,
            'values' => array_merge([
                'costs' => $costs,
                'sale' => $sale,
            ], $values),
        ]);
    }
}
