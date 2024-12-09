<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Trail Maps",
 *     description="Generowanie statycznej mapy (grafiki jpg) dla zobrazowania mapy szlaku.
 *      Generuje nam mapę leflet z zaznaczonym punktem start, stop, oraz ścieżką szlaku"
 * )
 */
class TrailMapController
{
    /**
     * @OA\Get(
     *     path="/trail-map/{slug}/static",
     *     summary="Pobierz statyczną mapę szlaku",
     *     description="Generuje i zwraca statyczną mapę dla wybranego szlaku",
     *     operationId="getStaticMap",
     *     tags={"Trail Maps"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug szlaku",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wygenerowana mapa",
     *         @OA\MediaType(
     *             mediaType="image/png",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Błąd generowania mapy",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Nie udało się wygenerować mapy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Szlak nie znaleziony"
     *     )
     * )
     */

}
