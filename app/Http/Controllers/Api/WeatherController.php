<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenMeteoService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(OpenMeteoService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get global weather data for all countries
     * GET /api/weather/global
     * 
     * OPTIMIZED VERSION:
     * - Uses database cache with 1-hour validity
     * - Returns cached data immediately (< 1 second)
     * - Only fetches fresh data if cache expired
     */
    public function getGlobalWeather()
    {
        // Fetch whatever is in the cache. The background job will populate it.
        return $this->getWeatherFromCache();
    }

    
    /**
     * Get all weather data from cache only (fast response < 1 second)
     * Cache valid for 4 hours (balanced between freshness and performance)
     */
    private function getWeatherFromCache()
    {
        $cacheHours = 4; // Cache valid for 4 hours
        
        $weatherData = \DB::table('countries as c')
            ->join('weather_cache as wc', 'c.code', '=', 'wc.country_code')
            ->select(
                'c.id as country_id', 'c.name as country_name', 'c.code as country_code',
                'c.region', 'c.flag_url', 'c.latitude', 'c.longitude',
                'wc.temperature', 'wc.rainfall', 'wc.wind_speed',
                'wc.weather_condition', 'wc.risk_level'
            )
            ->where('wc.fetched_at', '>=', now()->subHours($cacheHours))
            ->orderBy('c.name')
            ->get()
            ->map(function($item) {
                return [
                    'country_id' => $item->country_id,
                    'country_name' => $item->country_name,
                    'country_code' => $item->country_code,
                    'region' => $item->region,
                    'flag_url' => $item->flag_url,
                    'latitude' => (float) $item->latitude,
                    'longitude' => (float) $item->longitude,
                    'temperature' => (float) $item->temperature,
                    'rainfall' => (float) $item->rainfall,
                    'wind_speed' => (float) $item->wind_speed,
                    'weather_condition' => $item->weather_condition,
                    'risk_level' => $item->risk_level,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $weatherData,
            'total' => $weatherData->count(),
            'from_cache' => $weatherData->count(),
            'from_api' => 0,
            'message' => "Loaded {$weatherData->count()} countries from cache (< {$cacheHours} hours old)",
            'cache_age' => "Data cached for {$cacheHours} hours",
        ]);
    }

    /**
     * Get weather by coordinates
     * GET /api/weather/{lat}/{lon}
     */
    public function getWeatherByCoordinates($lat, $lon)
    {
        try {
            $weather = $this->weatherService->getWeather($lat, $lon);

            return response()->json([
                'success' => true,
                'data' => [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'temperature' => $weather['temperature'],
                    'rainfall' => $weather['rainfall'],
                    'wind_speed' => $weather['wind_speed'],
                    'weather_condition' => $weather['weather_condition'],
                    'risk_level' => $weather['risk_level'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weather data: ' . $e->getMessage()
            ], 500);
        }
    }
}
