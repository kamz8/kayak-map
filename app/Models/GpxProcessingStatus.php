<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpxProcessingStatus extends Model
{
    protected $fillable = [
        'trail_id',
        'file_path',
        'status',
        'message',
        'processed_at'
    ];

    protected $casts = [
        'processed_at' => 'datetime'
    ];

    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }
}
