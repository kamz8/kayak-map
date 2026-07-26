<?php

namespace Database\Factories;

use App\Enums\RegionType;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use MatanYadaev\EloquentSpatial\Objects\Point;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition()
    {
        $name = $this->faker->city();

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'type' => RegionType::CITY,
            'parent_id' => null,
            'is_root' => false,
            'center_point' => new Point($this->faker->latitude, $this->faker->longitude),
            'area' => null, // Polygon is complex, leave null for tests
        ];
    }

    public function root()
    {
        return $this->state(fn (array $attributes) => [
            'is_root' => true,
            'parent_id' => null,
            'type' => RegionType::COUNTRY,
        ]);
    }

    public function withParent(Region $parent)
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'is_root' => false,
        ]);
    }
}