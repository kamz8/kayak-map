<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey || !$this->isValidApiKey($apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Sprawdzanie typu klienta
        $clientType = $request->header('X-Client-Type');
        if (!$this->isValidClientType($clientType, $apiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid client type'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    private function isValidApiKey(string $apiKey): bool
    {
        $validKeys = [
            'web' => env('WEB_API_KEY'),
            'mobile' => env('MOBILE_API_KEY'),
            'dashboard' => env('DASHBOARD_API_KEY')
        ];

        return in_array($apiKey, $validKeys);
    }

    private function isValidClientType(?string $clientType, string $apiKey): bool
    {
        $keyToClientType = [
            env('WEB_API_KEY') => 'web',
            env('MOBILE_API_KEY') => 'mobile',
            env('DASHBOARD_API_KEY') => 'dashboard'
        ];

        return $keyToClientType[$apiKey] === $clientType;
    }
}
