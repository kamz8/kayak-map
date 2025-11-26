<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\LinkResource;
use App\Models\Link;
use App\Models\Trail;
use App\Models\Section;
use App\Services\Dashboard\LinkService;
use App\Traits\NotFoundResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Dashboard - Links",
 *     description="Zarządzanie linkami w panelu administracyjnym"
 * )
 */
class LinkController extends Controller
{
    use NotFoundResponse;

    public function __construct(
        private readonly LinkService $linkService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/trails/{id}/links",
     *     tags={"Dashboard - Links"},
     *     summary="Lista linków dla szlaku",
     *     description="Pobiera wszystkie linki przypisane do szlaku",
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
     *         description="Lista linków pobrana pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="trail", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="trail_name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Szlak nie został znaleziony"
     *     )
     * )
     */
    public function indexForTrail($id): JsonResponse
    {
        $result = $this->linkService->getLinksWithModel('trail', $id, ['id', 'trail_name']);

        if (!$result['model']) {
            return $this->notFoundResponse('Szlak nie został znaleziony');
        }

        return response()->json([
            'data' => LinkResource::collection($result['links']),
            'trail' => [
                'id' => $result['model']->id,
                'trail_name' => $result['model']->trail_name
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links",
     *     tags={"Dashboard - Links"},
     *     summary="Lista linków dla sekcji",
     *     description="Pobiera wszystkie linki przypisane do sekcji szlaku",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trailId",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sectionId",
     *         in="path",
     *         required=true,
     *         description="ID sekcji",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista linków pobrana pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="section", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sekcja nie została znaleziona"
     *     )
     * )
     */
    public function indexForSection($trailId, $sectionId): JsonResponse
    {
        $section = Section::where('trail_id', $trailId)
            ->where('id', $sectionId)
            ->select(['id', 'name', 'trail_id'])
            ->first();

        if (!$section) {
            return $this->notFoundResponse('Sekcja nie została znaleziona');
        }

        $result = $this->linkService->getLinksWithModel('section', $sectionId, ['id', 'name']);

        return response()->json([
            'data' => LinkResource::collection($result['links']),
            'section' => [
                'id' => $section->id,
                'name' => $section->name
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/dashboard/trails/{id}/links",
     *     tags={"Dashboard - Links"},
     *     summary="Utwórz link dla szlaku",
     *     description="Tworzy nowy link przypisany do szlaku",
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
     *             required={"url"},
     *             @OA\Property(property="url", type="string", example="https://youtube.com/watch?v=example"),
     *             @OA\Property(property="meta_data", type="string", example="{\"title\": \"Video\", \"description\": \"Tutorial\", \"icon\": \"mdi-youtube\"}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Link utworzony pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link został utworzony"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Szlak nie został znaleziony"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Błąd walidacji"
     *     )
     * )
     */
    public function storeForTrail(Request $request, $id): JsonResponse
    {
        $trail = Trail::find($id, ['id']);

        if (!$trail) {
            return $this->notFoundResponse('Szlak nie został znaleziony');
        }

        $validated = $request->validate([
            'url' => 'required|string|url',
            'meta_data' => 'nullable|string'
        ]);

        $link = $this->linkService->createLink($trail, $validated);

        return response()->json([
            'message' => 'Link został utworzony',
            'data' => new LinkResource($link)
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links",
     *     tags={"Dashboard - Links"},
     *     summary="Utwórz link dla sekcji",
     *     description="Tworzy nowy link przypisany do sekcji szlaku",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trailId",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sectionId",
     *         in="path",
     *         required=true,
     *         description="ID sekcji",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"url"},
     *             @OA\Property(property="url", type="string", example="https://facebook.com/page/example"),
     *             @OA\Property(property="meta_data", type="string", example="{\"title\": \"Facebook Page\", \"description\": \"\", \"icon\": \"mdi-facebook\"}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Link utworzony pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link został utworzony"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sekcja nie została znaleziona"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Błąd walidacji"
     *     )
     * )
     */
    public function storeForSection(Request $request, $trailId, $sectionId): JsonResponse
    {
        $section = Section::where('trail_id', $trailId)
            ->where('id', $sectionId)
            ->select(['id', 'trail_id'])
            ->first();

        if (!$section) {
            return $this->notFoundResponse('Sekcja nie została znaleziona');
        }

        $validated = $request->validate([
            'url' => 'required|string|url',
            'meta_data' => 'nullable|string'
        ]);

        $link = $this->linkService->createLink($section, $validated);

        return response()->json([
            'message' => 'Link został utworzony',
            'data' => new LinkResource($link)
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/dashboard/trails/{id}/links/{linkId}",
     *     tags={"Dashboard - Links"},
     *     summary="Aktualizuj link szlaku",
     *     description="Aktualizuje dane linku przypisanego do szlaku",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="linkId",
     *         in="path",
     *         required=true,
     *         description="ID linku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="https://updated-url.com"),
     *             @OA\Property(property="meta_data", type="string", example="{\"title\": \"Updated Title\", \"description\": \"\", \"icon\": \"mdi-web\"}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Link zaktualizowany pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link został zaktualizowany"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Link lub szlak nie został znaleziony"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Błąd walidacji"
     *     )
     * )
     */
    public function updateForTrail(Request $request, $id, $linkId): JsonResponse
    {
        $trail = Trail::find($id, ['id']);

        if (!$trail) {
            return $this->notFoundResponse('Szlak nie został znaleziony');
        }

        $link = Link::find($linkId);

        if (!$link) {
            return $this->notFoundResponse('Link nie został znaleziony');
        }

        // Verify link belongs to this trail
        if (!$this->linkService->linkBelongsToModel($link, $trail)) {
            return $this->notFoundResponse('Link nie należy do tego szlaku');
        }

        $validated = $request->validate([
            'url' => 'sometimes|required|string|url',
            'meta_data' => 'nullable|string'
        ]);

        $link = $this->linkService->updateLink($link, $validated);

        return response()->json([
            'message' => 'Link został zaktualizowany',
            'data' => new LinkResource($link)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links/{linkId}",
     *     tags={"Dashboard - Links"},
     *     summary="Aktualizuj link sekcji",
     *     description="Aktualizuje dane linku przypisanego do sekcji",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trailId",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sectionId",
     *         in="path",
     *         required=true,
     *         description="ID sekcji",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="linkId",
     *         in="path",
     *         required=true,
     *         description="ID linku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string"),
     *             @OA\Property(property="meta_data", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Link zaktualizowany pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link został zaktualizowany"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Link lub sekcja nie został znaleziony"
     *     )
     * )
     */
    public function updateForSection(Request $request, $trailId, $sectionId, $linkId): JsonResponse
    {
        $section = Section::where('trail_id', $trailId)
            ->where('id', $sectionId)
            ->select(['id', 'trail_id'])
            ->first();

        if (!$section) {
            return $this->notFoundResponse('Sekcja nie została znaleziona');
        }

        $link = Link::find($linkId);

        if (!$link) {
            return $this->notFoundResponse('Link nie został znaleziony');
        }

        // Verify link belongs to this section
        if (!$this->linkService->linkBelongsToModel($link, $section)) {
            return $this->notFoundResponse('Link nie należy do tej sekcji');
        }

        $validated = $request->validate([
            'url' => 'sometimes|required|string|url',
            'meta_data' => 'nullable|string'
        ]);

        $link = $this->linkService->updateLink($link, $validated);

        return response()->json([
            'message' => 'Link został zaktualizowany',
            'data' => new LinkResource($link)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/dashboard/trails/{id}/links/{linkId}",
     *     tags={"Dashboard - Links"},
     *     summary="Usuń link szlaku",
     *     description="Usuwa link przypisany do szlaku",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="linkId",
     *         in="path",
     *         required=true,
     *         description="ID linku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Link usunięty pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link został usunięty")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Link lub szlak nie został znaleziony"
     *     )
     * )
     */
    public function destroyForTrail($id, $linkId): JsonResponse
    {
        $trail = Trail::find($id, ['id']);

        if (!$trail) {
            return $this->notFoundResponse('Szlak nie został znaleziony');
        }

        $link = Link::find($linkId);

        if (!$link) {
            return $this->notFoundResponse('Link nie został znaleziony');
        }

        if (!$this->linkService->linkBelongsToModel($link, $trail)) {
            return $this->notFoundResponse('Link nie należy do tego szlaku');
        }

        $this->linkService->deleteLink($link);

        return response()->json([
            'message' => 'Link został usunięty'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/dashboard/trails/{trailId}/sections/{sectionId}/links/{linkId}",
     *     tags={"Dashboard - Links"},
     *     summary="Usuń link sekcji",
     *     description="Usuwa link przypisany do sekcji",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="trailId",
     *         in="path",
     *         required=true,
     *         description="ID szlaku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sectionId",
     *         in="path",
     *         required=true,
     *         description="ID sekcji",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="linkId",
     *         in="path",
     *         required=true,
     *         description="ID linku",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Link usunięty pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Link został usunięty")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Link lub sekcja nie został znaleziony"
     *     )
     * )
     */
    public function destroyForSection($trailId, $sectionId, $linkId): JsonResponse
    {
        $section = Section::where('trail_id', $trailId)
            ->where('id', $sectionId)
            ->select(['id', 'trail_id'])
            ->first();

        if (!$section) {
            return $this->notFoundResponse('Sekcja nie została znaleziona');
        }

        $link = Link::find($linkId);

        if (!$link) {
            return $this->notFoundResponse('Link nie został znaleziony');
        }

        // Verify link belongs to this section
        if (!$this->linkService->linkBelongsToModel($link, $section)) {
            return $this->notFoundResponse('Link nie należy do tej sekcji');
        }

        $this->linkService->deleteLink($link);

        return response()->json([
            'message' => 'Link został usunięty'
        ]);
    }
}
