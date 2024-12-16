<?php

namespace Tests\Feature\Auth;

use Faker\Factory;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    private $faker;
    private $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create('pl_PL');

        $this->testUser = User::create([
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'),
            'phone' => $this->faker->phoneNumber(),
            'birth_date' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'bio' => $this->faker->sentence(),
            'location' => $this->faker->city(),
            'email_verified_at' => now(),
            'is_active' => true,
            'phone_verified' => true,
            'preferences' => [
                'language' => $this->faker->languageCode(),
                'theme' => $this->faker->randomElement(['light', 'dark'])
            ],
            'notification_settings' => [
                'email' => true,
                'sms' => false
            ]
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->testUser) {
            $this->testUser->delete();
        }

        parent::tearDown();
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testUser->email,
            'password' => 'password123'
        ], ['X-Client-Type' => 'web']);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id', 'email',
                        'first_name', 'last_name'
                    ]
                ]
            ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testUser->email,
            'password' => 'wrongpassword',
        ], ['X-Client-Type' => 'web']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJson([
                'message' => 'The given data was invalid.',
            ]);
    }

    /** @test */
    public function login_requires_client_type_header()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->testUser->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'X-Client-Type header is required'
            ]);
    }

    /** @test */
    public function user_can_get_their_profile_when_authenticated()
    {
        $token = JWTAuth::fromUser($this->testUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Client-Type' => 'web'
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->testUser->id,
                'email' => $this->testUser->email,
                'first_name' => $this->testUser->first_name,
                'last_name' => $this->testUser->last_name,
                'email_verified_at' => $this->testUser->email_verified_at,
                'created_at' => $this->testUser->created_at->toJSON(),
                'updated_at' => $this->testUser->updated_at->toJSON()
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_me_endpoint()
    {
        $response = $this->withHeaders([
            'X-Client-Type' => 'web'
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 401,
                    'message' => 'Token not provided'
                ],

            ]);
    }

    /** @test */
    public function user_can_refresh_token()
    {
        $token = JWTAuth::fromUser($this->testUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Client-Type' => 'web'
        ])->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id', 'email', 'first_name', 'last_name'
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_logout()
    {
        $token = JWTAuth::fromUser($this->testUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Client-Type' => 'web'
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);
    }
}
