<?php
namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="TrailCollection",
 *     title="Kolekcja szlaków",
 *     description="Kolekcja zawierająca listę szlaków wraz z metadanymi"
 * )
 */
class TrailCollection
{
    /**
     * @OA\Property(
     *     property="data",
     *
     *
     * )
     */
    public $data;

    /**
     * @OA\Property(
     *     property="meta",
     *     type="object",
     *     @OA\Property(
     *         property="total_trails",
     *         type="integer",
     *         example=10
     *     ),
     *     @OA\Property(
     *         property="bounding_box",
     *         type="object",
     *         @OA\Property(
     *             property="start_lat",
     *             type="number",
     *             format="float",
     *             example=50.123
     *         ),
     *         @OA\Property(
     *             property="end_lat",
     *             type="number",
     *             format="float",
     *             example=51.123
     *         ),
     *         @OA\Property(
     *             property="start_lng",
     *             type="number",
     *             format="float",
     *             example=19.123
     *         ),
     *         @OA\Property(
     *             property="end_lng",
     *             type="number",
     *             format="float",
     *             example=20.123
     *         )
     *     ),
     *     @OA\Property(
     *         property="main_region",
     *         type="object",
     *         nullable=true,
     *         @OA\Property(
     *             property="name",
     *             type="string",
     *             example="Małopolska"
     *         ),
     *         @OA\Property(
     *             property="type",
     *             type="string",
     *             example="state"
     *         ),
     *         @OA\Property(
     *             property="full_slug",
     *             type="string",
     *             example="poland/malopolska"
     *         ),
     *         @OA\Property(
     *             property="path",
     *             type="array",
     *             @OA\Items(
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    public $meta;
}
