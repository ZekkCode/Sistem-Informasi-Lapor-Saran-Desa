<?php

use App\Http\Middleware\EnsureCanAccessReports;
use App\Http\Middleware\EnsureCanAccessUsulan;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\PastikanFormulirTerbuka;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);

        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin') || $request->is('admin/*')
            ? route('admin.login')
            : route('home'));

        $middleware->alias([
            'active.admin' => EnsureUserIsActive::class,
            'super.admin' => EnsureSuperAdmin::class,
            'akses.laporan' => EnsureCanAccessReports::class,
            'akses.usulan' => EnsureCanAccessUsulan::class,
            'formulir.terbuka' => PastikanFormulirTerbuka::class,
        ]);

        if (env('VERCEL')) {
            $middleware->trustProxies(at: '*');
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash(['description', 'photos', 'uploaded_media']);
    })->create();
