<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoint autoryzacji - logowanie, wylogowywanie, odświeżanie tokena"
 * )
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Logowanie użytkownika",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Poprawne logowanie",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(response=401, description="Nieprawidłowe dane logowania"),
     *     @OA\Response(response=422, description="Błędy walidacji"),
     *     @OA\Response(response=429, description="Zbyt wiele prób logowania"),
     *     @OA\Header(
     *         header="X-Client-Type",
     *         description="Typ klienta",
     *         required=true,
     *         @OA\Schema(type="string", enum={"web", "android", "ios"})
     *     )
     * )
     */
    public function login(LoginRequest $request): ApiResource
    {
        $result = $this->authService->login($request->validated());

        return new ApiResource([
            'token' => $result['token'],
            'user' => new UserResource($result['user'])
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Odświeżenie tokena JWT",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token odświeżony",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(response=401, description="Nieprawidłowy token")
     * )
     */
    public function refresh(): ApiResource
    {
        try {
            $token = $this->authService->refresh();
            return new ApiResource([
                'token' => $token,
                'user' => UserResource::make(auth()->user())
            ]);
        } catch (\Exception $e) {
            throw new HttpResponseException(
                response()->json([
                    'error' => [
                        'message' => 'Invalid token'
                    ]
                ], 401)
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Wylogowanie użytkownika",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Pomyślnie wylogowano"
     *     ),
     *     @OA\Response(response=401, description="Nieuatoryzowany dostęp")
     * )
     */
    public function logout(): ApiResource
    {
        $this->authService->logout();
        return new ApiResource(null, 'Successfully logged out');
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Pobranie danych zalogowanego użytkownika",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dane użytkownika",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(response=401, description="Nieuatoryzowany dostęp")
     * )
     */
    public function me(): \Illuminate\Http\JsonResponse
    {
        $user = $this->authService->me();
        return response()->json(new UserResource($user));
    }
}
