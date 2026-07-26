<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\Dashboard\RoleSeeder;

class RegisterTest extends TestCase
{
    private string $endpoint = '/api/v1/auth/register';
    private \Faker\Generator $faker;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles for testing
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        config(['auth.registration.enabled' => true]);
        $this->faker = Faker::create('pl_PL');
        $this->headers = [
            'X-Client-Type' => 'web',
        ];
    }

    /**
     * @test
     */
    public function registration_can_be_disabled(): void
    {
        config(['auth.registration.enabled' => false]);

        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Registration is currently disabled'
            ]);
    }

    /**
     * @test
     */
    public function user_can_register_with_valid_data(): void
    {
        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName()
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'timestamp'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name']
        ]);
    }

    /** @test */
    public function test_throttling_is_applied_to_registration(): void
    {
        // Przygotowanie
        config([
            'auth.registration.throttle.max_attempts' => 1,
            'auth.registration.throttle.decay_minutes' => 1
        ]);
        $this->serverVariables = ['REMOTE_ADDR' => '192.168.1.200'];
        // Pierwszy request
        $firstResponse = $this->postJson($this->endpoint, [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ], $this->headers);

        $firstResponse->assertStatus(201);

        // Drugi request - powinien być zablokowany
        $secondResponse = $this->postJson($this->endpoint, [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ], $this->headers);

        $secondResponse->assertStatus(429);
    }

    /**
     * @test
     */
    public function registration_requires_valid_email(): void
    {
        $payload = [
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * @test
     */
    public function registration_requires_unique_email(): void
    {
        $email = $this->faker->unique()->safeEmail();

        DB::table('users')->insert([
            'email' => $email,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $payload = [
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * @test
     */
    public function registration_requires_strong_password(): void
    {
        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     */
    public function registration_requires_password_confirmation(): void
    {
        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     */
    public function registration_requires_matching_passwords(): void
    {
        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * @test
     */
    public function first_name_and_last_name_are_optional(): void
    {
        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'first_name' => null,
            'last_name' => null
        ]);
    }

    /**
     * @test
     */
    public function registration_returns_valid_jwt_token(): void
    {
        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'token'
                ]
            ]);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // Sprawdzamy czy token działa
        $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token,
            'x-Client-Type' => 'web'
        ])->assertStatus(200);
    }

    /**
     * @test
     */
    public function newly_registered_user_has_default_user_role(): void
    {
        $payload = [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName()
        ];

        $response = $this->postJson($this->endpoint, $payload, $this->headers);

        $response->assertStatus(201);

        // Verify user has 'User' role in database
        $user = User::where('email', $payload['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('User'), 'Newly registered user should have "User" role');

        // Verify user has expected permissions through User role
        $expectedPermissions = ['trails.view', 'regions.view', 'api.access'];
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue(
                $user->hasPermissionTo($permission),
                "User should have '{$permission}' permission through User role"
            );
        }

        // Verify JWT token contains roles in claims
        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // Decode JWT and verify roles are included
        $tokenParts = explode('.', $token);
        $payload = json_decode(base64_decode($tokenParts[1]), true);

        $this->assertArrayHasKey('roles', $payload, 'JWT token should contain roles claim');
        $this->assertCount(1, $payload['roles'], 'User should have exactly 1 role');
        $this->assertEquals('User', $payload['roles'][0]['name'], 'Default role should be "User"');
    }
}
