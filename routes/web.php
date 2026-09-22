<?php

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PublicReportController;
use App\Http\Controllers\Site\ReportController;
use App\Http\Controllers\Site\ReportTrackingController;
use App\Http\Controllers\Site\ReportUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/lapor', [ReportController::class, 'create'])->name('reports.create');
Route::post('/lapor', [ReportController::class, 'store'])
    ->middleware('throttle:report-submission')
    ->name('reports.store');
Route::post('/lapor/uploads', ReportUploadController::class)
    ->middleware('throttle:report-upload-authorizations')
    ->name('reports.uploads.authorize');
Route::get('/lapor/sukses/{reportCode}', [ReportController::class, 'success'])->name('reports.success');

Route::get('/cek-laporan', ReportTrackingController::class)->name('reports.track');
Route::get('/laporan', [PublicReportController::class, 'index'])->name('public-reports.index');
Route::get('/laporan/{reportCode}', [PublicReportController::class, 'show'])->name('public-reports.show');

Route::view('/tentang', 'site.about.index')->name('about');
Route::view('/bantuan-situs', 'site.support')->name('site-support');
