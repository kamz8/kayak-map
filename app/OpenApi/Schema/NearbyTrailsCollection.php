<?php
namespace App\OpenApi\Models\Schema;

/**
 * @OA\Schema(
 *     schema="NearbyTrailsCollection",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/NearbyTrailResource")
 *     ),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="total_trails", type="integer")
 * )
 */

class NearbyTrailsCollection{}
