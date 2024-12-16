<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

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
    use SoftDeletes, HasFactory, Notifiable;

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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
        'notification_settings' => 'array',
        'birth_date' => 'date',
    ];

    // Relation
    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot('is_main', 'order')->orderBy('order');
    }

    // Avatar - pierwszy obraz oznaczony jako główny
    public function avatar(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')
            ->where('is_main', true);
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
}
