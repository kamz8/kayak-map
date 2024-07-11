<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id', 'point_type_id', 'name', 'description', 'lat', 'lng'
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float'
    ];

    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }

    public function pointType()
    {
        return $this->belongsTo(PointType::class);
    }

    public function images()
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }
}
