<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OpenMeteoService
{
    protected $baseUrl = 'https://api.open-meteo.com/v1';

    /**
     * Get current weather by coordinates
     */
    public function getCurrentWeather($latitude, $longitude)
    {
        $cacheKey = "weather_{$latitude}_{$longitude}";
        
        return Cache::remember($cacheKey, 1800, function () use ($latitude, $longitude) {
            try {
                $response = Http::get("{$this->baseUrl}/forecast", [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,relative_humidity_2m,precipitation,weather_code,wind_speed_10m',
                    'timezone' => 'auto'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                \Log::error('OpenMeteo API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get forecast weather by coordinates
     */
    public function getForecast($latitude, $longitude, $days = 7)
    {
        try {
            $response = Http::get("{$this->baseUrl}/forecast", [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,weather_code',
                'forecast_days' => $days,
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('OpenMeteo Forecast API Error: ' . $e->getMessage());
            return null;
        }
    }
}
