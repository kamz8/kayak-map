<?php

namespace App\Http\Responses\Dashboard;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Operacja wykonana pomyślnie',
        int $statusCode = HttpResponse::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode, $headers);
    }

    public static function created(
        mixed $data = null,
        string $message = 'Zasób został utworzony pomyślnie',
        array $headers = []
    ): JsonResponse {
        return self::success($data, $message, HttpResponse::HTTP_CREATED, $headers);
    }

    public static function noContent(
        string $message = 'Operacja wykonana pomyślnie'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], HttpResponse::HTTP_NO_CONTENT);
    }

    public static function error(
        string $message = 'Wystąpił błąd',
        int $statusCode = HttpResponse::HTTP_BAD_REQUEST,
        mixed $errors = null,
        array $headers = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode, $headers);
    }

    public static function notFound(
        string $message = 'Zasób nie został znaleziony'
    ): JsonResponse {
        return self::error($message, HttpResponse::HTTP_NOT_FOUND);
    }

    public static function forbidden(
        string $message = 'Brak uprawnień do wykonania tej operacji'
    ): JsonResponse {
        return self::error($message, HttpResponse::HTTP_FORBIDDEN);
    }

    public static function unauthorized(
        string $message = 'Brak autoryzacji'
    ): JsonResponse {
        return self::error($message, HttpResponse::HTTP_UNAUTHORIZED);
    }

    public static function validationError(
        mixed $errors,
        string $message = 'Dane są nieprawidłowe'
    ): JsonResponse {
        return self::error($message, HttpResponse::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    public static function serverError(
        string $message = 'Wystąpił błąd serwera'
    ): JsonResponse {
        return self::error($message, HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}