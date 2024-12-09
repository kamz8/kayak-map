<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
/**
 * @OA\Schema(
 *     schema="RecommendedTrailsCollection",
 *     type="object",
 *     @OA\Property(property="status", type="string", example="success"),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RecommendedTrailResource")),
 *     @OA\Property(property="message", type="string", example="Recommended trails retrieved successfully."),
 *     @OA\Property(property="total_trails", type="integer", example=10)
 * )
 */
class RecommendedTrailsCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'status' => 'success',
            'data' => RecommendedTrailResource::collection($this->collection),
            'message' => 'Recommended trails retrieved successfully.',
            'total_trails' => $this->collection->count(),
        ];
    }
}
