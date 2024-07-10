<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trail extends Model
{
    use HasFactory;

    protected $fillable = [
        'river_name', 'trail_name', 'description', 'start_lat', 'start_lng', 'end_lat', 'end_lng', 'trail_length', 'author'
    ];

    protected $casts = [
        'start_lat' => 'float',
        'start_lng' => 'float',
        'end_lat' => 'float',
        'end_lng' => 'float'
    ];

    public function riverTrack()
    {
        return $this->hasOne(RiverTrack::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function points()
    {
        return $this->hasMany(Point::class);
    }

    public function images()
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }
}
