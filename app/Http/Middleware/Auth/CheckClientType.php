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
                'message' => 'Client type not specified'
            ], 400);
        }

        $clientType = $request->header('X-Client-Type');
        $allowedTypes = ['web', 'mobile', 'dashboard'];

        if (!in_array($clientType, $allowedTypes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid client type'
            ], 400);
        }

        return $next($request);
    }
}
