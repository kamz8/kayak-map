<?php

namespace Kamz8\LaravelOverpass\Helpers;

use Kamz8\LaravelOverpass\Overpass;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class RouteHelper
{
    protected Overpass $overpass;

    public function __construct()
    {
        $this->overpass = new Overpass();
    }

    public function findRoute(float $lat1, float $lon1, float $lat2, float $lon2): array
    {
        // Implementacja logiki znajdowania trasy wodnej między dwoma punktami
        $query = "
        [out:json][timeout:60];
        (
          way(around:1000,{$lat1},{$lon1})['waterway'];
          way(around:1000,{$lat2},{$lon2})['waterway'];
        );
        out body;
        >;
        out skel qt;
        ";

        try {
            $response = $this->overpass->client->request('POST', '', [
                'form_params' => ['data' => $query],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            // Przetwórz i zwróć dane według potrzeb
            return $data;

        } catch (GuzzleException $e) {
            throw new Exception("Błąd podczas komunikacji z Overpass API: " . $e->getMessage(), $e->getCode(), $e);
        } catch (JsonException $e) {
            throw new Exception("Błąd podczas parsowania odpowiedzi JSON: " . $e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            throw new Exception("Błąd podczas znajdowania trasy: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
