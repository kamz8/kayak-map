<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Regions",
 *     description="Endpoints for managing regions"
 * )
 */
class RegionController
{
    /**
     * @OA\Get(
     *     path="/regions",
     *     summary="Lista regionów",
     *     tags={"Regions"},
     *     @OA\Response(response=200, description="Lista regionów")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/regions/{slug}",
     *     summary="Szczegóły regionu",
     *     tags={"Regions"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Szczegóły regionu")
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/regions/{slug}/top-trails",
     *     summary="Najlepsze szlaki w regionie",
     *     tags={"Regions"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista najlepszych szlaków")
     * )
     */
    public function getTopTrailsNearby() {}
}
