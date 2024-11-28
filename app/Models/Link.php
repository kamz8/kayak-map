<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Link extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'meta_data'];

    public function regions(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Region::class, 'linkable');
    }

    public function trail(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Trail::class, 'linkable');
    }
}
