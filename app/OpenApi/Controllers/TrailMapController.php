<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Maps",
 *     description="Endpointy map"
 * )
 */
class TrailMapController
{
    /**
     * @OA\Get(
     *     path="/trails/{slug}/static-map",
     *     summary="Statyczna mapa szlaku",
     *     tags={"Maps"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Map image",
     *         @OA\MediaType(
     *             mediaType="image/png"
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error generating map"
     *     )
     * )
     */
    public function getStaticMap() {}

    /**
     * @OA\Get(
     *     path="/trails/{slug}/test-map",
     *     summary="Test generowania mapy szlaku. Zwraca html, któy będzie zapisany",
     *     tags={"Maps"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Test map HTML"
     *     )
     * )
     */
    public function testMap() {}
}
