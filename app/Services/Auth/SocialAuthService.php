<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Image;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
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

    public function handleDataDeletionCallback(string $provider, string $signedRequest): array
    {
        // Weryfikacja podpisu (zależy od dostawcy)
        if (!$this->verifySignedRequest($provider, $signedRequest)) {
            throw new \InvalidArgumentException('Nieprawidłowy podpis żądania');
        }

        // Dekodowanie żądania (przykład dla Facebooka)
        $requestData = $this->decodeSignedRequest($provider, $signedRequest);

        // Znajdź konto społecznościowe
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $requestData['user_id'])
            ->first();

        if (!$socialAccount) {
            return [
                'status' => 'error',
                'message' => 'Nie znaleziono konta'
            ];
        }

        // Usuń powiązane dane
        DB::transaction(function () use ($socialAccount) {
            // Usuń zdjęcia użytkownika
            $socialAccount->user->images()->detach();

            // Usuń konta społecznościowe
            $socialAccount->user->socialAccounts()->delete();

            // Opcjonalnie: usuń użytkownika lub dezaktywuj
            $socialAccount->user->delete();
        });

        return [
            'status' => 'success',
            'message' => 'Dane użytkownika zostały usunięte'
        ];
    }

    private function verifySignedRequest(string $provider, string $signedRequest): bool
    {
        // Implementacja weryfikacji podpisu zależna od dostawcy
        switch ($provider) {
            case 'facebook':
                return $this->verifyFacebookSignedRequest($signedRequest);
            // Dodaj innych dostawców
            default:
                throw new \InvalidArgumentException('Nieobsługiwany dostawca');
        }
    }
    //Remove user data
    private function verifyFacebookSignedRequest(string $signedRequest): bool
    {
        // Przykładowa weryfikacja (wymaga klucza tajnego Facebooka)
        $appSecret = config('services.facebook.app_secret');

        try {
            // Tutaj dodaj logikę weryfikacji podpisu Facebooka
            // Typowo polega to na sprawdzeniu podpisu kryptograficznego
            return true; // Placeholder
        } catch (\Exception $e) {
            return false;
        }
    }

    private function decodeSignedRequest(string $provider, string $signedRequest): array
    {
        // Dekodowanie żądania zależy od dostawcy
        switch ($provider) {
            case 'facebook':
                return $this->decodeFacebookSignedRequest($signedRequest);
            default:
                throw new \InvalidArgumentException('Nieobsługiwany dostawca');
        }
    }

    private function decodeFacebookSignedRequest(string $signedRequest): array
    {
        // Przykładowa implementacja dekodowania żądania Facebooka
        // W praktyce wymaga odpowiedniego parsowania i weryfikacji
        return [
            'user_id' => '', // ID użytkownika z żądania
            'delete_data' => true
        ];
    }

    public function getAuthUrl(string $provider): string
    {
        return Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }
}
