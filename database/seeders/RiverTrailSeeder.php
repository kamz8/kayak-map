<?php

namespace Database\Seeders;

use App\Enums\Difficulty;
use App\Enums\PointType;
use App\Models\Point;
use App\Models\River;
use App\Models\RiverTrack;
use App\Models\Section;
use App\Models\Trail;
use App\Services\RiverService;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RiverTrailSeeder extends Seeder
{
    protected $riverService;

    public function __construct(RiverService $riverService)
    {
        $this->riverService = $riverService;
    }

    public function run()
    {
        // Pobierz dane rzek płynących we Wrocławiu
        $this->riverService->fetchAndStoreRiversInTown('Wrocław');

        // Generuj 30 przykładowych tras
        DB::transaction(function () {
            $this->createSampleTrails();
        });
    }

    private function createSampleTrails()
    {
        $faker = Faker::create('pl_PL');
        $rivers = River::all();

        foreach ($rivers as $river) {
            $riverPoints = $river->path->getCoordinates();

            for ($i = 0; $i < 30; $i++) {
                $startIdx = $faker->numberBetween(0, count($riverPoints) - 10);
                $endIdx = $startIdx + $faker->numberBetween(5, 10);

                $trail = Trail::create([
                    'river_name' => $river->name,
                    'trail_name' => $faker->sentence(3),
                    'slug' => Str::slug($faker->sentence(3)),
                    'description' => $faker->paragraph,
                    'start_lat' => $riverPoints[$startIdx][1],
                    'start_lng' => $riverPoints[$startIdx][0],
                    'end_lat' => $riverPoints[$endIdx][1],
                    'end_lng' => $riverPoints[$endIdx][0],
                    'trail_length' => $this->calculateDistance($riverPoints[$startIdx], $riverPoints[$endIdx]),
                    'author' => $faker->name,
                    'difficulty' => $faker->randomElement([Difficulty::EASY, Difficulty::MODERATE, Difficulty::HARD]),
                    'scenery' => $faker->numberBetween(0, 10),
                    'rating' => $faker->randomFloat(1, 0, 5),
                ]);

                $this->createRiverTrack($trail, array_slice($riverPoints, $startIdx, $endIdx - $startIdx + 1));
                $this->createSections($trail, array_slice($riverPoints, $startIdx, $endIdx - $startIdx + 1));
                $this->createPoints($trail, array_slice($riverPoints, $startIdx, $endIdx - $startIdx + 1));
            }
        }
    }

    private function createRiverTrack(Trail $trail, $riverPoints)
    {
        RiverTrack::create([
            'trail_id' => $trail->id,
            'track_points' => json_encode($riverPoints)
        ]);
    }

    private function createSections(Trail $trail, $riverPoints)
    {
        $faker = Faker::create('pl_PL');

        for ($i = 0; $i < 3; $i++) {
            Section::create([
                'trail_id' => $trail->id,
                'name' => 'Sekcja ' . ($i + 1),
                'description' => $faker->paragraph,
                'polygon_coordinates' => json_encode(array_slice($riverPoints, $i * 3, 4)),
                'scenery' => $faker->numberBetween(0, 10),
            ]);
        }
    }

    private function createPoints(Trail $trail, $riverPoints)
    {
        $faker = Faker::create('pl_PL');
        $pointTypes = PointType::all();

        for ($i = 0; $i < 5; $i++) {
            Point::create([
                'trail_id' => $trail->id,
                'point_type_id' => $pointTypes->random()->id,
                'name' => 'Punkt ' . ($i + 1),
                'description' => $faker->paragraph,
                'lat' => $riverPoints[$i * 2][1],
                'lng' => $riverPoints[$i * 2][0]
            ]);
        }
    }

    private function calculateDistance($start, $end): float|int
    {
        // Implementacja obliczania odległości między dwoma punktami [lat, lng] za pomocą formuły Haversine
        $lat1 = deg2rad($start[1]);
        $lng1 = deg2rad($start[0]);
        $lat2 = deg2rad($end[1]);
        $lng2 = deg2rad($end[0]);

        $dlon = $lng2 - $lng1;
        $dlat = $lat2 - $lat1;

        $a = pow(sin($dlat / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dlon / 2), 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $r = 6371; // Promień Ziemi w kilometrach
        return $r * $c * 1000; // Zwraca odległość w metrach
    }
}
