<?php

namespace App\Models;

use App\Enums\PointType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function pointType(): BelongsTo
    {
        return $this->belongsTo(PointType::class);
    }

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order');
    }
}
