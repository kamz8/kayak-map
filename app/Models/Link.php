<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'url', 'meta_data'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
