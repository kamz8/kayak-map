<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="GPX",
 *     description="Endpointy do obsługi plików GPX - pozwalają na przesyłanie plików GPX dla szlaków kajakowych oraz sprawdzanie statusu ich przetwarzania. Pliki GPX są używane do definiowania dokładnego przebiegu trasy kajakowej."
 * )
 */
class GPXController
{
    /**
     * @OA\Post(
     *     path="/gpx/upload",
     *     summary="Prześlij plik GPX dla szlaku",
     *     description="Pozwala na przesłanie pliku GPX, który definiuje przebieg szlaku kajakowego. Plik jest przetwarzany asynchronicznie, a status przetwarzania można sprawdzić osobnym endpointem.",
     *     operationId="uploadGpx",
     *     tags={"GPX"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Plik GPX oraz ID szlaku",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"gpx_file", "trail_id"},
     *                 @OA\Property(
     *                     property="gpx_file",
     *                     type="string",
     *                     format="binary",
     *                     description="Plik GPX (max 10MB, dozwolone rozszerzenia: .gpx, .xml)"
     *                 ),
     *                 @OA\Property(
     *                     property="trail_id",
     *                     type="integer",
     *                     description="ID szlaku, do którego ma być przypisany plik GPX",
     *                     example=1
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Plik został pomyślnie przesłany i dodany do kolejki przetwarzania",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="GPX file uploaded and queued for processing",
     *                 description="Komunikat potwierdzający przyjęcie pliku"
     *             ),
     *             @OA\Property(
     *                 property="status_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID statusu przetwarzania, które można użyć do sprawdzenia postępu"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Błąd walidacji przesłanych danych",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="gpx_file",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"The gpx file must be a file of type: gpx, xml."}
     *                 ),
     *                 @OA\Property(
     *                     property="trail_id",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"The selected trail id is invalid."}
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    /**
     * @OA\Get(
     *     path="/gpx/status/{statusId}",
     *     summary="Sprawdź status przetwarzania pliku GPX",
     *     description="Zwraca aktualny status przetwarzania przesłanego wcześniej pliku GPX. Pozwala na śledzenie postępu konwersji i przetwarzania pliku.",
     *     operationId="getGpxStatus",
     *     tags={"GPX"},
     *     @OA\Parameter(
     *         name="statusId",
     *         in="path",
     *         required=true,
     *         description="ID statusu przetwarzania otrzymane przy przesyłaniu pliku",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Zwraca aktualny status przetwarzania",
     *         @OA\JsonContent(ref="#/components/schemas/GpxProcessingStatus")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Status o podanym ID nie został znaleziony",
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
