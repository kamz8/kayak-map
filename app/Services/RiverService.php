<?php

namespace App\Services;

use App\Models\River;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Exception;

class RiverService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://overpass-api.de/api/',
            'timeout'  => 10.0,
        ]);
    }

    /**
     * Fetch and store river data for a given town.
     *
     * @param string $townName
     * @return void
     * @throws Exception
     */
    public function fetchAndStoreRiversInTown(string $townName): void
    {
        try {
            $riversData = $this->getRiverPathInTown($townName);

            DB::transaction(function() use ($riversData) {
                foreach ($riversData['elements'] as $element) {
                    if ($element['type'] === 'way' && isset($element['tags']['name'])) {
                        $coordinates = [];
                        foreach ($element['nodes'] as $node) {
                            foreach ($riversData['elements'] as $nodeElement) {
                                if ($nodeElement['type'] === 'node' && $nodeElement['id'] === $node) {
                                    $coordinates[] = [$nodeElement['lon'], $nodeElement['lat']];
                                }
                            }
                        }
                        $lineString = $this->createLineString($coordinates);
                        $river = new River();
                        $river->name = $element['tags']['name'];
                        $river->path = DB::raw("ST_GeomFromText('LINESTRING($lineString)', 4326)");
                        $river->save();
                    }
                }
            });
        } catch (Exception $e) {
            throw new Exception("Error storing river data: " . $e->getMessage());
        }
    }

    /**
     * Get river path in a given town.
     *
     * @param string $townName
     * @return array
     * @throws Exception
     */
    private function getRiverPathInTown(string $townName): array
    {
        $query = sprintf(
            '[out:json];area[name="%s"]->.searchArea;way["waterway"="river"](area.searchArea);(._;>;);out body;',
            $townName
        );

        return $this->executeQuery($query);
    }
    public function fetchRiverData(string $riverName): array
    {
        try {
            $query = $this->buildRiverQuery($riverName);
            $riverData = $this->executeQuery($query);

            if (empty($riverData['elements'])) {
                throw new Exception("No data found for river: $riverName");
            }

            $coordinates = $this->extractCoordinates($riverData['elements']);
            $lineString = $this->createLineString($coordinates);

            return [
                'name' => $riverName,
                'path' => "LINESTRING($lineString)"
            ];
        } catch (Exception $e) {
            throw new Exception("Error fetching river data: " . $e->getMessage());
        }
    }

    /**
     * Execute a query on Overpass API.
     *
     * @param string $query
     * @return array
     * @throws Exception
     */
    private function executeQuery(string $query): array
    {
        try {
            $overpassApiUrl = 'interpreter';

            $response = $this->client->post($overpassApiUrl, [
                'form_params' => ['data' => $query],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new Exception("Failed to execute query on Overpass API. Status: " . $response->getStatusCode() . " Response: " . $response->getBody());
            }

            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            throw new Exception("Error executing query: " . $e->getMessage());
        }
    }
    /**
     * Build the Overpass QL query for fetching river data.
     *
     * @param string $riverName
     * @return string
     */
    private function buildRiverQuery(string $riverName): string
    {
        return "[out:json];
            way[\"waterway\"=\"river\"][\"name\"=\"$riverName\"];
            (._;>;);
            out body;";
    }

    /**
     * Extract coordinates from Overpass API response.
     *
     * @param array $elements
     * @return array
     */
    private function extractCoordinates(array $elements): array
    {
        $nodes = [];
        $coordinates = [];

        foreach ($elements as $element) {
            if ($element['type'] === 'node') {
                $nodes[$element['id']] = [$element['lon'], $element['lat']];
            }
        }

        foreach ($elements as $element) {
            if ($element['type'] === 'way') {
                foreach ($element['nodes'] as $nodeId) {
                    if (isset($nodes[$nodeId])) {
                        $coordinates[] = $nodes[$nodeId];
                    }
                }
            }
        }

        return $coordinates;
    }
    /**
     * Create a LINESTRING from coordinates.
     *
     * @param array $coordinates
     * @return string
     */
    private function createLineString(array $coordinates): string
    {
        return implode(', ', array_map(fn($coord) => "{$coord[0]} {$coord[1]}", $coordinates));
    }

    public function extractLimitedTrackPoints(River $river, Trail $trail): array
    {
        $allPoints = $this->getRiverPoints($river);

        $startPoint = ['lat' => $trail->start_lat, 'lng' => $trail->start_lng];
        $endPoint = ['lat' => $trail->end_lat, 'lng' => $trail->end_lng];

        $startIndex = $this->findNearestPointIndex($allPoints, $startPoint);
        $endIndex = $this->findNearestPointIndex($allPoints, $endPoint);

        if ($startIndex > $endIndex) {
            $allPoints = array_reverse($allPoints);
            $startIndex = count($allPoints) - $startIndex - 1;
            $endIndex = count($allPoints) - $endIndex - 1;
        }

        $limitedPoints = array_slice($allPoints, $startIndex, $endIndex - $startIndex + 1);

        // Dodaj punkt startowy na początku, jeśli go nie ma
        if (!$this->pointsAreEqual($limitedPoints[0], $startPoint)) {
            array_unshift($limitedPoints, $startPoint);
        }

        // Dodaj punkt końcowy na końcu, jeśli go nie ma
        if (!$this->pointsAreEqual($limitedPoints[count($limitedPoints) - 1], $endPoint)) {
            $limitedPoints[] = $endPoint;
        }

        return $limitedPoints;
    }

    private function getRiverPoints(River $river): array
    {
        $query = "SELECT ST_AsText(path) as path_text FROM rivers WHERE id = ?";
        $result = DB::selectOne($query, [$river->id]);

        if (!$result || !$result->path_text) {
            throw new \Exception("Invalid river path data");
        }

        preg_match_all('/(-?\d+\.?\d*)\s(-?\d+\.?\d*)/', $result->path_text, $matches);

        $points = [];
        for ($i = 0; $i < count($matches[0]); $i++) {
            $points[] = [
                'lat' => floatval($matches[2][$i]),
                'lng' => floatval($matches[1][$i])
            ];
        }

        return $points;
    }

    private function findNearestPointIndex(array $points, array $targetPoint): int
    {
        return array_reduce(array_keys($points), function($carry, $index) use ($points, $targetPoint) {
            $distance = $this->geodataService->calculateDistance(
                $points[$index]['lat'], $points[$index]['lng'],
                $targetPoint['lat'], $targetPoint['lng']
            );
            return $distance < ($carry['distance'] ?? PHP_FLOAT_MAX) ? ['index' => $index, 'distance' => $distance] : $carry;
        }, [])['index'] ?? 0;
    }

    private function pointsAreEqual(array $point1, array $point2): bool
    {
        $epsilon = 0.00001; // Dopuszczalna różnica dla porównania liczb zmiennoprzecinkowych
        return abs($point1['lat'] - $point2['lat']) < $epsilon &&
            abs($point1['lng'] - $point2['lng']) < $epsilon;
    }
}
