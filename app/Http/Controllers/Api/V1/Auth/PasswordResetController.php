<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{ForgotPasswordRequest, ResetPasswordRequest};
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {}

    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->sendResetLink($request->email);

        return response()->json([
            'message' => 'Link do resetowania hasła został wysłany na podany adres email.'
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->resetPassword(
            $request->email,
            $request->token,
            $request->password
        );

        return response()->json([
            'message' => 'Hasło zostało pomyślnie zmienione.'
        ]);
    }
}
