<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Trail Geocoding",
 *     description="Endpointy do geokodowania szlaków kajakowych - pozwalają na pozyskanie informacji o regionach na podstawie współrzędnych geograficznych punktów startowych szlaków."
 * )
 */
class TrailGeocodingController
{
    /**
     * @OA\Get(
     *     path="/trail-geocoding/{id}/start-point",
     *     summary="Pobierz informacje o regionie punktu startowego",
     *     description="Na podstawie ID szlaku zwraca szczegółowe informacje o regionie, w którym znajduje się punkt startowy szlaku. Służy do kategoryzacji szlaków według położenia geograficznego.",
     *     operationId="getStartPointRegion",
     *     tags={"Trail Geocoding"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID szlaku kajakowego",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pomyślnie pobrano informacje o regionie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="trail_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID szlaku"
     *             ),
     *             @OA\Property(
     *                 property="trail_name",
     *                 type="string",
     *                 example="Szlak Dunajca z Nowego Targu do Szczawnicy",
     *                 description="Nazwa szlaku"
     *             ),
     *             @OA\Property(
     *                 property="start_point",
     *                 type="object",
     *                 description="Współrzędne punktu startowego",
     *                 @OA\Property(
     *                     property="latitude",
     *                     type="number",
     *                     format="float",
     *                     example=49.4375,
     *                     description="Szerokość geograficzna"
     *                 ),
     *                 @OA\Property(
     *                     property="longitude",
     *                     type="number",
     *                     format="float",
     *                     example=20.3244,
     *                     description="Długość geograficzna"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="region",
     *                 ref="#/components/schemas/RegionResource",
     *                 description="Szczegółowe informacje o regionie"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nie znaleziono szlaku lub nie można określić regionu",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unable to geocode the provided lat lang"
     *             )
     *         )
     *     )
     * )
     */
}
