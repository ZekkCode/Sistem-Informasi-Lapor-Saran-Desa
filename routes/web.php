<?php

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PublicReportController;
use App\Http\Controllers\Site\ReportController;
use App\Http\Controllers\Site\ReportTrackingController;
use App\Http\Controllers\Site\ReportUploadController;
use App\Http\Controllers\Site\LacakUsulanController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\UsulanController;
use App\Http\Controllers\Site\UsulanPublikController;
use App\Http\Controllers\Site\UsulanUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::view('/mulai', 'site.mulai')->name('mulai');

Route::get('/lapor', [ReportController::class, 'create'])->name('reports.create');
Route::post('/lapor', [ReportController::class, 'store'])
    ->middleware(['throttle:report-submission', 'formulir.terbuka:laporan'])
    ->name('reports.store');
Route::post('/lapor/uploads', ReportUploadController::class)
    ->middleware(['throttle:report-upload-authorizations', 'formulir.terbuka:laporan'])
    ->name('reports.uploads.authorize');
Route::get('/lapor/sukses/{reportCode}', [ReportController::class, 'success'])->name('reports.success');

Route::get('/cek-laporan', ReportTrackingController::class)->name('reports.track');
Route::get('/laporan', [PublicReportController::class, 'index'])->name('public-reports.index');
Route::get('/laporan/{reportCode}', [PublicReportController::class, 'show'])->name('public-reports.show');

Route::get('/usulan', [UsulanController::class, 'buat'])->name('usulan.create');
Route::post('/usulan', [UsulanController::class, 'simpan'])
    ->middleware(['throttle:suggestion-submission', 'formulir.terbuka:usulan'])
    ->name('usulan.store');
Route::post('/usulan/uploads', UsulanUploadController::class)
    ->middleware(['throttle:report-upload-authorizations', 'formulir.terbuka:usulan'])
    ->name('usulan.uploads.authorize');
Route::get('/usulan/sukses/{kode}', [UsulanController::class, 'sukses'])->name('usulan.sukses');
Route::get('/cek-usulan', LacakUsulanController::class)->name('usulan.lacak');
Route::get('/usulan-warga', [UsulanPublikController::class, 'daftar'])->name('public-usulan.index');
Route::get('/usulan-warga/{kode}', [UsulanPublikController::class, 'detail'])->name('public-usulan.show');

Route::redirect('/lapor/buat', '/lapor');
Route::redirect('/buat-laporan', '/lapor');
Route::redirect('/usulan/buat', '/usulan');
Route::redirect('/buat-usulan', '/usulan');

Route::view('/tentang', 'site.about.index')->name('about');
Route::view('/bantuan-situs', 'site.support')->name('site-support');
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap.xml');
Route::get('/peta-situs', [SitemapController::class, 'htmlSitemap'])->name('sitemap.html');
Route::redirect('/sitemap', '/peta-situs');

