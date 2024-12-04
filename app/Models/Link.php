<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Link",
 *     title="Link",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="section_id", type="integer"),
 *     @OA\Property(property="url", type="string"),
 *     @OA\Property(property="meta_data", type="string")
 * )
 */
class Link extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'url', 'meta_data'];

    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function regions(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Region::class, 'linkable');
    }
}
