<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleApiNotFound
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('api/*') && $response->status() === 404) {
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'API endpoint not found'
                ]
            ], 404);
        }

        return $response;
    }
}
