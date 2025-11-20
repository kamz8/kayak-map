<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Trail\IndexTrailRequest;
use App\Http\Resources\Dashboard\TrailResource;
use App\Models\Trail;
use App\Services\Dashboard\DashboardTrailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Dashboard - Trails",
 *     description="Zarządzanie szlakami w panelu administracyjnym"
 * )
 */
class TrailController extends Controller
{
    public function __construct(
        private readonly DashboardTrailService $trailService
    ) {
        // Middleware are handled at route level
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/trails",
     *     tags={"Dashboard - Trails"},
     *     summary="Lista szlaków dla dashboardu",
     *     description="Pobiera paginowaną listę szlaków z zaawansowanymi filtrami",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numer strony",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Liczba elementów na stronę (max 100)",
     *         @OA\Schema(type="integer", default=15, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Wyszukiwanie w nazwie szlaku, rzeki lub opisie",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtruj po statusie",
     *         @OA\Schema(type="string", enum={"active", "inactive", "draft", "archived"})
     *     ),
     *     @OA\Parameter(
     *         name="statuses[]",
     *         in="query",
     *         description="Filtruj po wielu statusach",
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"active", "inactive", "draft", "archived"}))
     *     ),
     *     @OA\Parameter(
     *         name="difficulty",
     *         in="query",
     *         description="Filtruj po trudności",
     *         @OA\Schema(type="string", enum={"łatwy", "umiarkowany", "trudny"})
     *     ),
     *     @OA\Parameter(
     *         name="difficulties[]",
     *         in="query",
     *         description="Filtruj po wielu poziomach trudności",
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"łatwy", "umiarkowany", "trudny"}))
     *     ),
     *     @OA\Parameter(
     *         name="region_id",
     *         in="query",
     *         description="Filtruj po ID regionu",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="region_ids[]",
     *         in="query",
     *         description="Filtruj po wielu regionach",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Data początkowa (utworzenia)",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Data końcowa (utworzenia)",
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="min_scenery",
     *         in="query",
     *         description="Minimalna ocena krajobrazu (0-10)",
     *         @OA\Schema(type="integer", minimum=0, maximum=10)
     *     ),
     *     @OA\Parameter(
     *         name="max_scenery",
     *         in="query",
     *         description="Maksymalna ocena krajobrazu (0-10)",
     *         @OA\Schema(type="integer", minimum=0, maximum=10)
     *     ),
     *     @OA\Parameter(
     *         name="min_rating",
     *         in="query",
     *         description="Minimalna ocena szlaku (0-10)",
     *         @OA\Schema(type="number", minimum=0, maximum=10)
     *     ),
     *     @OA\Parameter(
     *         name="max_rating",
     *         in="query",
     *         description="Maksymalna ocena szlaku (0-10)",
     *         @OA\Schema(type="number", minimum=0, maximum=10)
     *     ),
     *     @OA\Parameter(
     *         name="min_length",
     *         in="query",
     *         description="Minimalna długość szlaku (km)",
     *         @OA\Schema(type="integer", minimum=0)
     *     ),
     *     @OA\Parameter(
     *         name="max_length",
     *         in="query",
     *         description="Maksymalna długość szlaku (km)",
     *         @OA\Schema(type="integer", minimum=0)
     *     ),
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Filtruj po autorze",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sortuj według pola",
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "trail_name", "river_name", "difficulty", "scenery", "rating", "trail_length", "status", "created_at", "updated_at"},
     *             default="created_at"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Kolejność sortowania",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="with[]",
     *         in="query",
     *         description="Dodatkowe relacje do załadowania",
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"images", "sections", "points", "riverTrack"}))
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista szlaków pobrana pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nieautoryzowany dostęp"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Brak uprawnień"
     *     )
     * )
     */
    public function index(IndexTrailRequest $request): AnonymousResourceCollection
    {
        $filters = $request->getFilters();
        $trails = $this->trailService->getTrailsForDashboard($filters);

        return TrailResource::collection($trails);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/trails/statistics",
     *     tags={"Dashboard - Trails"},
     *     summary="Statystyki szlaków",
     *     description="Pobiera statystyki dotyczące szlaków",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statystyki pobrane pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="total", type="integer", example=156),
     *             @OA\Property(property="by_status", type="object",
     *                 @OA\Property(property="active", type="integer", example=120),
     *                 @OA\Property(property="inactive", type="integer", example=10),
     *                 @OA\Property(property="draft", type="integer", example=20),
     *                 @OA\Property(property="archived", type="integer", example=6)
     *             ),
     *             @OA\Property(property="by_difficulty", type="object",
     *                 @OA\Property(property="easy", type="integer", example=50),
     *                 @OA\Property(property="moderate", type="integer", example=80),
     *                 @OA\Property(property="hard", type="integer", example=26)
     *             ),
     *             @OA\Property(property="averages", type="object",
     *                 @OA\Property(property="rating", type="number", format="float", example=7.85),
     *                 @OA\Property(property="scenery", type="number", format="float", example=8.2)
     *             ),
     *             @OA\Property(property="total_length_km", type="integer", example=12500),
     *             @OA\Property(property="recent_count", type="integer", example=15)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Nieautoryzowany dostęp"
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->trailService->getStatistics();

        return response()->json($statistics);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/trails/{id}",
     *     tags={"Dashboard - Trails"},
     *     summary="Szczegóły szlaku",
     *     description="Pobiera szczegółowe informacje o szlaku",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Szczegóły szlaku pobrane pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Szlak nie został znaleziony"
     *     )
     * )
     */
    public function show(Trail $trail): TrailResource
    {
        $trail = $this->trailService->getTrailForDashboard($trail);

        return new TrailResource($trail);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/dashboard/trails/{id}/status",
     *     tags={"Dashboard - Trails"},
     *     summary="Zmień status szlaku",
     *     description="Aktualizuje status szlaku",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "draft", "archived"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status zaktualizowany pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Status szlaku został zaktualizowany"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Nieprawidłowy status"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Szlak nie został znaleziony"
     *     )
     * )
     */
    public function changeStatus(Trail $trail, \Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:active,inactive,draft,archived'
        ]);

        try {
            $trail = $this->trailService->changeStatus($trail, $validated['status']);

            return response()->json([
                'message' => 'Status szlaku został zaktualizowany',
                'data' => new TrailResource($trail)
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}