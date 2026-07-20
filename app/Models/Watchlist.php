<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Watchlist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'country_code',
        'country_name',
        'priority',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Watchlist belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: Get country details from countries table
     * 
     * @return object|null
     */
    public function getCountryAttribute()
    {
        return DB::table('countries')
            ->where('code', $this->country_code)
            ->first();
    }

    /**
     * Accessor: Get current risk score from risk_scores table
     * 
     * @return object|null
     */
    public function getRiskScoreAttribute()
    {
        return DB::table('risk_scores')
            ->where('country_code', $this->country_code)
            ->first();
    }

    /**
     * Accessor: Get weather data from weather_cache table
     * Only returns data that's less than 1 hour old
     * 
     * @return object|null
     */
    public function getWeatherAttribute()
    {
        return DB::table('weather_cache')
            ->where('country_code', $this->country_code)
            ->where('fetched_at', '>=', now()->subHour())
            ->first();
    }
}
