<?php

namespace App\OpenApi\Controllers;

class ApiRoutes
{
    /**
     * @OA\Get(
     *     path="/trails",
     *     summary="Get list of trails",
     *     tags={"Trails"},
     *     @OA\Parameter(name="start_lat", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="end_lat", in="query", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="List of trails")
     * )
     */

    /**
     * @OA\Get(
     *     path="/trails/{slug}",
     *     summary="Get trail details",
     *     tags={"Trails"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Trail details")
     * )
     */

    /**
     * @OA\Get(
     *     path="/nearby-trails",
     *     summary="Get nearby trails",
     *     tags={"Trails"},
     *     @OA\Parameter(name="lat", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="long", in="query", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="List of nearby trails")
     * )
     */

    /**
     * @OA\Get(
     *     path="/trails/{slug}/recommended",
     *     summary="Get recommended trails",
     *     tags={"Trails"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of recommended trails")
     * )
     */

    /**
     * @OA\Get(
     *     path="/regions",
     *     summary="Get list of regions",
     *     tags={"Regions"},
     *     @OA\Response(response=200, description="List of regions")
     * )
     */

    /**
     * @OA\Get(
     *     path="/regions/{slug}",
     *     summary="Get region details",
     *     tags={"Regions"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Region details")
     * )
     */

    /**
     * @OA\Get(
     *     path="/weather",
     *     summary="Get weather data",
     *     tags={"Weather"},
     *     @OA\Parameter(name="lat", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="lon", in="query", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="Weather data")
     * )
     */

    /**
     * @OA\Post(
     *     path="/gpx/upload",
     *     summary="Upload GPX file",
     *     tags={"GPX"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="File uploaded")
     * )
     */
}
