<?php

namespace Tests\Feature\Auth;

use App\Events\UserLoggedIn;
use App\Models\User;
use Database\Seeders\Dashboard\RoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginEventTest extends TestCase
{
    use RefreshDatabase;

    private string $loginEndpoint = '/api/v1/auth/login';
    private string $registerEndpoint = '/api/v1/auth/register';
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->headers = [
            'X-Client-Type' => 'web',
        ];
    }

    /** @test */
    public function user_logged_in_event_is_dispatched_on_login(): void
    {
        Event::fake();

        // Create user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        // Login
        $response = $this->postJson($this->loginEndpoint, [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ], $this->headers);

        $response->assertStatus(200);

        // Assert event was dispatched
        Event::assertDispatched(UserLoggedIn::class, function ($event) use ($user) {
            return $event->user->id === $user->id
                && $event->guard === 'web'
                && !empty($event->ip);
        });
    }

    /** @test */
    public function user_logged_in_event_is_dispatched_on_register(): void
    {
        Event::fake();

        // Register new user
        $response = $this->postJson($this->registerEndpoint, [
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ], $this->headers);

        $response->assertStatus(201);

        // Assert event was dispatched
        Event::assertDispatched(UserLoggedIn::class, function ($event) {
            return $event->user->email === 'newuser@example.com'
                && $event->guard === 'web'
                && !empty($event->ip);
        });
    }

    /** @test */
    public function last_login_at_is_updated_on_login(): void
    {
        // Create user without last_login_at
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null
        ]);

        $this->assertNull($user->last_login_at);
        $this->assertNull($user->last_login_ip);

        // Login
        $response = $this->postJson($this->loginEndpoint, [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ], $this->headers);

        $response->assertStatus(200);

        // Refresh user from database
        $user->refresh();

        // Assert last_login_at and last_login_ip are set
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
        $this->assertEquals('127.0.0.1', $user->last_login_ip);
    }

    /** @test */
    public function last_login_at_is_updated_on_register(): void
    {
        // Register new user
        $response = $this->postJson($this->registerEndpoint, [
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ], $this->headers);

        $response->assertStatus(201);

        // Find created user
        $user = User::where('email', 'newuser@example.com')->first();

        // Assert last_login_at and last_login_ip are set
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
        $this->assertEquals('127.0.0.1', $user->last_login_ip);
    }

    /** @test */
    public function last_login_data_is_available_in_dashboard_api(): void
    {
        // Create super admin user
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $admin->assignRole('Super Admin');

        // Create test user and login to set last_login
        $testUser = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        // Login as test user to set last_login
        $this->postJson($this->loginEndpoint, [
            'email' => 'testuser@example.com',
            'password' => 'Password123!'
        ], $this->headers);

        // Refresh user
        $testUser->refresh();

        // Login as admin
        $adminLoginResponse = $this->postJson($this->loginEndpoint, [
            'email' => 'admin@example.com',
            'password' => 'Password123!'
        ], $this->headers);

        $adminToken = $adminLoginResponse->json('data.access_token');

        // Fetch user details from Dashboard API
        $response = $this->getJson("/api/v1/dashboard/users/{$testUser->id}", [
            'Authorization' => 'Bearer ' . $adminToken,
            'X-Client-Type' => 'web'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'email',
                'last_login_at',
                'last_login_ip'
            ]
        ]);

        // Assert last_login data is present
        $userData = $response->json('data');
        $this->assertNotNull($userData['last_login_at']);
        $this->assertNotNull($userData['last_login_ip']);
        $this->assertEquals('127.0.0.1', $userData['last_login_ip']);
    }
}
