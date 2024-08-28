<?php

namespace Database\Seeders;

use App\Enums\PointType;
use App\Models\Point;
use App\Models\Trail;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PointSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $pointTypes = PointType::all();
        $trails = Trail::all();

        foreach ($trails as $trail) {
            $riverTrack = $trail->riverTrack;

            if ($riverTrack) {
                $trackPoints = json_decode($riverTrack->track_points, true);

                foreach ($trackPoints as $key => $trackPoint) {
                    // Dodaj punkt co 5 jednostek
                    if ($key % 5 == 0) {
                        Point::create([
                            'trail_id' => $trail->id,
                            'point_type_id' => $pointTypes->random()->id,
                            'name' => $faker->sentence(2),
                            'description' => $faker->paragraph,
                            'lat' => $this->getNearbyLatitude($trackPoint['lat']),
                            'lng' => $this->getNearbyLongitude($trackPoint['lng'])
                        ]);
                    }
                }
            }
        }
    }

    private function getNearbyLatitude($latitude)
    {
        $faker = Faker::create();
        return $latitude + $faker->randomFloat(7, -0.005, 0.005);
    }

    private function getNearbyLongitude($longitude)
    {
        $faker = Faker::create();
        return $longitude + $faker->randomFloat(7, -0.005, 0.005);
    }
}
