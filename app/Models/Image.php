<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['path'];

    public function trails()
    {
        return $this->morphedByMany(Trail::class, 'imageable')->withPivot('is_main', 'order');
    }

    public function sections()
    {
        return $this->morphedByMany(Section::class, 'imageable')->withPivot('is_main', 'order');
    }

    public function points()
    {
        return $this->morphedByMany(Point::class, 'imageable')->withPivot('is_main', 'order');
    }
}
