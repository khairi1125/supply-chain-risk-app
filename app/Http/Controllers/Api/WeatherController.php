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
     * Get global weather data for major cities
     * GET /api/weather/global
     */
    public function getGlobalWeather()
    {
        // Major cities coordinates for global weather monitoring
        $cities = [
            ['name' => 'New York', 'country' => 'USA', 'lat' => 40.7128, 'lon' => -74.0060],
            ['name' => 'London', 'country' => 'UK', 'lat' => 51.5074, 'lon' => -0.1278],
            ['name' => 'Tokyo', 'country' => 'Japan', 'lat' => 35.6762, 'lon' => 139.6503],
            ['name' => 'Singapore', 'country' => 'Singapore', 'lat' => 1.3521, 'lon' => 103.8198],
            ['name' => 'Dubai', 'country' => 'UAE', 'lat' => 25.2048, 'lon' => 55.2708],
            ['name' => 'Shanghai', 'country' => 'China', 'lat' => 31.2304, 'lon' => 121.4737],
            ['name' => 'Mumbai', 'country' => 'India', 'lat' => 19.0760, 'lon' => 72.8777],
            ['name' => 'São Paulo', 'country' => 'Brazil', 'lat' => -23.5505, 'lon' => -46.6333],
            ['name' => 'Sydney', 'country' => 'Australia', 'lat' => -33.8688, 'lon' => 151.2093],
            ['name' => 'Jakarta', 'country' => 'Indonesia', 'lat' => -6.2088, 'lon' => 106.8456],
            ['name' => 'Los Angeles', 'country' => 'USA', 'lat' => 34.0522, 'lon' => -118.2437],
            ['name' => 'Paris', 'country' => 'France', 'lat' => 48.8566, 'lon' => 2.3522],
            ['name' => 'Hong Kong', 'country' => 'China', 'lat' => 22.3193, 'lon' => 114.1694],
            ['name' => 'Seoul', 'country' => 'South Korea', 'lat' => 37.5665, 'lon' => 126.9780],
            ['name' => 'Bangkok', 'country' => 'Thailand', 'lat' => 13.7563, 'lon' => 100.5018],
            ['name' => 'Istanbul', 'country' => 'Turkey', 'lat' => 41.0082, 'lon' => 28.9784],
            ['name' => 'Moscow', 'country' => 'Russia', 'lat' => 55.7558, 'lon' => 37.6173],
            ['name' => 'Mexico City', 'country' => 'Mexico', 'lat' => 19.4326, 'lon' => -99.1332],
            ['name' => 'Cairo', 'country' => 'Egypt', 'lat' => 30.0444, 'lon' => 31.2357],
            ['name' => 'Lagos', 'country' => 'Nigeria', 'lat' => 6.5244, 'lon' => 3.3792],
        ];

        $weatherData = [];

        foreach ($cities as $city) {
            try {
                $weather = $this->weatherService->getWeather($city['lat'], $city['lon']);
                
                $weatherData[] = [
                    'city' => $city['name'],
                    'country' => $city['country'],
                    'latitude' => $city['lat'],
                    'longitude' => $city['lon'],
                    'temperature' => $weather['temperature'],
                    'rainfall' => $weather['rainfall'],
                    'wind_speed' => $weather['wind_speed'],
                    'weather_condition' => $weather['weather_condition'],
                    'risk_level' => $weather['risk_level'],
                ];
            } catch (\Exception $e) {
                \Log::error("Failed to fetch weather for {$city['name']}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'data' => $weatherData,
            'total' => count($weatherData),
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
