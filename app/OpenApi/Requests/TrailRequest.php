<?php

namespace App\OpenApi\Requests;

/**
 * @OA\Schema(
 *     schema="TrailRequest",
 *     title="Trail Request",
 *     description="Parametry żądania filtrowania szlaków"
 * )
 */
class TrailRequest
{
    /**
     * @OA\Property(
     *     property="difficulty",
     *     type="array",
     *     description="Filtry poziomu trudności szlaku",
     *     @OA\Items(
     *         type="string",
     *         enum={"łatwy", "umiarkowany", "trudny"}
     *     ),
     *     example={"łatwy", "umiarkowany"}
     * )
     */
    public $difficulty;

    /**
     * @OA\Property(
     *     property="scenery",
     *     type="integer",
     *     description="Minimalna ocena krajobrazów",
     *     minimum=0,
     *     maximum=10,
     *     example=7
     * )
     */
    public $scenery;

    /**
     * @OA\Property(
     *     property="start_lat",
     *     type="number",
     *     format="float",
     *     description="Szerokość geograficzna początku obszaru wyszukiwania",
     *     minimum=-90,
     *     maximum=90,
     *     example=49.5
     * )
     */
    public $start_lat;

    /**
     * @OA\Property(
     *     property="end_lat",
     *     type="number",
     *     format="float",
     *     description="Szerokość geograficzna końca obszaru wyszukiwania",
     *     minimum=-90,
     *     maximum=90,
     *     example=50.5
     * )
     */
    public $end_lat;

    /**
     * @OA\Property(
     *     property="start_lng",
     *     type="number",
     *     format="float",
     *     description="Długość geograficzna początku obszaru wyszukiwania",
     *     minimum=-180,
     *     maximum=180,
     *     example=19.0
     * )
     */
    public $start_lng;

    /**
     * @OA\Property(
     *     property="end_lng",
     *     type="number",
     *     format="float",
     *     description="Długość geograficzna końca obszaru wyszukiwania",
     *     minimum=-180,
     *     maximum=180,
     *     example=20.0
     * )
     */
    public $end_lng;

    /**
     * @OA\Property(
     *     property="search_query",
     *     type="string",
     *     description="Fraza wyszukiwania dla szlaków",
     *     maxLength=255,
     *     example="Dunajec"
     * )
     */
    public $search_query;

    /**
     * @OA\Property(
     *     property="min_length",
     *     type="integer",
     *     description="Minimalna długość szlaku w metrach",
     *     minimum=0,
     *     example=5000
     * )
     */
    public $min_length;

    /**
     * @OA\Property(
     *     property="max_length",
     *     type="integer",
     *     description="Maksymalna długość szlaku w metrach",
     *     minimum=0,
     *     example=20000
     * )
     */
    public $max_length;

    /**
     * @OA\Property(
     *     property="rating",
     *     type="number",
     *     format="float",
     *     description="Minimalna ocena szlaku",
     *     minimum=0,
     *     maximum=5,
     *     example=4.0
     * )
     */
    public $rating;
}
