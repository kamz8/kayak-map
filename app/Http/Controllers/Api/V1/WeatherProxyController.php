<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeatherProxyController extends Controller
{
    public function getWeather(Request $request)
    {
        $latitude = $request->query('lat');
        $longitude = $request->query('lon');

        if (!$latitude || !$longitude) {
            return response()->json(['error' => 'Missing latitude or longitude'], 400);
        }

        $cacheKey = "weather_data_{$latitude}_{$longitude}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude) {
                $client = new Client();
                $response = $client->request('GET', 'https://api.met.no/weatherapi/locationforecast/2.0/compact', [
                    'query' => [
                        'lat' => $latitude,
                        'lon' => $longitude,
                    ],
                    'headers' => [
                        'User-Agent' => 'Kayak backend/0.2 https://github.com/kamz8/kayak-map kamzil2@gmail.com'
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $weatherData = json_decode($response->getBody(), true);
                    $processedData = $this->processWeatherData($weatherData);
                    return response()->json($processedData);
                } else {
                    return response()->json(['error' => 'Failed to fetch weather data'], $response->getStatusCode());
                }
            });
        } catch (GuzzleException $e) {
            return response()->json(['error' => 'Error fetching weather data: ' . $e->getMessage()], 500);
        }
    }

    private function processWeatherData($weatherData)
    {
        $processedData = [
            'properties' => [
                'timeseries' => []
            ]
        ];

        $today = Carbon::today();
        $sevenDaysLater = $today->copy()->addDays(7);

        foreach ($weatherData['properties']['timeseries'] as $timeseriesData) {
            $dataTime = Carbon::parse($timeseriesData['time']);

            if ($dataTime->between($today, $sevenDaysLater) && $dataTime->hour == 12) {
                $processedData['properties']['timeseries'][] = $timeseriesData;
            }

            if (count($processedData['properties']['timeseries']) >= 7) {
                break;
            }
        }

        return $processedData;
    }
}
