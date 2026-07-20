<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoService
{
    protected $baseUrl = 'https://api.open-meteo.com/v1';

    /**
     * Get current weather by coordinates with caching to database
     */
    public function getWeather($latitude, $longitude)
    {
        // Check cache in database first
        $cached = \DB::table('weather_cache')
            ->where('country_code', $this->getCountryCodeFromCoords($latitude, $longitude))
            ->where('fetched_at', '>=', now()->subHours(4))
            ->first();
            
        if ($cached) {
            return [
                'temperature' => (float) $cached->temperature,
                'rainfall' => (float) $cached->rainfall,
                'wind_speed' => (float) $cached->wind_speed,
                'weather_condition' => $cached->weather_condition,
                'risk_level' => $cached->risk_level,
            ];
        }
        
        try {
            $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/forecast", [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,precipitation,windspeed_10m,weathercode',
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? [];
                
                $temperature = $current['temperature_2m'] ?? 0;
                $rainfall = $current['precipitation'] ?? 0;
                $windSpeed = $current['windspeed_10m'] ?? 0;
                $weatherCode = $current['weathercode'] ?? 0;
                
                $weatherCondition = $this->mapWeatherCode($weatherCode);
                $riskLevel = $this->calculateRiskLevel($windSpeed, $rainfall);
                
                $result = [
                    'temperature' => $temperature,
                    'rainfall' => $rainfall,
                    'wind_speed' => $windSpeed,
                    'weather_condition' => $weatherCondition,
                    'risk_level' => $riskLevel,
                ];
                
                // Save to cache
                $this->cacheWeather($latitude, $longitude, $result);
                
                return $result;
            }

            Log::error('OpenMeteo API failed', ['status' => $response->status()]);
            return $this->getMockWeather();
        } catch (\Exception $e) {
            Log::error('OpenMeteo API Error: ' . $e->getMessage());
            return $this->getMockWeather();
        }
    }
    
    /**
     * Get current weather with country code directly (OPTIMIZED for batch operations)
     * This avoids the expensive getCountryCodeFromCoords() query
     */
    public function getWeatherWithCountryCode($latitude, $longitude, $countryCode)
    {
        // Check cache in database first using provided country code
        $cached = \DB::table('weather_cache')
            ->where('country_code', $countryCode)
            ->where('fetched_at', '>=', now()->subHours(4))
            ->first();
            
        if ($cached) {
            return [
                'temperature' => (float) $cached->temperature,
                'rainfall' => (float) $cached->rainfall,
                'wind_speed' => (float) $cached->wind_speed,
                'weather_condition' => $cached->weather_condition,
                'risk_level' => $cached->risk_level,
            ];
        }
        
        try {
            $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/forecast", [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,precipitation,windspeed_10m,weathercode',
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? [];
                
                $temperature = $current['temperature_2m'] ?? 0;
                $rainfall = $current['precipitation'] ?? 0;
                $windSpeed = $current['windspeed_10m'] ?? 0;
                $weatherCode = $current['weathercode'] ?? 0;
                
                $weatherCondition = $this->mapWeatherCode($weatherCode);
                $riskLevel = $this->calculateRiskLevel($windSpeed, $rainfall);
                
                $result = [
                    'temperature' => $temperature,
                    'rainfall' => $rainfall,
                    'wind_speed' => $windSpeed,
                    'weather_condition' => $weatherCondition,
                    'risk_level' => $riskLevel,
                ];
                
                // Save to cache with provided country code
                $this->cacheWeatherWithCode($countryCode, $result);
                
                return $result;
            }

            Log::error('OpenMeteo API failed', ['status' => $response->status()]);
            return $this->getMockWeather();
        } catch (\Exception $e) {
            Log::error('OpenMeteo API Error: ' . $e->getMessage());
            return $this->getMockWeather();
        }
    }

    /**
     * Get forecast weather by coordinates
     */
    public function getForecast($latitude, $longitude, $days = 7)
    {
        try {
            $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/forecast", [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode',
                'forecast_days' => $days,
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OpenMeteo Forecast API Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Map weather code to human-readable string
     */
    private function mapWeatherCode($code)
    {
        $mapping = [
            0 => 'Clear Sky',
            1 => 'Partly Cloudy',
            2 => 'Partly Cloudy',
            3 => 'Partly Cloudy',
            45 => 'Foggy',
            48 => 'Foggy',
            51 => 'Drizzle',
            53 => 'Drizzle',
            55 => 'Drizzle',
            61 => 'Rain',
            63 => 'Rain',
            65 => 'Rain',
            71 => 'Snow',
            73 => 'Snow',
            75 => 'Snow',
            80 => 'Rain Showers',
            81 => 'Rain Showers',
            82 => 'Rain Showers',
            95 => 'Thunderstorm',
            96 => 'Severe Thunderstorm',
            99 => 'Severe Thunderstorm',
        ];
        
        return $mapping[$code] ?? 'Unknown';
    }
    
    /**
     * Calculate risk level based on weather conditions
     */
    private function calculateRiskLevel($windSpeed, $rainfall)
    {
        if ($windSpeed > 50 || $rainfall > 20) {
            return 'high';
        } elseif ($windSpeed > 25 || $rainfall > 5) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Cache weather data to database
     */
    private function cacheWeather($latitude, $longitude, $data)
    {
        try {
            $countryCode = $this->getCountryCodeFromCoords($latitude, $longitude);
            
            \DB::table('weather_cache')->updateOrInsert(
                ['country_code' => $countryCode],
                [
                    'temperature' => $data['temperature'],
                    'rainfall' => $data['rainfall'],
                    'wind_speed' => $data['wind_speed'],
                    'weather_condition' => $data['weather_condition'],
                    'risk_level' => $data['risk_level'],
                    'fetched_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to cache weather: ' . $e->getMessage());
        }
    }
    
    /**
     * Cache weather data to database with provided country code (OPTIMIZED)
     * This avoids the expensive getCountryCodeFromCoords() query
     */
    private function cacheWeatherWithCode($countryCode, $data)
    {
        try {
            \DB::table('weather_cache')->updateOrInsert(
                ['country_code' => $countryCode],
                [
                    'temperature' => $data['temperature'],
                    'rainfall' => $data['rainfall'],
                    'wind_speed' => $data['wind_speed'],
                    'weather_condition' => $data['weather_condition'],
                    'risk_level' => $data['risk_level'],
                    'fetched_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to cache weather: ' . $e->getMessage());
        }
    }
    
    /**
     * Get country code from coordinates (simplified)
     */
    private function getCountryCodeFromCoords($latitude, $longitude)
    {
        // Try to find closest country from database
        $country = \DB::table('countries')
            ->selectRaw("*, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$latitude, $longitude, $latitude])
            ->orderBy('distance')
            ->first();
            
        return $country ? $country->code : 'UNKNOWN';
    }
    
    /**
     * Get mock weather data for fallback
     */
    private function getMockWeather()
    {
        return [
            'temperature' => 25.0,
            'rainfall' => 0.0,
            'wind_speed' => 10.0,
            'weather_condition' => 'Clear Sky',
            'risk_level' => 'low',
        ];
    }
}
