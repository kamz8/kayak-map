<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        $user = User::create([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign default 'User' role to newly registered user
        $user->assignRole('User');

        // Load roles and permissions for JWT token claims
        $user->load('roles.permissions');

        // Generate JWT token with user claims (including roles/permissions)
        $customClaims = $this->getUserClaims($user);
        $token = JWTAuth::customClaims($customClaims)->fromUser($user);

        // Dispatch UserLoggedIn event to update last login timestamp
        event(new \App\Events\UserLoggedIn($user, request()->ip(), 'web'));

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Attempt to log in a user
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::with('roles.permissions')->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')], // Dopasowanie błędu do klucza `email`
            ]);
        }

        $tokens = $this->generateTokens($user);

        // Dispatch UserLoggedIn event to update last login timestamp
        event(new \App\Events\UserLoggedIn($user, request()->ip(), 'web'));

        return [
            'user' => $user,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60 // Convert minutes to seconds
        ];
    }

    /**
     * Generate access and refresh tokens for user
     */
    private function generateTokens(User $user): array
    {
        $customClaims = $this->getUserClaims($user);

        // Generate access token with short TTL
        $accessToken = JWTAuth::customClaims(array_merge($customClaims, [
            'token_type' => 'access_token'
        ]))->fromUser($user);

        // Generate refresh token with long TTL
        $refreshClaims = [
            'token_type' => 'refresh_token',
            'user_id' => $user->id,
            'email' => $user->email,
            'sub' => $user->id,
            'iss' => config('app.url'),
            'aud' => config('app.url'),
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(config('jwt.refresh_ttl', 20160))->timestamp
        ];

        $refreshToken = JWTAuth::getJWTProvider()->encode($refreshClaims);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    /**
     * Get user claims for JWT token
     */
    private function getUserClaims(User $user): array
    {
        return [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'email_verified_at' => $user->email_verified_at,
            'roles' => $user->roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name
                ];
            })->toArray(),
            'permissions' => $user->getAllPermissions()->map(function($permission) {
                return $permission->name;
            })->unique()->values()->toArray()
        ];
    }

    /**
     * Log out the current user
     */
    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Refresh user's access token using refresh token
     * RFC 6749 compliant implementation
     */
    public function refresh(string $refreshToken): array
    {
        try {
            // Validate refresh token
            JWTAuth::setToken($refreshToken);
            $payload = JWTAuth::getPayload();

            // Check if token is refresh token
            if ($payload->get('token_type') !== 'refresh_token') {
                throw new \Exception('Invalid token type');
            }

            // Get user with fresh data
            $userId = $payload->get('user_id') ?? $payload->get('sub');
            $user = User::with('roles.permissions')->findOrFail($userId);

            // Check if user is still active
            if (!$user->is_active) {
                throw new \Exception('User account is deactivated');
            }

            // Generate new tokens
            $tokens = $this->generateTokens($user);

            // Optionally invalidate old refresh token (for rotation)
            if (config('jwt.refresh_token_rotation', true)) {
                JWTAuth::invalidate($refreshToken);
            }

            return [
                'user' => $user,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60
            ];

        } catch (\Exception $e) {
            throw new \Exception('Refresh token is invalid or expired: ' . $e->getMessage());
        }
    }

    /**
     * Refresh user's token
     */
    public function me(): \App\Models\User
    {
        return auth()->user();
    }
}
