<?php

use App\Http\Middleware\ThrottleNominatimRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'throttle.nominatim' => ThrottleNominatimRequests::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->group('api', [
            'force.json',
            // inne middleware...
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'error' => [
                            'code' => 422,
                            'message' => $e->getMessage(),
                            'errors' => $e->errors(),
                        ]
                    ], 422);
                }

                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
                    ? 'The requested API endpoint does not exist.'
                    : $e->getMessage();

                $response = [
                    'error' => [
                        'code' => $statusCode,
                        'message' => $e->getMessage(),
                    ]
                ];

                if ($statusCode === 500 && config('app.debug')) {
                    $response['error']['trace'] = $e->getTraceAsString();
                    $response['error']['file'] = $e->getFile();
                    $response['error']['line'] = $e->getLine();
                }

                return response()->json($response, $statusCode);
            }

            return false; // Pozwala na domyślne renderowanie dla non-API requests
        });
    })->create();
