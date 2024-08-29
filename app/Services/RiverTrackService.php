<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RiverTrackService
{
    private $geodataService;

    public function __construct(GeodataService $geodataService)
    {
        $this->geodataService = $geodataService;
    }

    public function fetchRiverRoute(float $startLat, float $startLon, float $endLat, float $endLon): array
    {
        $query = $this->buildOverpassQuery($startLat, $startLon, $endLat, $endLon);
        Log::info("Overpass query: " . $query);

        $response = Http::get('https://overpass-api.de/api/interpreter', [
            'data' => $query
        ]);

        if ($response->failed()) {
            Log::error("Failed to fetch data from Overpass API. Status: " . $response->status());
            throw new \Exception("Failed to fetch data from Overpass API");
        }

        $data = $response->json();
        Log::info("Overpass API response: " . json_encode($data));

        $route = $this->extractRouteFromOverpassResponse($data, $startLat, $startLon, $endLat, $endLon);
        Log::info("Extracted route: " . json_encode($route));

        return $route;
    }

    private function buildOverpassQuery(float $startLat, float $startLon, float $endLat, float $endLon): string
    {
        $query = "[out:json][timeout:90];
        (
          way(around:10000,$startLat,$startLon)[\"waterway\"=\"river\"];
          way(around:10000,$endLat,$endLon)[\"waterway\"=\"river\"];
        )->.river_sections;
        node(w.river_sections)->.river_nodes;
        (
          .river_nodes;
          way(bn.river_nodes)[\"waterway\"=\"river\"];
        );
        out geom;";

        return $query;
    }

    private function extractRouteFromOverpassResponse(array $data, float $startLat, float $startLon, float $endLat, float $endLon): array
    {
        $nodes = [];
        $ways = [];

        foreach ($data['elements'] as $element) {
            if ($element['type'] === 'node') {
                $nodes[$element['id']] = [$element['lat'], $element['lon']];
            } elseif ($element['type'] === 'way') {
                $ways[] = $element['nodes'];
            }
        }

        Log::info("Extracted nodes: " . count($nodes) . ", ways: " . count($ways));

        $route = $this->buildRoute($ways, $nodes);
        Log::info("Built route: " . json_encode($route));

        return $this->ensureStartAndEndPoints($route, $startLat, $startLon, $endLat, $endLon);
    }

    private function buildRoute(array $ways, array $nodes): array
    {
        $route = [];
        $processedNodes = [];
        foreach ($ways as $way) {
            foreach ($way as $nodeId) {
                if (isset($nodes[$nodeId]) && !in_array($nodeId, $processedNodes)) {
                    $route[] = $nodes[$nodeId];
                    $processedNodes[] = $nodeId;
                }
            }
        }
        return $route;
    }

    private function ensureStartAndEndPoints(array $route, float $startLat, float $startLon, float $endLat, float $endLon): array
    {
        if (empty($route)) {
            Log::warning("Empty route, returning only start and end points");
            return [[$startLat, $startLon], [$endLat, $endLon]];
        }

        // Dodaj punkt startowy, jeśli jest daleko od pierwszego punktu trasy
        if ($this->geodataService->calculateDistance($startLat, $startLon, $route[0][0], $route[0][1]) > 1000) {
            Log::info("Adding start point to route");
            array_unshift($route, [$startLat, $startLon]);
        }

        // Dodaj punkt końcowy, jeśli jest daleko od ostatniego punktu trasy
        if ($this->geodataService->calculateDistance($endLat, $endLon, $route[count($route) - 1][0], $route[count($route) - 1][1]) > 1000) {
            Log::info("Adding end point to route");
            $route[] = [$endLat, $endLon];
        }

        return $route;
    }
}
