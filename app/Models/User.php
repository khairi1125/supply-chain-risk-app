<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'last_login' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationship: User has many watchlists
     */
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Helper method to check if user is watching a specific country
     * 
     * @param string $countryCode - 3-letter country code
     * @return bool
     */
    public function isWatching(string $countryCode): bool
    {
        return $this->watchlists()
            ->where('country_code', $countryCode)
            ->exists();
    }

    /**
     * Helper method to get count of watched countries
     * 
     * @return int
     */
    public function watchlistCount(): int
    {
        return $this->watchlists()->count();
    }
}
