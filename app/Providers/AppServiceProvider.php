<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register WatchlistService
        $this->app->singleton(\App\Services\WatchlistService::class, function ($app) {
            return new \App\Services\WatchlistService(
                $app->make(\App\Services\RiskScoringService::class),
                $app->make(\App\Services\OpenMeteoService::class),
                $app->make(\App\Services\ExchangeRateService::class),
                $app->make(\App\Services\ActivityLogService::class)
            );
        });

        // Register ActivityLogService
        $this->app->singleton(\App\Services\ActivityLogService::class, function ($app) {
            return new \App\Services\ActivityLogService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
