<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleNominatimRequests
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle($request, Closure $next)
    {
        if ($this->limiter->tooManyAttempts('nominatim', 1)) {
            return response()->json(['error' => 'Too many requests'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit('nominatim', 1); // 1 second

        return $next($request);
    }
}
