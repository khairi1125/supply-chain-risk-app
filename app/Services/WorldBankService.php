<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WorldBankService
{
    protected $baseUrl = 'https://api.worldbank.org/v2';

    /**
     * Get GDP data for a country
     */
    public function getGDP($countryCode, $year = null)
    {
        $year = $year ?? date('Y') - 1; // Previous year
        $cacheKey = "worldbank_gdp_{$countryCode}_{$year}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode, $year) {
            try {
                $response = Http::get("{$this->baseUrl}/country/{$countryCode}/indicator/NY.GDP.MKTP.CD", [
                    'format' => 'json',
                    'date' => $year,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data[1] ?? null;
                }

                return null;
            } catch (\Exception $e) {
                \Log::error('World Bank GDP API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get inflation data for a country
     */
    public function getInflation($countryCode, $year = null)
    {
        $year = $year ?? date('Y') - 1;
        $cacheKey = "worldbank_inflation_{$countryCode}_{$year}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode, $year) {
            try {
                $response = Http::get("{$this->baseUrl}/country/{$countryCode}/indicator/FP.CPI.TOTL.ZG", [
                    'format' => 'json',
                    'date' => $year,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data[1] ?? null;
                }

                return null;
            } catch (\Exception $e) {
                \Log::error('World Bank Inflation API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get population data for a country
     */
    public function getPopulation($countryCode, $year = null)
    {
        $year = $year ?? date('Y') - 1;
        $cacheKey = "worldbank_population_{$countryCode}_{$year}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode, $year) {
            try {
                $response = Http::get("{$this->baseUrl}/country/{$countryCode}/indicator/SP.POP.TOTL", [
                    'format' => 'json',
                    'date' => $year,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data[1] ?? null;
                }

                return null;
            } catch (\Exception $e) {
                \Log::error('World Bank Population API Error: ' . $e->getMessage());
                return null;
            }
        });
    }
}
