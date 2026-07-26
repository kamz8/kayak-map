<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @OA\Schema(
 *     schema="Section",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="trail_id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="polygon_coordinates", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="scenery", type="integer"),
 *     @OA\Property(property="difficulty", type="string"),
 *     @OA\Property(property="nuisance", type="string"),
 *     @OA\Property(property="cleanliness", type="string")
 * )
 * @method static find(int $modelId)
 */

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id', 'name', 'description', 'polygon_coordinates', 'scenery','difficulty','nuisance','cleanliness'
    ];

    protected $casts = [
        'polygon_coordinates' => 'array',
        'scenery' => 'integer'
    ];

    protected $attributes = [
        'scenery' => 0
    ];

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function links(): MorphToMany
    {
        return $this->morphToMany(Link::class, 'linkable');
    }

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }
}
