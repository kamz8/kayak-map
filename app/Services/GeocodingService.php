<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Exception;

class GeocodingService
{
    private string $nominatimBaseUrl = 'https://nominatim.openstreetmap.org/';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->nominatimBaseUrl,
            'headers' => [
                'User-Agent' => 'Kayak dev (kamzil2@gmail.com)',
            ],
        ]);
    }

    public function geocode($address)
    {
        try {
            $response = $this->client->get('search', [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'addressdetails' => 1
                ]
            ]);

            Log::info("Geocoding response", ['status' => $response->getStatusCode(), 'body' => $response->getBody()]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new Exception('Failed to fetch geocoding data.');
            }
        } catch (GuzzleException $e) {
            Log::error("Geocoding error", ['message' => $e->getMessage()]);
            throw new Exception('Error during geocoding: ' . $e->getMessage());
        }
    }

    public function fetchAreaBoundaries($osmId, $osmType)
    {
        try {
            $response = $this->client->get('details', [
                'query' => [
                    'osmtype' => $osmType,
                    'osmid' => $osmId,
                    'format' => 'json',
                    'polygon_geojson' => 1
                ]
            ]);

            Log::info("Area boundaries response", ['status' => $response->getStatusCode(), 'body' => $response->getBody()]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                return $data['geometry']['coordinates'][0] ?? null;
            } else {
                throw new Exception('Failed to fetch area boundaries.');
            }
        } catch (GuzzleException $e) {
            Log::error("Error fetching area boundaries", ['osmId' => $osmId, 'osmType' => $osmType, 'error' => $e->getMessage()]);
            throw new Exception('Error fetching area boundaries: ' . $e->getMessage());
        }
    }
}
