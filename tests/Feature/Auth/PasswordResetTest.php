<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    protected array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Nagłówki globalne dla zapytań w testach
        $this->headers = [
            'X-Client-Type' => 'web',
            'Accept' => 'application/json',
        ];
    }

    /** @test */
    public function it_sends_a_password_reset_email_to_a_registered_user()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->withHeaders($this->headers)->postJson('/api/v1/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Link do resetowania hasła został wysłany na podany adres email.',
            ]);

        Notification::assertSentTo(
            [$user],
            \Illuminate\Auth\Notifications\ResetPassword::class
        );
    }

    /** @test */
    public function it_resets_the_password_with_a_valid_token()
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = Str::random(60);

        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => bcrypt($token),
            'created_at' => now(),
        ]);

        $newPassword = 'new-secure-password';

        $response = $this->withHeaders($this->headers)->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => $newPassword,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Hasło zostało pomyślnie zmienione.',
            ]);

        $this->assertTrue(Hash::check($newPassword, $user->refresh()->password));
    }

    /** @test */
    public function it_fails_to_reset_password_with_invalid_token()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->headers)->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'new-password',
        ]);

        $response->assertStatus(400); // Assuming 400 is the error for invalid token
    }
}
