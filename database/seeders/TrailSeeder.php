<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\RiverTrack;
use App\Models\Section;
use App\Models\Point;
use App\Models\Image;
use App\Models\PointType;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TrailSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Seed Trails
        for ($i = 0; $i < 10; $i++) {
            $trail = Trail::factory()->create();

            // Seed River Tracks
            $trackPoints = [];
            for ($j = 0; $j < 100; $j++) {
                $trackPoints[] = ['lat' => $faker->latitude, 'lng' => $faker->longitude];
            }

            RiverTrack::create([
                'trail_id' => $trail->id,
                'track_points' => json_encode($trackPoints)
            ]);

            // Seed Sections
            for ($k = 0; $k < 5; $k++) {
                $polygonCoordinates = [];
                for ($l = 0; $l < 10; $l++) {
                    $polygonCoordinates[] = ['lat' => $faker->latitude, 'lng' => $faker->longitude];
                }

                $section = Section::factory()->create([
                    'trail_id' => $trail->id,
                    'polygon_coordinates' => json_encode($polygonCoordinates)
                ]);

                // Seed Links for Sections
                DB::table('links')->insert([
                    'section_id' => $section->id,
                    'url' => $faker->url,
                    'meta_data' => $faker->sentence,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Seed Points
            for ($m = 0; $m < 20; $m++) {
                Point::factory()->create([
                    'trail_id' => $trail->id,
                    'point_type_id' => PointType::inRandomOrder()->first()->id
                ]);
            }
        }
    }
}
