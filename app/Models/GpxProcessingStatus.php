<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="GpxProcessingStatus",
 *     title="GPX Processing Status",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="trail_id", type="integer"),
 *     @OA\Property(property="file_path", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="processed_at", type="string", format="date-time")
 * )
 */
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
