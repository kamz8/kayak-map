<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheResponse
{
    public function handle(Request $request, Closure $next)
    {
        $cacheKey = 'response_' . sha1($request->fullUrl());

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $response = $next($request);

        if ($response->status() === 200) {
            Cache::put($cacheKey, $response->getContent(), now()->addHours(1));
        }

        return $response;
    }
}
