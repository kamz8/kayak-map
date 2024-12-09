<?php

namespace App\OpenApi\Requests;

/**
 * @OA\Schema(
 *     schema="NearbyTrailsRequest",
 *     title="Nearby Trails Request",
 *     description="Parametry żądania wyszukiwania pobliskich szlaków"
 * )
 */
class NearbyTrailsRequest
{
    /**
     * @OA\Property(
     *     property="lat",
     *     type="number",
     *     format="float",
     *     description="Szerokość geograficzna punktu centralnego",
     *     minimum=-90,
     *     maximum=90,
     *     example=50.0619
     * )
     */
    public $lat;

    /**
     * @OA\Property(
     *     property="long",
     *     type="number",
     *     format="float",
     *     description="Długość geograficzna punktu centralnego",
     *     minimum=-180,
     *     maximum=180,
     *     example=19.9368
     * )
     */
    public $long;

    /**
     * @OA\Property(
     *     property="location_name",
     *     type="string",
     *     description="Nazwa lokalizacji (alternatywa dla współrzędnych)",
     *     example="Kraków",
     *     nullable=true
     * )
     */
    public $location_name;
}
