<?php
namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="NearbyTrailsCollection",
 *     title="Kolekcja pobliskich szlaków",
 *     description="Kolekcja zawierająca listę pobliskich szlaków"
 * )
 */
class NearbyTrailsCollection
{
    /**
     * @OA\Property(
     *     property="status",
     *     type="string",
     *     example="success"
     * )
     */
    private $status;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/NearbyTrailResource")
     * )
     */
    private $data;

    /**
     * @OA\Property(
     *     property="message",
     *     type="string",
     *     example="Top 10 nearby trails retrieved successfully."
     * )
     */
    private $message;

    /**
     * @OA\Property(
     *     property="total_trails",
     *     type="integer",
     *     example=10
     * )
     */
    private $total_trails;
}
