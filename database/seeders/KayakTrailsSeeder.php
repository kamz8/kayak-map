<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trail;
use App\Models\River;
use App\Models\RiverTrack;
use App\Models\Section;
use App\Models\Point;
use App\Models\PointType;
use App\Models\Image;
use App\Enums\Difficulty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        // Pobierz przykładową rzekę z bazy danych
        $river = River::where('name', 'like', 'River%')->first();

        // Zakładamy, że path jest przechowywane jako LineString
        $riverPoints = $river->path->getCoordinates();

        $trails = [
            [
                'trail_name' => 'Szlak kajakowy Odra - Sekcja 1',
                'start_point' => $riverPoints[0],
                'end_point' => $riverPoints[10],
                'description' => 'Malowniczy szlak wzdłuż rzeki Odra, sekcja 1.',
                'trail_length' => $this->calculateDistance($riverPoints[0], $riverPoints[10]),
                'difficulty' => Difficulty::EASY,
                'scenery' => 8,
                'rating' => 4.5,
            ],
            // Dodaj więcej tras, zmieniając punkty startowe i końcowe
        ];

        foreach ($trails as $data) {
            $trail = Trail::create([
                'river_name' => $river->name,
                'trail_name' => $data['trail_name'],
                'slug' => Str::slug($data['trail_name']),
                'description' => $data['description'],
                'start_lat' => $data['start_point'][1],
                'start_lng' => $data['start_point'][0],
                'end_lat' => $data['end_point'][1],
                'end_lng' => $data['end_point'][0],
                'trail_length' => $data['trail_length'],
                'author' => 'Jan Kowalski',
                'difficulty' => $data['difficulty'],
                'scenery' => $data['scenery'],
                'rating' => $data['rating'],
            ]);

            $this->createRiverTrack($trail, $riverPoints);
            $this->createSections($trail, $riverPoints);
            $this->createPoints($trail, $riverPoints);
            $this->addImages($trail);
        }
    }

    private function createRiverTrack(Trail $trail, $riverPoints)
    {
        RiverTrack::create([
            'trail_id' => $trail->id,
            'track_points' => json_encode(array_slice($riverPoints, 0, 11)) // Przykładowe punkty trasy
        ]);
    }

    private function createSections(Trail $trail, $riverPoints)
    {
        for ($i = 0; $i < 3; $i++) {
            Section::create([
                'trail_id' => $trail->id,
                'name' => 'Sekcja ' . ($i + 1),
                'description' => 'Opis sekcji ' . ($i + 1),
                'polygon_coordinates' => json_encode(array_slice($riverPoints, $i * 3, 4)),
                'scenery' => rand(1, 10),
            ]);
        }
    }

    private function createPoints(Trail $trail, $riverPoints)
    {
        $pointTypes = PointType::all();

        for ($i = 0; $i < 5; $i++) {
            Point::create([
                'trail_id' => $trail->id,
                'point_type_id' => $pointTypes->random()->id,
                'name' => 'Punkt ' . ($i + 1),
                'description' => 'Opis punktu ' . ($i + 1),
                'lat' => $riverPoints[$i * 2][1],
                'lng' => $riverPoints[$i * 2][0]
            ]);
        }
    }

    private function addImages(Trail $trail)
    {
        $images = [
            [
                'path' => 'https://example.com/image1.jpg',
                'is_main' => true,
                'order' => 1
            ],
            [
                'path' => 'https://example.com/image2.jpg',
                'is_main' => false,
                'order' => 2
            ],
            [
                'path' => 'https://example.com/image3.jpg',
                'is_main' => false,
                'order' => 3
            ],
        ];

        foreach ($images as $data) {
            $trail->images()->create($data);
        }
    }

    private function calculateDistance($start, $end)
    {
        // Implementacja obliczania odległości między dwoma punktami [lat, lng]
        // Możesz użyć Haversine formula lub innej metody obliczania odległości
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
