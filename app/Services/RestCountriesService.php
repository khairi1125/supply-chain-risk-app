<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RestCountriesService
{
    protected $baseUrl = 'https://restcountries.com/v3.1';

    /**
     * Get all countries
     */
    public function getAllCountries()
    {
        $cacheKey = 'rest_countries_all';
        
        return Cache::remember($cacheKey, 86400, function () {
            try {
                $response = Http::get("{$this->baseUrl}/all", [
                    'fields' => 'name,cca2,cca3,capital,region,subregion,population,area,flags,latlng,currencies,timezones'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return [];
            } catch (\Exception $e) {
                \Log::error('REST Countries API Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get country by code
     */
    public function getCountryByCode($code)
    {
        $cacheKey = "rest_country_{$code}";
        
        return Cache::remember($cacheKey, 86400, function () use ($code) {
            try {
                $response = Http::get("{$this->baseUrl}/alpha/{$code}");

                if ($response->successful()) {
                    $data = $response->json();
                    return $data[0] ?? null;
                }

                return null;
            } catch (\Exception $e) {
                \Log::error('REST Countries API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get countries by region
     */
    public function getCountriesByRegion($region)
    {
        $cacheKey = "rest_countries_region_{$region}";
        
        return Cache::remember($cacheKey, 86400, function () use ($region) {
            try {
                $response = Http::get("{$this->baseUrl}/region/{$region}");

                if ($response->successful()) {
                    return $response->json();
                }

                return [];
            } catch (\Exception $e) {
                \Log::error('REST Countries API Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Search countries by name
     */
    public function searchCountries($name)
    {
        try {
            $response = Http::get("{$this->baseUrl}/name/{$name}");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            \Log::error('REST Countries Search API Error: ' . $e->getMessage());
            return [];
        }
    }
}
