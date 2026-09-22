<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DusunController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\QrSourceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReportExportController;
use App\Http\Controllers\Admin\ReportMediaController;
use App\Http\Controllers\Admin\ReportUploadController;
use App\Http\Controllers\Admin\ReportWorkflowController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
});

Route::middleware(['auth', 'active.admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/ekspor.csv', [ReportExportController::class, 'csv'])->name('reports.export.csv');
    Route::get('/laporan/cetak', [ReportExportController::class, 'print'])->name('reports.print');
    Route::get('/laporan/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::patch('/laporan/{report}/alur', [ReportWorkflowController::class, 'update'])->name('reports.workflow.update');
    Route::post('/laporan/{report}/uploads', ReportUploadController::class)
        ->middleware('throttle:report-upload-authorizations')
        ->name('reports.uploads.authorize');
    Route::get('/media/{media}', ReportMediaController::class)->name('media.show');

    Route::middleware('super.admin')->group(function () {
        Route::get('/data-master', MasterDataController::class)->name('master.index');
        Route::post('/data-master/dusun', [DusunController::class, 'store'])->name('master.dusuns.store');
        Route::patch('/data-master/dusun/{dusun}', [DusunController::class, 'update'])->name('master.dusuns.update');
        Route::post('/data-master/kategori', [CategoryController::class, 'store'])->name('master.categories.store');
        Route::patch('/data-master/kategori/{category}', [CategoryController::class, 'update'])->name('master.categories.update');
        Route::post('/data-master/jenis-masalah', [SubcategoryController::class, 'store'])->name('master.subcategories.store');
        Route::patch('/data-master/jenis-masalah/{subcategory}', [SubcategoryController::class, 'update'])->name('master.subcategories.update');
        Route::post('/data-master/sumber-qr', [QrSourceController::class, 'store'])->name('master.qr-sources.store');
        Route::patch('/data-master/sumber-qr/{qrSource}', [QrSourceController::class, 'update'])->name('master.qr-sources.update');

        Route::get('/petugas', [UserController::class, 'index'])->name('users.index');
        Route::post('/petugas', [UserController::class, 'store'])->name('users.store');
        Route::patch('/petugas/{user}', [UserController::class, 'update'])->name('users.update');
    });
});
