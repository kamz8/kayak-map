<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use LineString;

class RiverTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id', 'track_points'
    ];

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    // Dodaj przestrzenne operacje do modelu
    public $timestamps = true;

    // Konwersja track_points na tablicę punktów (lat, lon)
    public function getTrackPointsAttribute($value): ?array
    {
        if ($value) {
            $lineString = DB::selectOne('SELECT ST_AsText(track_points) AS track_points FROM river_tracks WHERE id = ?', [$this->id])->track_points;

            // Zamiana WKT (LINESTRING) na tablicę punktów
            $points = str_replace(['LINESTRING(', ')'], '', $lineString);
            return array_map(function ($point) {
                [$lon, $lat] = explode(' ', trim($point));
                return ['lat' => $lat, 'lon' => $lon];
            }, explode(',', $points));
        }

        return null;
    }
}
