<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
/**
 * @OA\Schema(
 *     schema="River",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="path", type="object")
 * )
 */
class River extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path'
    ];

}
