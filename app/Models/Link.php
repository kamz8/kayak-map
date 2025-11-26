<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Link",
 *     title="Link",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="url", type="string", example="https://example.com"),
 *     @OA\Property(property="meta_data", type="string", example="{\"title\": \"Example\", \"description\": \"Description\", \"icon\": \"mdi-web\"}"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Link extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'meta_data'];

    public function regions(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Region::class, 'linkable');
    }

    public function trails(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Trail::class, 'linkable');
    }

    public function sections(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Section::class, 'linkable');
    }
}
