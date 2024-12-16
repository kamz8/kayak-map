<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;

class CheckClientType
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasHeader('X-Client-Type')) {
            return response()->json([
                'status' => 'error',
                'message' => 'X-Client-Type header is required'
            ], 400);
        }

        $clientType = $request->header('X-Client-Type');
        $allowedTypes = array_values(config('auth.clients.types', ['web', 'android', 'ios']));

        if (!in_array($clientType, $allowedTypes)) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => 'Invalid client type. Allowed types: ' . implode(', ', $allowedTypes)
                ]
            ], 400);
        }

        return $next($request);
    }
}
