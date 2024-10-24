<?php

namespace App\Models;

namespace App\Models;

use App\Enums\RegionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

/**
 * @method static create(array $array)
 * @method static where(string $string, string $slug)
 * @property mixed $trails
 */
class Region extends Model
{
    use HasFactory, HasSpatial;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'parent_id',
        'is_root',
        'center_point',
        'area'
    ];

    protected $casts = [
        'is_root' => 'boolean',
        'center_point' => Point::class,
        'area' => Polygon::class,
        'type' => RegionType::class, // Dodaj to pole
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function trails(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Trail::class, 'trail_region');
    }


    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => str_slug($value),
        );
    }
}
