<?php

namespace App\OpenApi\Schemas\Auth;

/**
 * @OA\Schema(
 *     schema="LoginResponse",
 *     title="Login Response",
 *     description="Schemat odpowiedzi dla logowania"
 * )
 */
class LoginResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="object",
     *     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1..."),
     *     @OA\Property(
     *         property="user",
     *         ref="#/components/schemas/UserResource"
     *     )
     * )
     */
    public $data;
}
