<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $trail_id
 * @property false|mixed|string $track_points
 */
class RiverTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id', 'track_points'
    ];

    protected $casts = [
        'track_points' => 'array'
    ];

    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }
}
