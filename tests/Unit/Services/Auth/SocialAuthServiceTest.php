<?php

namespace Tests\Unit\Services\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\SocialAccount;
use App\Services\Auth\SocialAuthService;
use App\Services\Auth\AuthService;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Support\Facades\Auth;
use Mockery;

class SocialAuthServiceTest extends TestCase
{
    private SocialAuthService $socialAuthService;

    protected function setUp(): void
    {
        parent::setUp();

        $authServiceMock = Mockery::mock(AuthService::class);
        $this->socialAuthService = new SocialAuthService($authServiceMock);
    }

    /** @test */
    public function it_handles_existing_social_account_login(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);

        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '12345'
        ]);

        $socialiteUser = $this->mockSocialiteUser([
            'id' => '12345',
            'email' => $user->email,
            'name' => 'Test User'
        ]);

        Auth::shouldReceive('login')
            ->once()
            ->with($user)
            ->andReturn('fake_token');

        $result = $this->socialAuthService->handleCallback('google', $socialiteUser);

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('fake_token', $result['token']);
        $this->assertTrue($result['user']->is($user));
    }

    /** @test */
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
    }

    /** @test */
    public function it_links_social_account_to_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'first_name' => 'Existing',
            'last_name' => 'User'
        ]);

        $socialiteUser = $this->mockSocialiteUser([
            'id' => '12345',
            'email' => 'existing@example.com',
            'name' => 'Existing User'
        ]);

        Auth::shouldReceive('login')
            ->once()
            ->with($existingUser)
            ->andReturn('existing_token');

        $result = $this->socialAuthService->handleCallback('google', $socialiteUser);

        $socialAccount = SocialAccount::where('user_id', $existingUser->id)->first();

        $this->assertNotNull($socialAccount);
        $this->assertEquals('google', $socialAccount->provider);
        $this->assertEquals('12345', $socialAccount->provider_id);
    }

    /** @test */
    public function it_handles_single_name_social_user(): void
    {
        $socialiteUser = $this->mockSocialiteUser([
            'id' => '12345',
            'email' => 'single@example.com',
            'name' => 'SingleName'
        ]);

        Auth::shouldReceive('login')
            ->once()
            ->andReturn('single_token');

        $result = $this->socialAuthService->handleCallback('google', $socialiteUser);

        $user = User::where('email', 'single@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('SingleName', $user->first_name);
        $this->assertEmpty($user->last_name);
    }

    private function mockSocialiteUser(array $data): SocialiteUser
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($data['id']);
        $socialiteUser->shouldReceive('getEmail')->andReturn($data['email']);
        $socialiteUser->shouldReceive('getName')->andReturn($data['name']);
        $socialiteUser->shouldReceive('getAvatar')->andReturn($data['avatar'] ?? null);
        $socialiteUser->shouldReceive('token')->andReturn('test_token');
        $socialiteUser->shouldReceive('refreshToken')->andReturn(null);
        $socialiteUser->shouldReceive('expiresIn')->andReturn(null);

        return $socialiteUser;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
