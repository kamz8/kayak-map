<?php

namespace App\OpenApi\Requests;

/**
 * @OA\Schema(
 *     schema="GpxUploadRequest",
 *     title="GPX Upload Request",
 *     description="Parametry żądania przesyłania pliku GPX"
 * )
 */
class GpxUploadRequest
{
    /**
     * @OA\Property(
     *     property="gpx_file",
     *     type="string",
     *     format="binary",
     *     description="Plik GPX szlaku (max 10MB, dozwolone rozszerzenia: gpx, xml)"
     * )
     */
    public $gpx_file;

    /**
     * @OA\Property(
     *     property="trail_id",
     *     type="integer",
     *     description="ID szlaku, do którego zostanie przypisany plik GPX",
     *     example=1
     * )
     */
    public $trail_id;
}
