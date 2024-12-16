<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Image;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class SocialAuthService
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function handleCallback(string $provider, SocialiteUser $socialUser): array
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            // Sprawdź istniejące konto social
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                return $this->loginExistingUser($socialAccount->user);
            }

            // Sprawdź czy istnieje użytkownik z tym emailem
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = $this->createUserFromSocialite($socialUser);
            }

            $this->createSocialAccount($user, $provider, $socialUser);
            $this->createUserAvatar($user, $socialUser);

            return $this->loginExistingUser($user);
        });
    }

    public function createUserFromSocialite(SocialiteUser $socialUser): User
    {
        $names = $this->parseFullName($socialUser->getName());

        return User::create([
            'email' => $socialUser->getEmail(),
            'first_name' => $names['first_name'],
            'last_name' => $names['last_name'],
            'email_verified_at' => now()
        ]);
    }


    private function createSocialAccount(User $user, string $provider, SocialiteUser $socialUser): void
    {
        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
            'token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
        ]);
    }

    private function createUserAvatar(User $user, SocialiteUser $socialUser): void
    {
        if (!$avatarUrl = $socialUser->getAvatar()) {
            return; // Wyjście, jeśli brak avatara
        }

        try {
            $user->images()->create([
                'path' => $avatarUrl, // Przechowuje URL zamiast ścieżki lokalnej
                'is_main' => true,
                'vattr' => json_encode([
                    'alt' => "Avatar użytkownika {$user->first_name} {$user->last_name}",
                    'type' => 'avatar',
                    'source' => 'social',
                ]),

            ]);
        } catch (\Exception $e) {
            // Opcjonalne logowanie błędu
            Log::error('Błąd pobierania avatara: ' . $e->getMessage());
        }
    }

    private function loginExistingUser(User $user): array
    {
        $token = auth()->login($user);

        return [
            'token' => $token,
            'user' => $user->fresh(['images'])
        ];
    }

    private function parseFullName(string $fullName): array
    {
        $parts = explode(' ', $fullName, 2);
        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? ''
        ];
    }
}
