<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Reverse Geocoding",
 *     description="Odwrotne geokodowanie - konwersja współrzędnych na adresy"
 * )
 */
class ReverseGeocodingController
{
    /**
     * @OA\Get(
     *     path="/reverse-geocoding",
     *     summary="Odwrotne geokodowanie",
     *     description="Konwertuje współrzędne geograficzne na informacje o lokalizacji",
     *     operationId="reverseGeocode",
     *     tags={"Reverse Geocoding"},
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         required=true,
     *         description="Szerokość geograficzna",
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90)
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=true,
     *         description="Długość geograficzna",
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informacje o lokalizacji",
     *         @OA\JsonContent(ref="#/components/schemas/ReverseGeocodeResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nie można znaleźć lokalizacji",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unable to geocode the given coordinates")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Nieprawidłowe parametry",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="lat", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="lang", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
}
