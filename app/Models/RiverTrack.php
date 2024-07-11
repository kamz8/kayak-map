<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiverTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id', 'track_points'
    ];

    protected $casts = [
        'track_points' => 'array'
    ];

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }
}
