<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id', 'name', 'description', 'polygon_coordinates', 'scenery'
    ];

    protected $casts = [
        'polygon_coordinates' => 'array',
        'scenery' => 'integer'
    ];
    /*warości domyślne*/
    protected $attributes = [
        'scenery' => 0 // wartość domyślna
    ];

    public function trail(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function links(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }
}
