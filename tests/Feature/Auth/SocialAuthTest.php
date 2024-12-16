<?php
namespace Tests\Feature\Auth;

use Faker\Factory;
use Illuminate\Support\Facades\Auth;
use phpGPX\Models\Email;
use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use App\Models\User;
use App\Models\SocialAccount;
use Mockery;

class SocialAuthTest extends TestCase
{
    private array $headers;
    public \Faker\Generator $faker;
    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('pl_PL');
        $this->headers = [
            'X-Client-Type' => 'web'
        ];

    }

    /** @test */
    public function user_can_login_with_google()
    {
        // Przygotuj mock Socialite User
        $socialiteUser = $this->createMockSocialiteUser();

        // Przygotuj Provider Mock
        $providerMock = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $providerMock->shouldReceive('userFromToken')
            ->with('fake-token')
            ->andReturn($socialiteUser);

        // Mockuj Socialite Facade
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($providerMock);

        $response = $this->postJson('/api/v1/auth/social/google/callback', [
            'access_token' => 'fake-token'
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'email',
                        'first_name',
                        'last_name'
                    ]
                ]
            ]);
    }


    /** @test */
    public function it_creates_new_user_if_email_not_registered()
    {
        $socialiteUser = $this->createMockSocialiteUser('google');
        Socialite::shouldReceive('driver->userFromToken')->andReturn($socialiteUser);

        $response = $this->postJson('/api/v1/auth/social/google/callback', [
            'access_token' => 'fake-token'
        ], $this->headers);

        $response->assertStatus(200);

        $user = User::where('email', $socialiteUser->getEmail())->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => $socialiteUser->getId()
        ]);

        // Sprawdzenie czy avatar został zapisany
        $image = $user->images()->first();

        $this->assertNotNull($image);
        $this->assertEquals($socialiteUser->getAvatar(), $image->path);
    }

    /** @test */
    public function it_links_social_account_to_existing_user()
    {
        $existingUser = User::factory()->create([
            'email' => $this->faker->unique()->safeEmail,
        ]);

        $socialiteUser = $this->createMockSocialiteUser('google', $existingUser->email);
        Socialite::shouldReceive('driver->userFromToken')->andReturn($socialiteUser);

        $response = $this->postJson('/api/v1/auth/social/google/callback', [
            'access_token' => 'fake-token'
        ], $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $existingUser->id,
            'provider' => 'google'
        ]);
    }

    private function createMockSocialiteUser($provider=null, $email=null): \Laravel\Socialite\Two\User
    {
        $user = new \Laravel\Socialite\Two\User();

        $user->map([
            'id' => $this->faker->uuid,
            'provider' => $provider ?? 'google',
            'email' => $email ?? $this->faker->unique()->safeEmail,
            'name' => $this->faker->name,         // To jest ważne dla parseFullName
            'nickname' => $this->faker->userName,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $user->token = 'fake-oauth-token';
        $user->refreshToken = 'fake-refresh-token';
        $user->expiresIn = 3600;

        return $user;
    }

}
