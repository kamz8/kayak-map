<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imageable extends Model
{
    use HasFactory;

    protected $table = 'imageables';

    protected $fillable = [
        'image_id',
        'imageable_id',
        'imageable_type',
        'is_main',
        'order',
        'vattr',
    ];

    protected $casts = [
        'vattr' => 'array', // Laravel automatycznie przekształci `vattr` na JSON
    ];

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
