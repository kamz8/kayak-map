<?php

namespace App\Services\Auth;

use App\Exceptions\ExpiredResetTokenException;
use App\Exceptions\InvalidResetTokenException;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Support\Facades\{Hash, DB};
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PasswordResetService
{
    public function sendResetLink(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        $token = $this->createToken($user);

        $user->notify(new ResetPasswordNotification($token));
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $this->validateReset($email, $token);

        DB::transaction(function () use ($email, $password) {
            $user = User::where('email', $email)->firstOrFail();

            // Update password
            $user->password = Hash::make($password);
            $user->save();

            // Remove reset token
            $this->deleteToken($email);
        });
    }

    private function createToken(User $user): string
    {
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        return $token;
    }

    private function validateReset(string $email, string $token): void
    {
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetToken || !Hash::check($token, $resetToken->token)) {
            throw new InvalidResetTokenException();
        }

        if (now()->subHours(24)->gt($resetToken->created_at)) {
            $this->deleteToken($email);
            throw new ExpiredResetTokenException();
        }
    }

    private function deleteToken(string $email): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();
    }
}
