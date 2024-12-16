<?php

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \App\SocialAccount
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class SocialAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = SocialAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Domyślnie tworzy powiązanego użytkownika
            'provider' => $this->faker->randomElement(['google', 'facebook', 'twitter']),
            'provider_id' => $this->faker->unique()->numerify('##########'),
            'provider_token' => $this->faker->sha256,
            'provider_refresh_token' => $this->faker->optional()->sha256,
            'token_expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
