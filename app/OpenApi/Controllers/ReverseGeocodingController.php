<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Geocoding",
 *     description="Endpointy geokodowania"
 * )
 */
class ReverseGeocodingController
{
    /**
     * @OA\Get(
     *     path="/geocoding/reverse",
     *     summary="Odwrotne geokodowanie",
     *     tags={"Geocoding"},
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="number", minimum=-90, maximum=90)
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="number", minimum=-180, maximum=180)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unable to geocode coordinates"
     *     )
     * )
     */
    public function reverseGeocode() {}
}
