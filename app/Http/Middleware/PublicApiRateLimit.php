<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicApiRateLimit
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many requests',
                'retry_after' => $this->limiter->availableIn($key)
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $this->maxAttempts(),
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, $this->maxAttempts()),
        ]);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(implode('|', [
            $request->header('X-API-Key'),
            $request->ip(),
            $request->header('X-Client-Type')
        ]));
    }

    protected function maxAttempts(): int
    {
        $clientType = request()->header('X-Client-Type');

        return match($clientType) {
            'mobile' => 120, // 120 requests per minute
            'web' => 60,     // 60 requests per minute
            default => 30    // 30 requests per minute
        };
    }

    protected function decayMinutes(): int
    {
        return 1; // Reset after 1 minute
    }
}
