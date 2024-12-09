<?php
namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="LinkResource",
 *     title="Link Resource",
 *     description="Resource containing link data"
 * )
 */
class LinkResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     example=1
     * )
     */
    private $id;

    /**
     * @OA\Property(
     *     property="url",
     *     type="string",
     *     example="https://example.com"
     * )
     */
    private $url;

    /**
     * @OA\Property(
     *     property="meta_data",
     *     type="object",
     *     example={"title": "Example Link", "description": "Link description"}
     * )
     */
    private $meta_data;

    /**
     * @OA\Property(
     *     property="section_id",
     *     type="integer",
     *     example=1
     * )
     */
    private $section_id;

    /**
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time"
     * )
     */
    private $created_at;

    /**
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time"
     * )
     */
    private $updated_at;
}
