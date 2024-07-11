<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\RiverTrack;
use App\Models\Section;
use App\Models\Point;
use App\Models\PointType;
use App\Enums\Difficulty;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TrailSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $pointTypes = PointType::all();

        for ($i = 0; $i < 10; $i++) {
            $trail = Trail::create([
                'river_name' => $faker->word,
                'trail_name' => $faker->sentence(3),
                'description' => $faker->paragraph,
                'start_lat' => $faker->latitude,
                'start_lng' => $faker->longitude,
                'end_lat' => $faker->latitude,
                'end_lng' => $faker->longitude,
                'trail_length' => $faker->numberBetween(1000, 100000),
                'author' => $faker->name,
                'difficulty' => $faker->randomElement([Difficulty::LATWY, Difficulty::UMIARKOWANY, Difficulty::TRUDNY]),
                'scenery' => $faker->numberBetween(0, 10)
            ]);

            $trackPoints = [];
            for ($j = 0; $j < 100; $j++) {
                $trackPoints[] = ['lat' => $faker->latitude, 'lng' => $faker->longitude];
            }

            RiverTrack::create([
                'trail_id' => $trail->id,
                'track_points' => json_encode($trackPoints)
            ]);

            for ($k = 0; $k < 5; $k++) {
                $polygonCoordinates = [];
                for ($l = 0; $l < 10; $l++) {
                    $polygonCoordinates[] = ['lat' => $faker->latitude, 'lng' => $faker->longitude];
                }

                Section::create([
                    'trail_id' => $trail->id,
                    'name' => $faker->sentence(2),
                    'description' => $faker->paragraph,
                    'polygon_coordinates' => json_encode($polygonCoordinates),
                    'scenery' => $faker->numberBetween(0, 10)
                ]);
            }

            for ($m = 0; $m < 20; $m++) {
                Point::create([
                    'trail_id' => $trail->id,
                    'point_type_id' => $pointTypes->random()->id,
                    'name' => $faker->sentence(2),
                    'description' => $faker->paragraph,
                    'lat' => $faker->latitude,
                    'lng' => $faker->longitude
                ]);
            }
        }
    }
}
