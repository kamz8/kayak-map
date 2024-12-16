<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_nickname',
        'token_expires_at'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime'
    ];

    /**
     * Get the user that owns the social account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
