<?php

use App\Http\Middleware\ThrottleNominatimRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '/api/v1',
        then: function (Application $app) {
            Route::middleware('api')->prefix('api/v1')->group(base_path('routes/api.php'));
                }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'throttle.nominatim' => ThrottleNominatimRequests::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
            'auth.check.client.type' => \App\Http\Middleware\Auth\CheckClientType::class,
            'validate.api.key' => \App\Http\Middleware\Auth\ApiKeyMiddleware::class,
            'jwt.auth' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\GetUserFromToken::class,
            'jwt.refresh' => \PHPOpenSourceSaver\JWTAuth\Middleware\RefreshToken::class,
            'api.not.found'=>\App\Http\Middleware\HandleApiNotFound::class,
        ]);

        $middleware->group('api', [
            'force.json',
            'auth.check.client.type',
            'api.not.found'
        ]);

        $middleware->group('api.auth', [
            'force.json',
            'auth.check.client.type',
            'jwt.auth',
            'api.not.found'
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {

                // 1. Najpierw HttpResponseException bo jest specyficzny
                if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                    $response = $e->getResponse();
                    return response()->json([
                        'error' => [
                            'code' => $response->getStatusCode(),
                            'message' => $response->getData()->message ?? 'Too Many Attempts'
                        ]
                    ], $response->getStatusCode());
                }

                // ValidationException
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                            'code' => 422,
                            'message' => 'The given data was invalid.',
                            'errors' => $e->errors(),
                    ], 422);
                }

                // 3. Następnie HttpExceptionInterface dla wszystkich HTTP wyjątków
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    return response()->json([
                        'error' => [
                            'code' => $e->getStatusCode(),
                            'message' => $e->getMessage()
                        ]
                    ], $e->getStatusCode());
                }

                // W sekcji exceptions
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'error' => [
                            'code' => 401,
                            'message' => 'Unauthorized'
                        ]
                    ], 401);
                }

                // 4. Na końcu ogólna obsługa pozostałych wyjątków
                $statusCode = 500;
                $message = $e->getMessage() ?: 'Server Error';

                $response = [
                    'error' => [
                        'code' => $statusCode,
                        'message' => $message,
                    ]
                ];

                if (config('app.debug')) {
                    $response['error']['trace'] = $e->getTraceAsString();
                    $response['error']['file'] = $e->getFile();
                    $response['error']['line'] = $e->getLine();
                }

                return response()->json($response, $statusCode);
            }

            return false;
        });
    })
    ->create();
