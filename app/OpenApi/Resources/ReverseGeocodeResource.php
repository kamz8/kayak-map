<?php
namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="ReverseGeocodeResource",
 *     @OA\Property(property="country", type="string"),
 *     @OA\Property(property="state", type="string"),
 *     @OA\Property(property="city", type="string"),
 *     @OA\Property(property="lat", type="number", format="float"),
 *     @OA\Property(property="lang", type="number", format="float"),
 *     @OA\Property(property="slug", type="string")
 * )
 */
class ReverseGeocodeResource {}
