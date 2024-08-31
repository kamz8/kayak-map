<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait NotFoundResponse
{
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 404,
                'message' => $message,
            ]
        ], 404);
    }
}

