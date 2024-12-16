<?php

namespace Tests\Unit\Services;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Auth\AuthService;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private \Faker\Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
        $this->faker = Faker::create();
    }

    /** @test */
    public function test_can_register_new_user_with_minimal_data(): void
    {
        $data = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!'
        ];

        $result = $this->authService->register($data);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
    }

    /** @test */
    public function test_can_register_new_user_with_full_data(): void
    {
        $data = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName()
        ];

        $result = $this->authService->register($data);

        $this->assertEquals($data['first_name'], $result['user']->first_name);
        $this->assertEquals($data['last_name'], $result['user']->last_name);
    }

    /** @test */
    public function test_jwt_token_is_valid(): void
    {
        $data = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!'
        ];

        $result = $this->authService->register($data);
        $decoded = JWTAuth::setToken($result['token'])->getPayload();

        $this->assertEquals($result['user']->id, $decoded->get('sub'));
    }

    public function it_creates_new_user_from_social_provider(): void
    {
        $socialiteUser = $this->mockSocialiteUser([
            'id' => '12345',
            'email' => 'new_user@example.com',
            'name' => 'John Doe',
            'avatar' => 'http://example.com/avatar.jpg'
        ]);

        Auth::shouldReceive('login')
            ->once()
            ->andReturn('new_token');

        $result = $this->socialAuthService->handleCallback('google', $socialiteUser);

        $user = User::where('email', 'new_user@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);

        $socialAccount = SocialAccount::where('user_id', $user->id)->first();
        $this->assertNotNull($socialAccount);
        $this->assertEquals('google', $socialAccount->provider);
        $this->assertEquals('12345', $socialAccount->provider_id);

        // Weryfikuj URL awatara
        $image = $user->images()->first();
        $this->assertNotNull($image);
        $this->assertEquals('http://example.com/avatar.jpg', $image->path);
    }
}
