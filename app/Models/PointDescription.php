<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointDescription extends Model
{
    protected $table = 'point_description';

    protected $fillable = [
        'point_id',
        'point_type',
        'description',
    ];

    /**
     * Get the point that owns the description.
     */
    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'point_id');
    }

    /**
     * Get the point type associated with this description.
     */
    public function pointType(): BelongsTo
    {
        return $this->belongsTo(PointType::class, 'point_type');
    }
}
