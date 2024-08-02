<?php

namespace Database\Seeders;

use App\Models\River;
use App\Models\Trail;
use App\Models\Image;
use App\Models\Imageable;
use App\Models\RiverTrack;
use App\Services\GeodataService;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrailSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $geodataService = new GeodataService();

        $rivers = River::all();
        $images = Image::all();

        for ($i = 0; $i < 30; $i++) {
            $riverPaths = $this->getRandomRiverPaths($rivers, $geodataService);

            if (empty($riverPaths)) {
                continue; // Skip if no valid river paths found
            }

            $startCoord = $riverPaths[0]['coordinates'][0];
            $endCoord = $riverPaths[count($riverPaths) - 1]['coordinates'][count($riverPaths[count($riverPaths) - 1]['coordinates']) - 1];

            $trail = Trail::create([
                'river_name' => $this->getRiverNames($riverPaths),
                'trail_name' => $faker->sentence(3),
                'slug' => Str::slug($faker->sentence(3)),
                'description' => $faker->paragraph,
                'start_lat' => $startCoord[1],
                'start_lng' => $startCoord[0],
                'end_lat' => $endCoord[1],
                'end_lng' => $endCoord[0],
                'trail_length' => $this->calculateTrailLength($riverPaths, $geodataService),
                'author' => $faker->name,
                'difficulty' => $faker->randomElement(['łatwy', 'umiarkowany', 'trudny']),
                'scenery' => $faker->numberBetween(0, 10),
                'rating' => $faker->randomFloat(1, 0, 5),
            ]);

            // Save river paths to river_tracks
            $trackPoints = [];
            foreach ($riverPaths as $path) {
                $trackPoints = array_merge($trackPoints, $path['coordinates']);
            }

            RiverTrack::create([
                'trail_id' => $trail->id,
                'track_points' => json_encode($trackPoints)
            ]);

            // Assign images to the trail
            $trailImages = $images->random($faker->numberBetween(2, 5));
            foreach ($trailImages as $index => $image) {
                Imageable::create([
                    'image_id' => $image->id,
                    'imageable_id' => $trail->id,
                    'imageable_type' => Trail::class,
                    'is_main' => $index === 0, // Set the first image as the main image
                    'order' => $index + 1,
                ]);
            }
        }
    }

    /**
     * Get random river paths with a minimum total length of 500 meters.
     *
     * @param \Illuminate\Support\Collection $rivers
     * @param GeodataService $geodataService
     * @return array
     */
    private function getRandomRiverPaths($rivers, $geodataService)
    {
        $faker = Faker::create();
        $totalLength = 0;
        $riverPaths = [];

        while ($totalLength < 0.5) {
            $river = $rivers->random();
            $path = DB::select("SELECT ST_AsText(path) as path FROM rivers WHERE id = ?", [$river->id])[0]->path;
            $coordinates = $this->parseLineString($path);

            if (count($coordinates) < 2) {
                continue; // Skip if there are not enough coordinates
            }

            $startIndex = $faker->numberBetween(0, count($coordinates) - 2);
            $endIndex = $faker->numberBetween($startIndex + 1, count($coordinates) - 1);

            $segment = array_slice($coordinates, $startIndex, $endIndex - $startIndex + 1);
            $segmentLength = $geodataService->calculateDistance(
                $segment[0][1], $segment[0][0],
                $segment[count($segment) - 1][1], $segment[count($segment) - 1][0]
            );

            $riverPaths[] = [
                'name' => $river->name,
                'coordinates' => $segment
            ];
            $totalLength += $segmentLength;
        }

        return $riverPaths;
    }

    /**
     * Calculate the total length of a trail.
     *
     * @param array $riverPaths
     * @param GeodataService $geodataService
     * @return float
     */
    private function calculateTrailLength(array $riverPaths, GeodataService $geodataService)
    {
        $totalLength = 0;

        for ($i = 0; $i < count($riverPaths); $i++) {
            $segment = $riverPaths[$i]['coordinates'];
            for ($j = 0; $j < count($segment) - 1; $j++) {
                $totalLength += $geodataService->calculateDistance(
                    $segment[$j][1], $segment[$j][0],
                    $segment[$j + 1][1], $segment[$j + 1][0]
                );
            }
        }

        return $totalLength;
    }

    /**
     * Get a concatenated string of river names.
     *
     * @param array $riverPaths
     * @return string
     */
    private function getRiverNames(array $riverPaths)
    {
        $riverNames = [];
        foreach ($riverPaths as $path) {
            $riverNames[] = $path['name'];
        }
        return implode(', ', $riverNames);
    }

    /**
     * Parse LINESTRING to an array of coordinates.
     *
     * @param string $lineString
     * @return array
     */
    private function parseLineString(string $lineString): array
    {
        $lineString = str_replace('LINESTRING(', '', $lineString);
        $lineString = str_replace(')', '', $lineString);
        $points = explode(',', $lineString);

        $coordinates = [];
        foreach ($points as $point) {
            $coords = explode(' ', trim($point));
            $coordinates[] = [(float) $coords[0], (float) $coords[1]];
        }

        return $coordinates;
    }
}
