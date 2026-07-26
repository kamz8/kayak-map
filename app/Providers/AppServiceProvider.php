<?php

namespace App\Providers;

use App\Services\ReverseGeocodingService;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('facebook', \SocialiteProviders\Facebook\Provider::class);
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });

        // Register UserLoggedIn event listener
        Event::listen(
            \App\Events\UserLoggedIn::class,
            \App\Listeners\UpdateUserLastLogin::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }

        // Super Admin bypass - according to Spatie documentation
        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true; // Super Admin bypasses all permission checks
            }
        });
    }
}
