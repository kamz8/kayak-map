<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="Trails",
 *     description="Endpointy do obsługi szlaków kajakowych - umożliwiają przeglądanie, filtrowanie i wyszukiwanie szlaków oraz pobieranie rekomendowanych i pobliskich tras. Dostarczają szczegółowe informacje o każdym szlaku."
 * )
 */
class TrailController
{
    /**
     * @OA\Get(
     *     path="/trails",
     *     summary="Lista szlaków",
     *     description="Zwraca listę szlaków kajakowych z możliwością filtrowania według różnych kryteriów. Wyniki zawierają podstawowe informacje o szlakach oraz metadane o regionie.",
     *     operationId="index",
     *     tags={"Trails"},
     *     @OA\Parameter(
     *         name="start_lat",
     *         in="query",
     *         required=true,
     *         description="Szerokość geograficzna początku obszaru wyszukiwania",
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90)
     *     ),
     *     @OA\Parameter(
     *         name="end_lat",
     *         in="query",
     *         required=true,
     *         description="Szerokość geograficzna końca obszaru wyszukiwania",
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90)
     *     ),
     *     @OA\Parameter(
     *         name="start_lng",
     *         in="query",
     *         required=true,
     *         description="Długość geograficzna początku obszaru wyszukiwania",
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180)
     *     ),
     *     @OA\Parameter(
     *         name="end_lng",
     *         in="query",
     *         required=true,
     *         description="Długość geograficzna końca obszaru wyszukiwania",
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180)
     *     ),
     *     @OA\Parameter(
     *         name="difficulty",
     *         in="query",
     *         required=false,
     *         description="Filtrowanie według poziomu trudności",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string", enum={"łatwy", "umiarkowany", "trudny"})
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="scenery",
     *         in="query",
     *         required=false,
     *         description="Filtrowanie według oceny krajobrazów (0-10)",
     *         @OA\Schema(type="integer", minimum=0, maximum=10)
     *     ),
     *     @OA\Parameter(
     *         name="search_query",
     *         in="query",
     *         required=false,
     *         description="Fraza wyszukiwania",
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="min_length",
     *         in="query",
     *         required=false,
     *         description="Minimalna długość szlaku (w metrach)",
     *         @OA\Schema(type="integer", minimum=0)
     *     ),
     *     @OA\Parameter(
     *         name="max_length",
     *         in="query",
     *         required=false,
     *         description="Maksymalna długość szlaku (w metrach)",
     *         @OA\Schema(type="integer", minimum=0)
     *     ),
     *     @OA\Parameter(
     *         name="rating",
     *         in="query",
     *         required=false,
     *         description="Minimalna ocena szlaku (0-5)",
     *         @OA\Schema(type="number", format="float", minimum=0, maximum=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista szlaków spełniających kryteria",
     *         @OA\JsonContent(ref="#/components/schemas/TrailCollection")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Błędne parametry wyszukiwania",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/trails/{slug}",
     *     summary="Szczegóły szlaku",
     *     description="Zwraca szczegółowe informacje o szlaku kajakowym, włącznie z punktami, przebiegiem rzeki, zdjęciami i powiązanymi regionami.",
     *     operationId="show",
     *     tags={"Trails"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Unikalny slug szlaku",
     *         @OA\Schema(type="string", example="dunajec-nowy-targ-szczawnica")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Szczegóły szlaku",
     *         @OA\JsonContent(ref="#/components/schemas/TrailResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Szlak nie został znaleziony"
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/nearby-trails",
     *     summary="Pobliskie szlaki",
     *     description="Zwraca listę szlaków kajakowych w pobliżu określonej lokalizacji. Można określić lokalizację przez współrzędne geograficzne lub nazwę miejsca.",
     *     operationId="getNearbyTrails",
     *     tags={"Trails"},
     *     @OA\Parameter(
     *         name="lat",
     *         in="query",
     *         required=false,
     *         description="Szerokość geograficzna punktu centralnego",
     *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90)
     *     ),
     *     @OA\Parameter(
     *         name="long",
     *         in="query",
     *         required=false,
     *         description="Długość geograficzna punktu centralnego",
     *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180)
     *     ),
     *     @OA\Parameter(
     *         name="location_name",
     *         in="query",
     *         required=false,
     *         description="Nazwa lokalizacji (alternatywa dla współrzędnych)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista pobliskich szlaków",
     *         @OA\JsonContent(ref="#/components/schemas/NearbyTrailsCollection")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Nieprawidłowe parametry lokalizacji"
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/trails/{slug}/recommended",
     *     summary="Rekomendowane szlaki",
     *     description="Zwraca listę rekomendowanych szlaków na podstawie wybranego szlaku. Szlaki są dobierane na podstawie podobieństwa i bliskości geograficznej.",
     *     operationId="getRecommendedTrails",
     *     tags={"Trails"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Unikalny slug szlaku bazowego",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         required=false,
     *         description="Promień wyszukiwania w kilometrach (1-100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista rekomendowanych szlaków",
     *         @OA\JsonContent(ref="#/components/schemas/RecommendedTrailsCollection")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Szlak bazowy nie został znaleziony"
     *     )
     * )
     */
}
