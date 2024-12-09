<?php

namespace App\OpenApi\Requests;

/**
 * @OA\Schema(
 *     schema="RecommendedTrailsRequest",
 *     title="Recommended Trails Request",
 *     description="Parametry żądania pobierania rekomendowanych szlaków"
 * )
 */
class RecommendedTrailsRequest
{
    /**
     * @OA\Property(
     *     property="radius",
     *     type="integer",
     *     description="Maksymalny promień wyszukiwania rekomendowanych szlaków w kilometrach",
     *     minimum=1,
     *     maximum=100,
     *     default=50,
     *     example=30
     * )
     */
    public $radius;
}
