<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Point extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id',
        'point_type_id',
        'at_length',
        'name',
        'description',
        'lat',
        'lng',
        'order'
    ];
/*New Laravel 11 casting method*/
    protected function casts()
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'at_length' => 'float',
            'order' => 'integer'
        ];
    }

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

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeAtLength($query, $length)
    {
        return $query->where('at_length', '<=', $length);
    }
}
