<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Trail extends Model
{
    use HasFactory;

    protected $fillable = [
        'river_name', 'trail_name', 'description', 'start_lat', 'start_lng', 'end_lat', 'end_lng', 'trail_length', 'author', 'difficulty', 'scenery'
    ];

    protected $casts = [
        'start_lat' => 'float',
        'start_lng' => 'float',
        'end_lat' => 'float',
        'end_lng' => 'float',
        'scenery' => 'integer',
        'difficulty' => Difficulty::class,
    ];

    protected $attributes = [
        'difficulty' => Difficulty::EASY,
        'scenery' => 0
    ];

    public function riverTrack(): HasOne
    {
        return $this->hasOne(RiverTrack::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }

    // Define the difficulty accessor and mutator
    protected function difficulty(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Difficulty::from($value),
            set: fn (Difficulty $value) => $value->value,
        );
    }
}
