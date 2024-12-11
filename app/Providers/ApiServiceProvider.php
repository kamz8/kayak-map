<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Rejestracja grup middleware
        $this->app['router']->middlewareGroup('public.api', [
            \App\Http\Middleware\Auth\ApiKeyMiddleware::class,
            \App\Http\Middleware\PublicApiRateLimit::class,
        ]);
    }
}
