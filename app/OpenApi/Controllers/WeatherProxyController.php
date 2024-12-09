<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Weather",
 *     description="Endpointy pogodowe - dostarczają dane o pogodzie dla wybranych lokalizacji poprzez proxy do serwisu met.no. Umożliwiają sprawdzenie prognozy pogody dla szlaków kajakowych."
 * )
 */
class WeatherProxyController
{
    /**
     * @OA\Get(
     *     path="/weather",
     *     summary="Pobierz prognozę pogody",
     *     description="Pobiera 7-dniową prognozę pogody dla wskazanej lokalizacji. Dane są pobierane z serwisu met.no i buforowane przez godzinę. Zwracana jest prognoza na każdy dzień o godzinie 12:00.",
     *     operationId="getWeather",
     *     tags={"Weather"},
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         required=true,
     *         description="Szerokość geograficzna punktu",
     *         @OA\Schema(
     *             type="number",
     *             format="float",
     *             minimum=-90,
     *             maximum=90,
     *             example=49.424
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lon",
     *         in="query",
     *         required=true,
     *         description="Długość geograficzna punktu",
     *         @OA\Schema(
     *             type="number",
     *             format="float",
     *             minimum=-180,
     *             maximum=180,
     *             example=20.323
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prognoza pogody",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="properties",
     *                 type="object",
     *                 @OA\Property(
     *                     property="timeseries",
     *                     type="array",
     *                     description="Seria prognoz pogody dla kolejnych dni",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="time",
     *                             type="string",
     *                             format="date-time",
     *                             description="Data i godzina prognozy",
     *                             example="2024-03-10T12:00:00Z"
     *                         ),
     *                         @OA\Property(
     *                             property="data",
     *                             type="object",
     *                             @OA\Property(
     *                                 property="instant",
     *                                 type="object",
     *                                 description="Natychmiastowe parametry pogodowe"
     *                             ),
     *                             @OA\Property(
     *                                 property="next_12_hours",
     *                                 type="object",
     *                                 description="Prognoza na następne 12 godzin"
     *                             ),
     *                             @OA\Property(
     *                                 property="next_1_hours",
     *                                 type="object",
     *                                 description="Prognoza na następną godzinę"
     *                             ),
     *                             @OA\Property(
     *                                 property="next_6_hours",
     *                                 type="object",
     *                                 description="Prognoza na następne 6 godzin"
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Brak wymaganych parametrów",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Missing latitude or longitude"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Błąd podczas pobierania danych pogodowych",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error fetching weather data: Connection timeout"
     *             )
     *         )
     *     )
     * )
     */
}
