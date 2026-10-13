<?php

namespace App\Providers;

use App\Enums\JenisFormulir;
use App\Policies\KunciFormulirPolicy;
use App\Services\Formulir\LayananKunciFormulir;
use App\Services\Site\SiteDataService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteDataService::class);
        $this->app->scoped(LayananKunciFormulir::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(JenisFormulir::class, KunciFormulirPolicy::class);

        RateLimiter::for('report-submission', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('report-upload-authorizations', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('suggestion-submission', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower($request->string('username')->toString()).'|'.$request->ip());
        });
    }
}
