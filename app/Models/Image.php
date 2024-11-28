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
        return $this->morphedByMany(Trail::class, 'imageable')
            ->using(Imageable::class)
            ->withPivot('is_main', 'order', 'vattr');
    }

    public function sections()
    {
        return $this->morphedByMany(Section::class, 'imageable')
            ->using(Imageable::class)
            ->withPivot('is_main', 'order', 'vattr');
    }

    public function points()
    {
        return $this->morphedByMany(Point::class, 'imageable')
            ->using(Imageable::class)
            ->withPivot('is_main', 'order', 'vattr');
    }

    public function regions()
    {
        return $this->morphedByMany(Region::class, 'imageable')
            ->using(Imageable::class)
            ->withPivot('is_main', 'order', 'vattr')
            ->withTimestamps();
    }

    public function imageable()
    {
        return $this->morphTo();
    }
}
