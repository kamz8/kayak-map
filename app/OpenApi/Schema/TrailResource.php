<?php

namespace App\OpenApi\Models\Schema;

/**
 * @OA\Schema(
 *     schema="TrailResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="river_name", type="string"),
 *     @OA\Property(property="trail_name", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="main_image", ref="#/components/schemas/ImageResource"),
 *     @OA\Property(property="start_lat", type="number", format="float"),
 *     @OA\Property(property="start_lng", type="number", format="float"),
 *     @OA\Property(property="end_lat", type="number", format="float"),
 *     @OA\Property(property="end_lng", type="number", format="float"),
 *     @OA\Property(property="trail_length", type="integer"),
 *     @OA\Property(property="author", type="string"),
 *     @OA\Property(property="difficulty", type="string"),
 *     @OA\Property(property="scenery", type="integer"),
 *     @OA\Property(property="rating", type="number", format="float"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="images",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ImageResource")
 *     ),
 *     @OA\Property(property="river_track", ref="#/components/schemas/RiverTrackResource"),
 *     @OA\Property(
 *         property="sections",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SectionResource")
 *     ),
 *     @OA\Property(
 *         property="points",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PointResource")
 *     ),
 *     @OA\Property(
 *         property="regions",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/RegionResource")
 *     )
 * )
 */
class TrailResource {}
