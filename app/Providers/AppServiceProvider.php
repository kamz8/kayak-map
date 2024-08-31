<?php

namespace App\Providers;

use App\Services\ReverseGeocodingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ReverseGeocodingService::class, function ($app) {
            return new ReverseGeocodingService();
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
