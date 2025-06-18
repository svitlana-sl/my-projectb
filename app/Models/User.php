<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasFileUpload;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasFileUpload;

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Clean up files when user is deleted
        static::deleting(function ($user) {
            $user->deleteOldAvatar();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'avatar_url',
        'avatar_path',
        'avatar_thumb_path',
        'address_line',
        'city',
        'postal_code',
        'country',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    // for Filament
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->is_admin;
    }

    /**
     * Get the pets owned by the user.
     */
    public function pets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pet::class, 'owner_id');
    }

    /**
     * Get the sitter profile for the user.
     */
    public function sitterProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SitterProfile::class);
    }

    /**
     * Get the sitter services offered by the user.
     */
    public function sitterServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SitterService::class, 'sitter_id');
    }

    /**
     * Get the favorites where this user is the owner.
     */
    public function favoritesSitters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Favorite::class, 'owner_id');
    }

    /**
     * Get the favorites where this user is the sitter.
     */
    public function favoritedBy(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Favorite::class, 'sitter_id');
    }

    /**
     * Get the ratings given by this user.
     */
    public function ratingsGiven(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rating::class, 'owner_id');
    }

    /**
     * Get the ratings received by this user.
     */
    public function ratingsReceived(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rating::class, 'sitter_id');
    }

    /**
     * Get the service requests made by this user.
     */
    public function serviceRequestsMade(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'owner_id');
    }

    /**
     * Get the service requests received by this user.
     */
    public function serviceRequestsReceived(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'sitter_id');
    }
    
    /**
     * Get avatar URL (prioritize uploaded file over URL)
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_thumb_path) {
            return $this->getFileUrl($this->avatar_thumb_path);
        }
        
        if ($this->avatar_path) {
            return $this->getFileUrl($this->avatar_path);
        }
        
        if ($this->attributes['avatar_url']) {
            return $this->attributes['avatar_url'];
        }
        
        return $this->getDefaultImage();
    }
    
    /**
     * Get avatar URL for display (separate method to avoid conflicts)
     */
    public function getDisplayAvatarUrl(): string
    {
        return $this->getAvatarUrlAttribute();
    }
    

    
    /**
     * Delete old avatar files
     */
    public function deleteOldAvatar(): void
    {
        $this->deleteFiles([
            $this->avatar_path,
            $this->avatar_thumb_path
        ]);
    }
}
