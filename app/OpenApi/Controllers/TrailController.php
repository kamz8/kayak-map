<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Trails",
 *     description="Endpoints for managing kayak trails"
 * )
 */
class TrailController
{
    /**
     * @OA\Get(
     *     path="/trails",
     *     summary="Lista szlaków",
     *     tags={"Trails"},
     *     @OA\Parameter(name="start_lat", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="end_lat", in="query", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="Lista szlaków")
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/trails/{slug}",
     *     summary="Szczegóły szlaku",
     *     tags={"Trails"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Szczegóły szlaku")
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *     path="/trails/nearby",
     *     summary="Pobliskie szlaki",
     *     tags={"Trails"},
     *     @OA\Parameter(name="lat", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="long", in="query", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="Lista pobliskich szlaków")
     * )
     */
    public function getNearbyTrails() {}

    /**
     * @OA\Get(
     *     path="/trails/{slug}/recommended",
     *     summary="Rekomendowane szlaki",
     *     tags={"Trails"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista rekomendowanych szlaków")
     * )
     */
    public function getRecommendedTrails() {}
}
