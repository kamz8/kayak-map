<?php
Namespace App\OpenApi\Models;
class Region
{
    /**
     * @OA\Property(property="id", type="integer")
     * @OA\Property(property="name", type="string")
     * @OA\Property(property="slug", type="string")
     * @OA\Property(property="type", type="string", enum={"country", "state", "city", "geographic_area"})
     * @OA\Property(property="parent_id", type="integer", nullable=true)
     * @OA\Property(property="is_root", type="boolean")
     * @OA\Property(
     *     property="center_point",
     *     type="object",
     *     @OA\Property(property="type", type="string", example="Point"),
     *     @OA\Property(
     *         property="coordinates",
     *         type="array",
     *         @OA\Items(type="number", format="float"),
     *         example={19.145136, 51.919438}
     *     )
     * )
     * @OA\Property(
     *     property="area",
     *     type="object",
     *     @OA\Property(property="type", type="string", example="Polygon"),
     *     @OA\Property(
     *         property="coordinates",
     *         type="array",
     *         @OA\Items(
     *             type="array",
     *             @OA\Items(
     *                 type="array",
     *                 @OA\Items(type="number", format="float")
     *             )
     *         )
     *     )
     * )
     */
    public $properties;
}
