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
     * Create a LINESTRING from coordinates.
     *
     * @param array $coordinates
     * @return string
     */
    private function createLineString(array $coordinates): string
    {
        return implode(', ', array_map(fn($coord) => "{$coord[0]} {$coord[1]}", $coordinates));
    }
}
