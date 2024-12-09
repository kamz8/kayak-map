<?php
namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="RiverTrackResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="trail_id", type="integer"),
 *     @OA\Property(property="track_points", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class RiverTrackResource {}
