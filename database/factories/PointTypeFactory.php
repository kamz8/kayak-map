<?php

namespace Database\Factories;

use App\Enums\PointType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Enums\PointType>
 */
class PointTypeFactory extends Factory
{

    protected $model = PointType::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition()
    {
        return [
            'type' => $this->faker->randomElement([
                'Pole namiotowe',
                'Przeszkoda',
                'Niebezpieczeństwo',
                'Jaz',
                'Blokada na rzece'
            ])
        ];
    }
}
