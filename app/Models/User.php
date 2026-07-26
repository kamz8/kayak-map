<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

/**
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time")
 * )
 */
class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes, HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'bio',
        'location',
        'birth_date',
        'gender',
        'preferences',
        'notification_settings',
        'is_admin',
        'is_active',
    ];

    protected $guarded = [
        'id',
        'email_notifications_enabled',
        'notifications_enabled',
        'preferred_language',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => \App\Casts\UserPreferences::class,
        'notification_settings' => \App\Casts\UserPreferences::class,
        'birth_date' => 'date',
    ];

    // Relation
    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order')->orderBy('order');
    }

    // Avatar - pierwszy obraz oznaczony jako główny przez pivot table
    public function avatar()
    {
        return $this->morphToMany(Image::class, 'imageable')
            ->wherePivot('is_main', true)
            ->withPivot(['is_main', 'order'])
            ->withTimestamps()
            ->orderBy('order')
            ->limit(1);
    }


    public function socialAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function devices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    // Metody JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        $clientType = request()->header('X-Client-Type');

        // Sprawdzamy czy przekazany typ klienta jest dozwolony
        if (!in_array($clientType, config('auth.clients.types'))) {
            $clientType = config('auth.clients.default');
        }

        return [
            'sub' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'iat' => time(),
            'exp' => time() + (60*60),
            'iss' => config('app.url'),
            'type' => 'access_token',
            'client_type' => $clientType,
            'ip' => request()->ip(),
        ];
    }

    /**
     * Determine if the user is a Super Admin.
     * Super Admin bypasses all permission checks.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    /**
     * Get user status based on various conditions
     */
    public function getStatus(): string
    {
        if ($this->deleted_at) {
            return 'deleted';
        }

        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->email_verified_at) {
            return 'unverified';
        }

        return 'active';
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
