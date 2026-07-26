<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\PointTypeResource;
use App\Services\Dashboard\PointService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Dashboard - Points",
 *     description="Zarządzanie punktami i typami punktów w panelu administracyjnym"
 * )
 */
class PointController extends Controller
{
    public function __construct(
        private readonly PointService $pointService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/points/types",
     *     tags={"Dashboard - Points"},
     *     summary="Lista typów punktów",
     *     description="Pobiera wszystkie typy punktów (POI) z ikonami",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista typów punktów pobrana pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="key", type="string"),
     *                 @OA\Property(property="icon", type="string")
     *             ))
     *         )
     *     )
     * )
     */
    public function getPointTypes(): AnonymousResourceCollection
    {
        $pointTypes = $this->pointService->getAllPointTypes();

        return PointTypeResource::collection($pointTypes);
    }
}