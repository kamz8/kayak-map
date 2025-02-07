<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Imageable",
 *     title="Imageable",
 *     @OA\Property(property="image_id", type="integer"),
 *     @OA\Property(property="imageable_id", type="integer"),
 *     @OA\Property(property="imageable_type", type="string"),
 *     @OA\Property(property="is_main", type="boolean"),
 *     @OA\Property(property="order", type="integer"),
 *     @OA\Property(property="vattr", type="object")
 * )
 */
class Imageable extends Model
{
    use HasFactory;

    protected $table = 'imageables';

    protected $fillable = [
        'image_id',
        'imageable_id',
        'imageable_type',
        'is_main',
        'order',
        'vattr',
    ];

    protected $casts = [
        'vattr' => 'array', // Laravel automatycznie przekształci `vattr` na JSON
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
