<?php

namespace App\Http\Controllers;

use App\Services\BillingExportService;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class BillingExportController extends Controller
{
    public function pdf(Request $request, BillingExportService $billingExportService): Response
    {
        abort_unless($request->user()?->can(Permissions::EXPORT_BILLING), 403);

        [$year, $month] = $this->period($request);

        return $billingExportService->downloadPdf($year, $month);
    }

    public function excel(Request $request, BillingExportService $billingExportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can(Permissions::EXPORT_BILLING), 403);

        [$year, $month] = $this->period($request);

        return $billingExportService->downloadExcel($year, $month);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function period(Request $request): array
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        return [
            (int) $validated['year'],
            (int) $validated['month'],
        ];
    }
}
