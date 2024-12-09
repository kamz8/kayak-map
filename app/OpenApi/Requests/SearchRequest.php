<?php

namespace App\OpenApi\Requests;

/**
 * @OA\Schema(
 *     schema="SearchRequest",
 *     title="Search Request",
 *     description="Parametry żądania wyszukiwania"
 * )
 */
class SearchRequest
{
    /**
     * @OA\Property(
     *     property="query",
     *     type="string",
     *     description="Fraza wyszukiwania",
     *     minLength=2,
     *     maxLength=100,
     *     example="Dunajec"
     * )
     */
    public $query;

    /**
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="Typ wyszukiwanych elementów",
     *     enum={"all", "trail", "country", "state", "city"},
     *     default="all"
     * )
     */
    public $type;

    /**
     * @OA\Property(
     *     property="limit",
     *     type="integer",
     *     description="Limit wyników wyszukiwania",
     *     minimum=1,
     *     maximum=100,
     *     default=50,
     *     example=20
     * )
     */
    public $limit;
}
