<?php
namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="RegionResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string", example="Mazowieckie"),
 *     @OA\Property(property="slug", type="string", example="mazowieckie"),
 *     @OA\Property(property="type", type="string", enum={"country", "state", "city", "geographic_area"}),
 *     @OA\Property(property="parent_id", type="integer", nullable=true),
 *     @OA\Property(property="is_root", type="boolean", example=false),
 *     @OA\Property(
 *         property="center_point",
 *         type="object",
 *         @OA\Property(property="latitude", type="number", format="float", example=52.237049),
 *         @OA\Property(property="longitude", type="number", format="float", example=21.017532)
 *     ),
 *     @OA\Property(property="parent", type="object", ref="#/components/schemas/RegionResource"),
 *     @OA\Property(
 *         property="children",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/RegionResource")
 *     )
 * )
 */
class RegionResource {}
