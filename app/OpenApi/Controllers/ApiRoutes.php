<?php
namespace App\OpenApi\Controllers;
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Kajaki API",
 *     description="API dla aplikacji szlaków kajakowych"
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1"
 * )
 */

/**
 * @OA\Get(
 *     path="/",
 *     summary="Strona powitalna API",
 *     tags={"General"},
 *     @OA\Response(
 *         response=200,
 *         description="Wiadomość powitalna",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Witamy w naszym api")
 *         )
 *     )
 * )
 */

/**
 * @OA\Tag(name="Trails", description="Endpointy związane ze szlakami")
 * @OA\Tag(name="Regions", description="Endpointy związane z regionami")
 * @OA\Tag(name="Weather", description="Endpointy pogodowe")
 * @OA\Tag(name="Geocoding", description="Endpointy geokodowania")
 * @OA\Tag(name="Maps", description="Endpointy związane z mapami")
 */

class ApiRoutes
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

    /**
     * @OA\Get(
     *     path="/trail/{slug}",
     *     summary="Szczegóły szlaku",
     *     tags={"Trails"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Szczegóły szlaku")
     * )
     */

    /**
     * @OA\Get(
     *     path="/trails/{slug}/recommended",
     *     summary="Rekomendowane szlaki",
     *     tags={"Trails"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista rekomendowanych szlaków")
     * )
     */

    /**
     * @OA\Get(
     *     path="/river-track/{id}",
     *     summary="Przebieg rzeki",
     *     tags={"Trails"},
     *     @OA\Parameter(name="id", in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dane przebiegu rzeki")
     * )
     */

    /**
     * @OA\Post(
     *     path="/upload-gpx",
     *     summary="Upload pliku GPX",
     *     tags={"Trails"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Plik GPX został przesłany")
     * )
     */

    /**
     * @OA\Get(
     *     path="/regions",
     *     summary="Lista regionów",
     *     tags={"Regions"},
     *     @OA\Response(response=200, description="Lista regionów")
     * )
     */

    /**
     * @OA\Get(
     *     path="/regions/{slug}",
     *     summary="Szczegóły regionu",
     *     tags={"Regions"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Szczegóły regionu")
     * )
     */

    /**
     * @OA\Get(
     *     path="/regions/{slug}/top-trails",
     *     summary="Najlepsze szlaki w regionie",
     *     tags={"Regions"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista najlepszych szlaków")
     * )
     */

    /**
     * @OA\Get(
     *     path="/regions/{slug}/top-trails-nearby",
     *     summary="Najlepsze pobliskie szlaki w regionie",
     *     tags={"Regions"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Lista najlepszych pobliskich szlaków")
     * )
     */

    /**
     * @OA\Get(
     *     path="/weather",
     *     summary="Dane pogodowe",
     *     tags={"Weather"},
     *     @OA\Parameter(name="lat", in="query", @OA\Schema(type="number")),
     *     @OA\Parameter(name="lon", in="query", @OA\Schema(type="number")),
     *     @OA\Response(response=200, description="Dane pogodowe")
     * )
     */

    /**
     * @OA\Post(
     *     path="/geocoding/reverse",
     *     summary="Odwrotne geokodowanie",
     *     tags={"Geocoding"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="lat", type="number"),
     *             @OA\Property(property="lon", type="number")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Wynik geokodowania")
     * )
     */

    /**
     * @OA\Get(
     *     path="/trails/{slug}/static-map",
     *     summary="Statyczna mapa szlaku",
     *     tags={"Maps"},
     *     @OA\Parameter(name="slug", in="path", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Obrazek mapy")
     * )
     */

    /**
     * @OA\Get(
     *     path="/search",
     *     summary="Wyszukiwanie",
     *     tags={"General"},
     *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Wyniki wyszukiwania")
     * )
     */
}
