<?php

namespace App\Models;

namespace App\Models;

use App\Enums\RegionType;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

/**
 * @method maps create(array $array)
 * @method maps where(string $string, string $slug)
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

    protected static function booted(): void
    {
        static::addGlobalScope('with-parents', function ($builder) {
            $builder->with('parent');
        });
    }

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

    public function ancestors(): \Illuminate\Database\Eloquent\Relations\BelongsTo|\Illuminate\Database\Eloquent\Builder
    {
        return $this->parent()->with('ancestors');
    }

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order')->orderBy('order');
    }


    public function links(): MorphToMany
    {
        return $this->morphToMany(Link::class, 'linkable');
    }

    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => str_slug($value),
        );
    }

    public function scopeSearch(Builder $query, string $searchTerm): Builder
    {
        return $query->where('name', 'like', "%{$searchTerm}%");
    }

    // Define a local scope for loading full ancestry
    public function scopeWithAncestors($query)
    {
        return $query->with(['parent.parent.parent']); // Adjust depth as needed
    }

    // Define a local scope for loading full tree
    public function scopeWithFullTree($query)
    {
        return $query->with(['parent.parent.parent', 'children.children.children']); // Adjust depth as needed
    }

}
