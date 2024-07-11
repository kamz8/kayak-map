<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\Trail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'trail_id' => Trail::factory(),
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->paragraph,
            'polygon_coordinates' => json_encode(array_map(function () {
                return ['lat' => $this->faker->latitude, 'lng' => $this->faker->longitude];
            }, range(1, 10)))
        ];
    }
}
