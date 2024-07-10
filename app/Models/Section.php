<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = [
        'trail_id', 'name', 'description', 'polygon_coordinates'
    ];

    protected $casts = [
        'polygon_coordinates' => 'array'
    ];

    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }

    public function links()
    {
        return $this->hasMany(Link::class);
    }

    public function images()
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }
}
