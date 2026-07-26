<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $icons = [
            'mdi-youtube',
            'mdi-facebook',
            'mdi-instagram',
            'mdi-wikipedia',
            'mdi-google-maps',
            'mdi-web'
        ];

        return [
            'url' => $this->faker->url(),
            'meta_data' => json_encode([
                'title' => $this->faker->sentence(3),
                'description' => $this->faker->sentence(8),
                'icon' => $this->faker->randomElement($icons)
            ])
        ];
    }

    /**
     * Link for YouTube
     */
    public function youtube(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => 'https://youtube.com/watch?v=' . $this->faker->bothify('???########'),
            'meta_data' => json_encode([
                'title' => $this->faker->sentence(3),
                'description' => 'Video guide',
                'icon' => 'mdi-youtube'
            ])
        ]);
    }

    /**
     * Link for Facebook
     */
    public function facebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => 'https://facebook.com/' . $this->faker->userName(),
            'meta_data' => json_encode([
                'title' => $this->faker->sentence(2),
                'description' => 'Facebook page',
                'icon' => 'mdi-facebook'
            ])
        ]);
    }

    /**
     * Link for Wikipedia
     */
    public function wikipedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => 'https://en.wikipedia.org/wiki/' . $this->faker->word(),
            'meta_data' => json_encode([
                'title' => $this->faker->sentence(2),
                'description' => 'Wikipedia article',
                'icon' => 'mdi-wikipedia'
            ])
        ]);
    }

    /**
     * Link with minimal meta_data
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_data' => json_encode([
                'title' => '',
                'description' => '',
                'icon' => ''
            ])
        ]);
    }
}
