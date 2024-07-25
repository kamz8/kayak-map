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

class KayakTrailsSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $this->createTrails();
        });
    }

    private function createTrails()
    {
        // Koordynaty tras wokół Wrocławia
        $trails = [
            [
                'river_name' => 'Odra',
                'trail_name' => 'Szlak kajakowy Odra',
                'description' => 'Malowniczy szlak wzdłuż rzeki Odra.',
                'start_lat' => 51.1100,
                'start_lng' => 17.0300,
                'end_lat' => 51.1200,
                'end_lng' => 17.0400,
                'trail_length' => 8000,
                'difficulty' => Difficulty::EASY,
                'scenery' => 8,
            ],
            [
                'river_name' => 'Bystrzyca',
                'trail_name' => 'Szlak kajakowy Bystrzyca',
                'description' => 'Piękny szlak wzdłuż rzeki Bystrzyca.',
                'start_lat' => 51.0700,
                'start_lng' => 16.9300,
                'end_lat' => 51.0800,
                'end_lng' => 16.9400,
                'trail_length' => 6000,
                'difficulty' => Difficulty::MODERATE,
                'scenery' => 9,
            ],
            [
                'river_name' => 'Oława',
                'trail_name' => 'Szlak kajakowy Oława',
                'description' => 'Spokojny i naturalny szlak wzdłuż rzeki Oława.',
                'start_lat' => 51.1000,
                'start_lng' => 17.0500,
                'end_lat' => 51.1100,
                'end_lng' => 17.0600,
                'trail_length' => 7000,
                'difficulty' => Difficulty::EASY,
                'scenery' => 7,
            ],
        ];

        foreach ($trails as $data) {
            $trail = Trail::create([
                'river_name' => $data['river_name'],
                'trail_name' => $data['trail_name'],
                'description' => $data['description'],
                'start_lat' => $data['start_lat'],
                'start_lng' => $data['start_lng'],
                'end_lat' => $data['end_lat'],
                'end_lng' => $data['end_lng'],
                'trail_length' => $data['trail_length'],
                'author' => 'Jan Kowalski',
                'difficulty' => $data['difficulty'],
                'scenery' => $data['scenery'],
            ]);

            $this->createRiverTrack($trail);
            $this->createSections($trail);
            $this->createPoints($trail);
        }
    }

    private function createRiverTrack(Trail $trail)
    {
        RiverTrack::create([
            'trail_id' => $trail->id,
            'track_points' => json_encode([
                ['lat' => $trail->start_lat, 'lng' => $trail->start_lng],
                ['lat' => ($trail->start_lat + $trail->end_lat) / 2, 'lng' => ($trail->start_lng + $trail->end_lng) / 2],
                ['lat' => $trail->end_lat, 'lng' => $trail->end_lng]
            ])
        ]);
    }

    private function createSections(Trail $trail)
    {
        for ($i = 0; $i < 3; $i++) {
            Section::create([
                'trail_id' => $trail->id,
                'name' => 'Sekcja ' . ($i + 1),
                'description' => 'Opis sekcji ' . ($i + 1),
                'polygon_coordinates' => json_encode([
                    ['lat' => $trail->start_lat, 'lng' => $trail->start_lng],
                    ['lat' => ($trail->start_lat + $trail->end_lat) / 2, 'lng' => ($trail->start_lng + $trail->end_lng) / 2],
                    ['lat' => $trail->end_lat, 'lng' => $trail->end_lng]
                ]),
                'scenery' => rand(1, 10),
            ]);
        }
    }

    private function createPoints(Trail $trail)
    {
        $pointTypes = PointType::all();

        for ($i = 0; $i < 5; $i++) {
            Point::create([
                'trail_id' => $trail->id,
                'point_type_id' => $pointTypes->random()->id,
                'name' => 'Punkt ' . ($i + 1),
                'description' => 'Opis punktu ' . ($i + 1),
                'lat' => $trail->start_lat + $i * 0.01,
                'lng' => $trail->start_lng + $i * 0.01
            ]);
        }
    }
}
