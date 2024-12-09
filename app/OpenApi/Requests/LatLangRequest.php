<?php

namespace App\OpenApi\Requests;

/**
 * @OA\Schema(
 *     schema="LatLangRequest",
 *     title="Latitude Longitude Request",
 *     description="Parametry żądania zawierające współrzędne geograficzne"
 * )
 */
class LatLangRequest
{
    /**
     * @OA\Property(
     *     property="lat",
     *     type="number",
     *     format="float",
     *     description="Szerokość geograficzna",
     *     minimum=-90,
     *     maximum=90,
     *     example=50.0619
     * )
     */
    public $lat;

    /**
     * @OA\Property(
     *     property="lang",
     *     type="number",
     *     format="float",
     *     description="Długość geograficzna",
     *     minimum=-180,
     *     maximum=180,
     *     example=19.9368
     * )
     */
    public $lang;
}
