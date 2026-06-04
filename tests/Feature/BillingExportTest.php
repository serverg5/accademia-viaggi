<?php

namespace Tests\Feature;

use App\Models\TravelRecord;
use App\Models\TravelYear;
use App\Services\BillingExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class BillingExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTravelDomain();
    }

    public function test_pdf_and_excel_page_actions_require_export_billing_permission(): void
    {
        $this->actingAs($this->operatorUser());

        $this->get(route('billing.exports.pdf', ['year' => 2040, 'month' => 3]))
            ->assertForbidden();

        $this->get(route('billing.exports.excel', ['year' => 2040, 'month' => 3]))
            ->assertForbidden();
    }

    public function test_pdf_export_generates_filtered_period_filename(): void
    {
        $this->actingAs($this->adminUser());
        $this->travelRecord(2040, 3, 'PDF-001', 300, 500);

        $response = $this->get(route('billing.exports.pdf', ['year' => 2040, 'month' => 3]));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString(
            'riepilogo-fatturazione-marzo-2040.pdf',
            $response->headers->get('Content-Disposition'),
        );
    }

    public function test_excel_export_respects_selected_period_and_includes_totals(): void
    {
        $this->travelRecord(2041, 4, 'XLS-001', 100, 250);
        $this->travelRecord(2041, 4, 'XLS-002', 50.5, 75.25);
        $this->travelRecord(2041, 5, 'XLS-OTHER', 999, 999);

        $response = app(BillingExportService::class)->downloadExcel(2041, 4);
        $path = $response->getFile()->getPathname();
        $sheet = IOFactory::load($path)->getActiveSheet();

        $this->assertStringContainsString(
            'riepilogo-fatturazione-aprile-2041.xlsx',
            $response->headers->get('Content-Disposition'),
        );
        $this->assertSame('Riepilogo Fatturazione - Aprile 2041', $sheet->getCell('A6')->getValue());
        $this->assertSame('Codice pratica', $sheet->getCell('A8')->getValue());
        $this->assertSame('XLS-001', $sheet->getCell('A9')->getValue());
        $this->assertSame('XLS-002', $sheet->getCell('A10')->getValue());
        $this->assertSame('Totale Costi', $sheet->getCell('A12')->getValue());
        $this->assertSame(150.5, $sheet->getCell('B12')->getValue());
        $this->assertSame('"' . "\u{20AC}" . '" #.##0,00', $sheet->getStyle('B12')->getNumberFormat()->getFormatCode());
        $this->assertSame('Totale Vendita', $sheet->getCell('A13')->getValue());
        $this->assertSame(325.25, $sheet->getCell('B13')->getValue());
        $this->assertNotSame('XLS-OTHER', $sheet->getCell('A11')->getValue());
    }

    private function travelRecord(int $year, int $month, string $practiceCode, float $costs, float $sale): TravelRecord
    {
        $travelYear = TravelYear::query()->firstOrCreate([
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
            'values' => [
                'costs' => $costs,
                'sale' => $sale,
            ],
        ]);
    }
}
