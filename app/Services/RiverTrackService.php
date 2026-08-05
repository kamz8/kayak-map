<?php

namespace App\Services;

use App\Models\River;
use App\Models\Trail;
use App\Models\RiverTrack;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RiverTrackService
{
    private $geodataService;

    public function __construct(GeodataService $geodataService)
    {
        $this->geodataService = $geodataService;
    }
    /**
     * Generate a river track for a given trail.
     *
     * @param Trail $trail The trail to generate the river track for
     * @param River $river The river data
     * @return RiverTrack|null The generated river track or null if failed
     */
    public function generateRiverTrack(Trail $trail, River $river): ?RiverTrack
    {
        try {
            $riverPoints = collect(json_decode($river->path, true));

            if ($riverPoints->isEmpty()) {
                Log::error("No river points found for river: {$river->name}");
                return null;
            }

            $trailPoints = $trail->points()->orderBy('id')->get();

            Log::info("Generating path for trail: {$trail->id}");
            Log::info("River points count: " . $riverPoints->count());
            Log::info("Trail points count: " . $trailPoints->count());

            $path = $this->generatePath($riverPoints, $trailPoints, $trail);

            Log::info("Generated path points count: " . count($path));

            if (empty($path)) {
                Log::error("Failed to generate path for trail: {$trail->id}");
                return null;
            }

            $simplifiedPath = $this->geodataService->simplifyPath($path);

            Log::info("Simplified path points count: " . count($simplifiedPath));

            return RiverTrack::updateOrCreate(
                ['trail_id' => $trail->id],
                ['track_points' => json_encode($simplifiedPath)]
            );
        } catch (\Exception $e) {
            Log::error("Error generating river track for trail {$trail->id}: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Fetch river data from Overpass API.
     *
     * @param string $riverName
     * @param float $startLat
     * @param float $startLon
     * @param float $endLat
     * @param float $endLon
     * @return array|null
     */
    public function fetchRiverData(string $riverName, float $startLat, float $startLon, float $endLat, float $endLon): ?array
    {
        $bbox = $this->calculateBoundingBox($startLat, $startLon, $endLat, $endLon);
        $query = $this->buildOverpassQuery($riverName, $bbox);

        Log::info("Fetching river data for: $riverName");
        $response = Http::get('https://overpass-api.de/api/interpreter', [
            'data' => $query
        ]);

        if ($response->failed()) {
            Log::error("Failed to fetch data from Overpass API for river: $riverName");
            return null;
        }

        $data = $response->json();
        $riverPoints = $this->extractRiverPoints($data);

        if (empty($riverPoints)) {
            Log::error("No river points found in Overpass API response for river: $riverName");
            return null;
        }

        Log::info("Fetched " . count($riverPoints) . " points for river: $riverName");
        return $riverPoints;
    }

    /**
     * Fetch river data from Overpass API.
     *
     * @param Trail $trail The trail object
     * @return Collection Collection of river points
     */
    private function fetchRiverDataFromOverpass(Trail $trail): Collection
    {
        $query = $this->buildOverpassQuery($trail);
        $response = Http::get('https://overpass-api.de/api/interpreter', [
            'data' => $query
        ]);

        if ($response->failed()) {
            Log::error("Failed to fetch data from Overpass API: " . $response->body());
            throw new \Exception("Failed to fetch data from Overpass API");
        }

        $data = $response->json();
        $riverPoints = $this->extractRiverPointsFromOverpassResponse($data);

        // Save river data to database
        River::updateOrCreate(
            ['name' => $trail->river_name],
            ['path' => json_encode($riverPoints)]
        );

        return collect($riverPoints);
    }

    /**
     * Build Overpass API query.
     *
     * @param Trail $trail The trail object
     * @return string Overpass API query
     */
    private function buildOverpassQuery(Trail $trail): string
    {
        $bbox = $this->calculateBoundingBox($trail);
        return "[out:json];
        (
          way[\"waterway\"=\"river\"][\"name\"=\"{$trail->river_name}\"]($bbox);
          relation[\"waterway\"=\"river\"][\"name\"=\"{$trail->river_name}\"]($bbox);
        );
        (._;>;);
        out geom;";
    }

    /**
     * Calculate bounding box for Overpass API query.
     *
     * @param Trail $trail The trail object
     * @return string Bounding box string
     */
    private function calculateBoundingBox(Trail $trail): string
    {
        $minLat = min($trail->start_lat, $trail->end_lat);
        $maxLat = max($trail->start_lat, $trail->end_lat);
        $minLon = min($trail->start_lng, $trail->end_lng);
        $maxLon = max($trail->start_lng, $trail->end_lng);

        // Add some padding to the bounding box
        $padding = 0.1; // Approximately 11km at the equator
        return ($minLat - $padding) . "," . ($minLon - $padding) . "," . ($maxLat + $padding) . "," . ($maxLon + $padding);
    }

    /**
     * Extract river points from Overpass API response.
     *
     * @param array $data Overpass API response data
     * @return array Array of river points
     */
    private function extractRiverPointsFromOverpassResponse(array $data): array
    {
        $riverPoints = [];
        foreach ($data['elements'] as $element) {
            if (isset($element['geometry'])) {
                foreach ($element['geometry'] as $point) {
                    $riverPoints[] = [$point['lat'], $point['lon']];
                }
            }
        }
        return $riverPoints;
    }
    /**
     * Generate a path along the river based on trail points.
     *
     * @param Collection $riverPoints Collection of river points
     * @param Collection $trailPoints Collection of trail points
     * @param Trail $trail The trail object
     * @return array The generated path
     */
    private function generatePath(Collection $riverPoints, Collection $trailPoints, Trail $trail): array
    {
        $path = [];
        $start = [$trail->start_lat, $trail->start_lng];
        $end = [$trail->end_lat, $trail->end_lng];

        $path[] = $start;

        $nearestStartPoint = $this->geodataService->findNearestPointOnRiver($start, $riverPoints);
        if ($nearestStartPoint) {
            $path[] = $nearestStartPoint;
        }

        foreach ($trailPoints as $point) {
            $nearestRiverPoint = $this->geodataService->findNearestPointOnRiver([$point->lat, $point->lng], $riverPoints);
            if ($nearestRiverPoint) {
                $subPath = $this->geodataService->findRiverPath(end($path), $nearestRiverPoint, $riverPoints);
                if ($subPath) {
                    $path = array_merge($path, $subPath);
                } else {
                    Log::warning("Could not find river path for trail point: {$point->id}");
                    $path[] = $nearestRiverPoint;
                }
            } else {
                Log::warning("Could not find nearest river point for trail point: {$point->id}");
            }
        }

        $nearestEndPoint = $this->geodataService->findNearestPointOnRiver($end, $riverPoints);
        if ($nearestEndPoint) {
            $finalSubPath = $this->geodataService->findRiverPath(end($path), $nearestEndPoint, $riverPoints);
            if ($finalSubPath) {
                $path = array_merge($path, $finalSubPath);
            } else {
                Log::warning("Could not find river path to end point");
                $path[] = $nearestEndPoint;
            }
        } else {
            Log::warning("Could not find nearest river point for end point");
        }

        $path[] = $end;

        return $path;
    }


}
