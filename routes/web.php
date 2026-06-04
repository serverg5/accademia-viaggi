<?php

use App\Http\Controllers\BillingExportController;
use App\Http\Controllers\TravelRecordImportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')
    ->prefix('admin/fatturazione/esporta')
    ->name('billing.exports.')
    ->group(function (): void {
        Route::get('pdf', [BillingExportController::class, 'pdf'])->name('pdf');
        Route::get('excel', [BillingExportController::class, 'excel'])->name('excel');
    });

Route::middleware('auth')
    ->prefix('admin/import-export-records')
    ->name('travel-record-import-export.')
    ->group(function (): void {
        Route::get('template/{format?}', [TravelRecordImportExportController::class, 'template'])->name('template');
        Route::get('export/{format?}', [TravelRecordImportExportController::class, 'export'])->name('export');
        Route::post('preview', [TravelRecordImportExportController::class, 'preview'])->name('preview');
        Route::post('confirm', [TravelRecordImportExportController::class, 'confirm'])->name('confirm');
        Route::post('clear', [TravelRecordImportExportController::class, 'clear'])->name('clear');
    });
