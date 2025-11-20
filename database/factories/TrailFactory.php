<?php

namespace Database\Factories;

use App\Models\Trail;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrailFactory extends Factory
{
    protected $model = Trail::class;

    public function definition()
    {
        $trailName = $this->faker->sentence(3);

        return [
            'river_name' => $this->faker->word,
            'trail_name' => $trailName,
            'slug' => \Illuminate\Support\Str::slug($trailName) . '-' . $this->faker->unique()->numberBetween(1, 10000),
            'description' => $this->faker->paragraph,
            'start_lat' => $this->faker->latitude,
            'start_lng' => $this->faker->longitude,
            'end_lat' => $this->faker->latitude,
            'end_lng' => $this->faker->longitude,
            'trail_length' => $this->faker->numberBetween(1000, 100000),
            'author' => $this->faker->name,
            'difficulty' => $this->faker->randomElement(['łatwy', 'umiarkowany', 'trudny']),
            'difficulty_detailed' => $this->faker->sentence(),
            'scenery' => $this->faker->numberBetween(0, 10),
            'rating' => $this->faker->randomFloat(1, 0, 10),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft', 'archived']),
        ];
    }

    public function active()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function draft()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function easy()
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'łatwy',
        ]);
    }

    public function moderate()
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'umiarkowany',
        ]);
    }

    public function hard()
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'trudny',
        ]);
    }
}
