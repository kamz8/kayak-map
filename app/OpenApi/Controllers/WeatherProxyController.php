<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Weather",
 *     description="Endpointy pogodowe"
 * )
 */
class WeatherProxyController
{
    /**
     * @OA\Get(
     *     path="/weather",
     *     summary="Dane pogodowe",
     *     tags={"Weather"},
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="lon",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dane pogodowe"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing latitude or longitude"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching weather data"
     *     )
     * )
     */
    public function getWeather() {}
}
