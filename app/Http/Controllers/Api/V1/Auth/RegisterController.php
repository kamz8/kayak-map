<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Response;


/**
 * @OA\Post(
 *     path="/api/v1/auth/register",
 *     summary="Register a new user",
 *     description="Register a new user in the system",
 *     operationId="register",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password","password_confirmation"},
 *             @OA\Property(property="first_name", type="string", example="John", description="User's first name"),
 *             @OA\Property(property="last_name", type="string", example="Doe", description="User's last name"),
 *             @OA\Property(property="name", type="string", example="johndoe", description="User's display name"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="SecurePass123!"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="SecurePass123!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Registration successful"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *                 @OA\Property(
 *                     property="user",
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="johndoe"),
 *                     @OA\Property(property="email", type="string", example="john@example.com"),
 *                     @OA\Property(property="created_at", type="string", format="date-time")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken."))
 *             )
 *         )
 *     )
 * )
 */
class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function __invoke(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return (new ApiResource(
                [
                    'token' => $result['token'],
                    'user' => new UserResource($result['user'])
                ],
                'Registration successful',
                Response::HTTP_CREATED
            ))->response()->setStatusCode(Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return (new ApiResource(
                [],
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            ))->response()->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
