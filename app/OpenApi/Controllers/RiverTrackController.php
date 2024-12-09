<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="River Tracks",
 *     description="Endpointy do pobierania informacji o przebiegu rzek. Zawierają dane geograficzne określające dokładny przebieg koryta rzecznego dla szlaków kajakowych."
 * )
 */
class RiverTrackController
{
    /**
     * @OA\Get(
     *     path="/river-tracks/{id}",
     *     summary="Pobierz dane przebiegu rzeki",
     *     description="Zwraca szczegółowe dane geograficzne opisujące przebieg koryta rzecznego dla wskazanego ID. Dane zawierają serię punktów określających rzeczywisty przebieg rzeki.",
     *     operationId="show",
     *     tags={"River Tracks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Unikalne ID przebiegu rzeki",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pomyślnie zwrócono dane przebiegu rzeki",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID przebiegu rzeki"
     *             ),
     *             @OA\Property(
     *                 property="trail_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID powiązanego szlaku kajakowego"
     *             ),
     *             @OA\Property(
     *                 property="track_points",
     *                 type="object",
     *                 description="Dane geograficzne w formacie GeoJSON określające przebieg rzeki",
     *                 @OA\Property(property="type", type="string", example="LineString"),
     *                 @OA\Property(
     *                     property="coordinates",
     *                     type="array",
     *                     description="Tablica współrzędnych [długość, szerokość]",
     *                     @OA\Items(
     *                         type="array",
     *                         @OA\Items(type="number", format="float"),
     *                         example={20.123456, 49.123456}
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="created_at",
     *                 type="string",
     *                 format="date-time",
     *                 description="Data utworzenia rekordu"
     *             ),
     *             @OA\Property(
     *                 property="updated_at",
     *                 type="string",
     *                 format="date-time",
     *                 description="Data ostatniej aktualizacji rekordu"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nie znaleziono przebiegu rzeki o podanym ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Not Found"
     *             )
     *         )
     *     )
     * )
     */
}
