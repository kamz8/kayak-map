<?php

namespace App\OpenApi\Resources;

/**
 * @OA\Schema(
 *     schema="ImageResource",
 *     title="Image Resource",
 *     description="Resource containing image data"
 * )
 */
class ImageResource
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
     *     property="path",
     *     type="string",
     *     example="/storage/images/trail1.jpg"
     * )
     */
    private $path;

    /**
     * @OA\Property(
     *     property="is_main",
     *     type="boolean",
     *     example=false
     * )
     */
    private $is_main;

    /**
     * @OA\Property(
     *     property="order",
     *     type="integer",
     *     nullable=true,
     *     example=1
     * )
     */
    private $order;

    /**
     * @OA\Property(
     *     property="vattr",
     *     type="object",
     *     nullable=true,
     *     @OA\Property(property="caption", type="string", example="Beautiful view"),
     *     @OA\Property(property="alt", type="string", example="Mountain landscape")
     * )
     */
    private $vattr;
}


