<?php

namespace Tests\Feature;

use App\Models\TravelColumn;
use App\Models\TravelRecord;
use App\Models\TravelYear;
use App\Services\TravelRecordImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class TravelRecordImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTravelDomain();
    }

    public function test_import_export_area_is_admin_only(): void
    {
        $this->actingAs($this->adminUser())
            ->get('/admin/import-export-records')
            ->assertOk();

        $this->actingAs($this->operatorUser())
            ->get('/admin/import-export-records')
            ->assertForbidden();
    }

    public function test_template_uses_current_dynamic_labels_and_keys(): void
    {
        $this->actingAs($this->adminUser());
        TravelColumn::query()->where('key', 'practice_code')->update(['label' => 'Codice dossier']);

        $response = app(TravelRecordImportExportService::class)->downloadTemplate();
        $sheet = IOFactory::load($response->getFile()->getPathname())->getActiveSheet();

        $this->assertSame('Codice dossier', $sheet->getCell('D1')->getValue());
        $this->assertSame('practice_code', $sheet->getCell('D2')->getValue());
        $this->assertFalse($sheet->getRowDimension(2)->getVisible());
    }

    public function test_import_preview_and_confirm_updates_duplicates_and_creates_new_records(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);
        $year = 2050;
        $month = 4;
        $travelYear = TravelYear::query()->create([
            'year' => $year,
            'status' => TravelYear::STATUS_ACTIVE,
            'is_locked' => false,
        ]);
        TravelRecord::query()->create([
            'travel_year_id' => $travelYear->getKey(),
            'year' => $year,
            'month' => $month,
            'record_date' => '2050-04-01',
            'practice_code' => 'IMP-001',
            'values' => [
                'departure_from' => 'Roma',
                'arrival' => 'Parigi',
                'transport' => 'treno',
                'costs' => 10,
                'sale' => 20,
            ],
        ]);

        $file = new UploadedFile(
            $this->xlsxImportFile($year, $month),
            'import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $preview = app(TravelRecordImportExportService::class)->preview($file);

        $this->assertSame(1, $preview['summary']['create']);
        $this->assertSame(1, $preview['summary']['update']);
        $this->assertSame(0, $preview['summary']['errors']);

        $result = app(TravelRecordImportExportService::class)->confirm($preview, $admin);

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame(2, TravelRecord::query()->where('year', $year)->count());
        $this->assertEquals(100.0, TravelRecord::query()->where('practice_code', 'IMP-001')->first()->values['costs']);
        $this->assertEquals(200.0, TravelRecord::query()->where('practice_code', 'IMP-002')->first()->values['costs']);
    }

    private function xlsxImportFile(int $year, int $month): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $keys = [
            'year',
            'month',
            'record_date',
            'practice_code',
            'departure_from',
            'arrival',
            'transport',
            'costs',
            'sale',
        ];

        foreach ($keys as $index => $key) {
            $column = TravelColumn::query()->where('key', $key)->firstOrFail();
            $sheet->setCellValue([$index + 1, 1], $column->label);
            $sheet->setCellValue([$index + 1, 2], $column->key);
        }

        $rows = [
            [$year, $month, '05/04/2050', 'IMP-001', 'Milano', 'Londra', 'Aereo', 100, 150],
            [$year, $month, '06/04/2050', 'IMP-002', 'Torino', 'Madrid', 'Treno', 200, 300],
        ];

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex + 3], $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'travel-import-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}
