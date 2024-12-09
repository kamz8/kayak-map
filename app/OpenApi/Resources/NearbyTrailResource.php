<?php
namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="NearbyTrailResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="image", type="object", ref="#/components/schemas/ImageResource"),
 *     @OA\Property(property="rating", type="number", format="float"),
 *     @OA\Property(property="distance", type="string"),
 *     @OA\Property(property="difficulty", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="length", type="number", format="float"),
 *     @OA\Property(property="location_name", type="string"),
 *     @OA\Property(property="location_type", type="string")
 * )
 */

class NearbyTrailResource{}
