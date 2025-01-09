<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private readonly SocialAuthService $socialAuthService
    ) {}

    /**
     * @OA\Post(
     *     path="/auth/social/{provider}/callback",
     *     summary="Authenticate user with social provider",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum={"google", "facebook"})
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"access_token"},
     *             @OA\Property(property="access_token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=422, description="Invalid token")
     * )
     */
    public function callback(string $provider): ApiResource | JsonResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->userFromToken(
                request()->input('access_token')
            );

            $result = $this->socialAuthService->handleCallback($provider, $socialUser);

            return new ApiResource([
                'token' => $result['token'],
                'user' => new UserResource($result['user'])
            ]);

        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Failed to authenticate with ' . $provider
                ]
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/social/{provider}/auth-url",
     *     summary="Get authentication URL for social provider",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum={"google", "facebook"})
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=422, description="Invalid provider")
     * )
     */
    public function getAuthUrl(string $provider): ApiResource | JsonResponse
    {
        try {
            $url = $this->socialAuthService->getAuthUrl($provider);

            return new ApiResource([
                'url' => $url
            ]);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            return response()->json([
                'error' => [
                    'code' => 422,
                    'message' => 'Failed to get auth URL for ' . $provider
                ]
            ], 422);
        }
    }
}
