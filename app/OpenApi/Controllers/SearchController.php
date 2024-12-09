<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Search",
 *     description="Endpointy wyszukiwania"
 * )
 */
class SearchController
{
    /**
     * @OA\Get(
     *     path="/search",
     *     summary="Wyszukiwarka",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         @OA\Schema(
     *             type="string",
     *             enum={"all", "trail", "region"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             maximum=100
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results"
     *     )
     * )
     */
    public function search() {}
}
