<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('auth.registration.enabled')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration is currently disabled'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
