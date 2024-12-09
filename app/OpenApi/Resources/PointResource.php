<?php
namespace App\OpenApi\Resources;
/**
 * @OA\Schema(
 *     schema="PointResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="trail_id", type="integer"),
 *     @OA\Property(property="point_type_id", type="integer"),
 *     @OA\Property(property="point_type_key", type="string", example="parking"),
 *     @OA\Property(property="name", type="string", example="Parking przy moście"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="length_at", type="number", format="float"),
 *     @OA\Property(property="lat", type="number", format="float"),
 *     @OA\Property(property="lng", type="number", format="float"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PointResource {}
