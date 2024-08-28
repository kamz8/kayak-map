<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }
}
