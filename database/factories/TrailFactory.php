<?php

namespace Database\Factories;

use App\Models\Trail;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrailFactory extends Factory
{
    protected $model = Trail::class;

    public function definition()
    {
        return [
            'river_name' => $this->faker->word,
            'trail_name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'start_lat' => $this->faker->latitude,
            'start_lng' => $this->faker->longitude,
            'end_lat' => $this->faker->latitude,
            'end_lng' => $this->faker->longitude,
            'trail_length' => $this->faker->numberBetween(1000, 100000),
            'author' => $this->faker->name
        ];
    }
}
