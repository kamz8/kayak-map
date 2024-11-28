<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class RiverTrack extends Model
{
    use HasFactory, HasSpatial;

    protected $fillable = [
        'trail_id', 'track_points'
    ];

    protected $casts = [
        'track_points' => LineString::class
    ];

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }


    public $timestamps = true;

}
