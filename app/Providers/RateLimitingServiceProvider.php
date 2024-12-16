<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\ServiceProvider;
use function Pest\Laravel\json;

class RateLimitingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

        RateLimiter::for('registration', function (Request $request) {
            $maxAttempts = config('auth.registration.throttle.max_attempts', 3);
            $decayMinutes = config('auth.registration.throttle.decay_minutes', 60);
            return Limit::perMinute($maxAttempts)
                ->by('IP_' . $request->ip())
                ->response(function () use ($decayMinutes) {
                    return response()->json([
                        'message' => trans('auth.too_many_attempts', [
                            'minutes' => config('auth.registration.throttle.decay_minutes')
                        ])
                    ], 429);
                });
        });

    }
}

