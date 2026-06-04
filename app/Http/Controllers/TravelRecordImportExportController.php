<?php

namespace App\Http\Controllers;

use App\Services\TravelRecordImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TravelRecordImportExportController extends Controller
{
    public function template(Request $request, TravelRecordImportExportService $service, string $format = TravelRecordImportExportService::FORMAT_XLSX): BinaryFileResponse
    {
        $this->authorizeAdmin($request);

        return $service->downloadTemplate($format);
    }

    public function export(Request $request, TravelRecordImportExportService $service, string $format = TravelRecordImportExportService::FORMAT_XLSX): BinaryFileResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ]);

        return $service->downloadRecords(
            isset($validated['year']) ? (int) $validated['year'] : null,
            isset($validated['month']) ? (int) $validated['month'] : null,
            $format,
        );
    }

    public function preview(Request $request, TravelRecordImportExportService $service): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:10240'],
        ]);

        session(['travel_record_import_preview' => $service->preview($validated['file'])]);

        return redirect()
            ->route('filament.admin.pages.import-export-records')
            ->with('status', 'Anteprima import generata.');
    }

    public function confirm(Request $request, TravelRecordImportExportService $service): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $preview = session('travel_record_import_preview');

        if (! is_array($preview)) {
            return redirect()
                ->route('filament.admin.pages.import-export-records')
                ->withErrors(['file' => 'Nessuna anteprima import disponibile.']);
        }

        $result = $service->confirm($preview, $request->user());

        if (! empty($result['errors'])) {
            return redirect()
                ->route('filament.admin.pages.import-export-records')
                ->withErrors(['file' => implode(' ', $result['errors'])]);
        }

        session()->forget('travel_record_import_preview');

        return redirect()
            ->route('filament.admin.pages.import-export-records')
            ->with('status', sprintf('Import completato: %d creati, %d aggiornati.', $result['created'], $result['updated']));
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        session()->forget('travel_record_import_preview');

        return redirect()->route('filament.admin.pages.import-export-records');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
