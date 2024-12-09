<?php

namespace App\OpenApi\Controllers;

/**
 * @OA\Tag(
 *     name="GPX",
 *     description="Endpointy do obsługi plików GPX"
 * )
 */
class GPXController
{
    /**
     * @OA\Post(
     *     path="/gpx/upload",
     *     summary="Upload pliku GPX",
     *     tags={"GPX"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="gpx_file",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="trail_id",
     *                     type="integer"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully"
     *     )
     * )
     */
    public function upload() {}

    /**
     * @OA\Get(
     *     path="/gpx/status/{statusId}",
     *     summary="Status przetwarzania GPX",
     *     tags={"GPX"},
     *     @OA\Parameter(
     *         name="statusId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Processing status"
     *     )
     * )
     */
    public function status() {}
}
