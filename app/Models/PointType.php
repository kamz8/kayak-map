<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointType extends Model
{
    use HasFactory;

    protected $fillable = ['type'];

    public function points()
    {
        return $this->hasMany(Point::class);
    }

    public function pointDescription()
    {
        return $this->hasMany(PointDescription::class);
    }
}
