<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RestCountriesService
{
    protected $baseUrl = 'https://raw.githubusercontent.com/mledoze/countries/master';

    /**
     * Get all countries from GitHub mledoze dataset
     */
    public function getAllCountries()
    {
        $cacheKey = 'rest_countries_all';
        
        return Cache::remember($cacheKey, 86400, function () {
            try {
                // Use GitHub dataset which is more reliable than deprecated REST Countries API
                $response = Http::withoutVerifying()->timeout(30)->get("{$this->baseUrl}/countries.json");

                if ($response->successful()) {
                    $countries = $response->json();
                    
                    // Check if response is valid array
                    if (!is_array($countries) || empty($countries)) {
                        Log::error('Countries API returned invalid data');
                        Log::warning('Using fallback country data');
                        return $this->getFallbackCountries();
                    }
                    
                    // Transform data to required format
                    return collect($countries)->map(function ($country) {
                        // Extract currencies - structure: {"USD": {"name": "...", "symbol": "..."}}
                        $currencies = $country['currencies'] ?? [];
                        $firstCurrencyCode = !empty($currencies) ? array_key_first($currencies) : null;
                        $firstCurrencyData = $firstCurrencyCode ? ($currencies[$firstCurrencyCode] ?? []) : [];
                        
                        // Generate flag URL from cca2 code (2-letter country code)
                        // Format: https://flagcdn.com/w320/{cca2}.png
                        $cca2 = $country['cca2'] ?? null;
                        $flagUrl = $cca2 ? 'https://flagcdn.com/w320/' . strtolower($cca2) . '.png' : null;
                        
                        return [
                            'name' => $country['name']['common'] ?? 'Unknown',
                            'code' => $country['cca3'] ?? null,
                            'region' => $country['region'] ?? 'Unknown',
                            'currency_code' => $firstCurrencyCode,
                            'currency_name' => $firstCurrencyData['name'] ?? null,
                            'flag_url' => $flagUrl,
                            'latitude' => isset($country['latlng'][0]) ? $country['latlng'][0] : 0,
                            'longitude' => isset($country['latlng'][1]) ? $country['latlng'][1] : 0,
                        ];
                    })->filter(function ($country) {
                        return !empty($country['code']); // Only countries with valid code
                    })->values()->toArray();
                }

                Log::error('Countries API failed', ['status' => $response->status()]);
                Log::warning('Using fallback country data');
                return $this->getFallbackCountries();
            } catch (\Exception $e) {
                Log::error('Countries API Error: ' . $e->getMessage());
                Log::warning('Using fallback country data');
                return $this->getFallbackCountries();
            }
        });
    }

    /**
     * Get country by code
     */
    public function getCountryByCode($code)
    {
        // First try to get from database
        $country = \DB::table('countries')->where('code', $code)->first();
        
        if ($country) {
            return [
                'name' => $country->name,
                'code' => $country->code,
                'region' => $country->region,
                'currency_code' => $country->currency_code,
                'currency_name' => $country->currency_name,
                'flag_url' => $country->flag_url,
                'latitude' => $country->latitude,
                'longitude' => $country->longitude,
            ];
        }
        
        $cacheKey = "rest_country_{$code}";
        
        return Cache::remember($cacheKey, 86400, function () use ($code) {
            try {
                $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/alpha/{$code}");

                if ($response->successful()) {
                    $data = $response->json();
                    $country = is_array($data) && isset($data[0]) ? $data[0] : $data;
                    
                    if (!$country) {
                        return null;
                    }
                    
                    $currencies = $country['currencies'] ?? [];
                    $firstCurrency = !empty($currencies) ? array_key_first($currencies) : null;
                    
                    return [
                        'name' => $country['name']['common'] ?? 'Unknown',
                        'code' => $country['cca3'] ?? null,
                        'region' => $country['region'] ?? 'Unknown',
                        'subregion' => $country['subregion'] ?? null,
                        'currency_code' => $firstCurrency,
                        'currency_name' => $firstCurrency ? ($currencies[$firstCurrency]['name'] ?? null) : null,
                        'flag_url' => $country['flags']['png'] ?? null,
                        'latitude' => isset($country['latlng'][0]) ? $country['latlng'][0] : 0,
                        'longitude' => isset($country['latlng'][1]) ? $country['latlng'][1] : 0,
                        'population' => $country['population'] ?? 0,
                        'capital' => isset($country['capital'][0]) ? $country['capital'][0] : null,
                    ];
                }

                return null;
            } catch (\Exception $e) {
                Log::error('REST Countries API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Search countries by name
     */
    public function searchCountries($name)
    {
        try {
            $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/name/{$name}");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('REST Countries Search API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get countries by region
     */
    public function getCountriesByRegion($region)
    {
        $cacheKey = "rest_countries_region_{$region}";
        
        return Cache::remember($cacheKey, 86400, function () use ($region) {
            try {
                $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/region/{$region}");

                if ($response->successful()) {
                    return $response->json();
                }

                return [];
            } catch (\Exception $e) {
                Log::error('REST Countries API Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Fallback countries data when API is unavailable
     * REST Countries API has been deprecated as of July 2026
     * Contains 50 major countries for supply chain monitoring
     */
    protected function getFallbackCountries()
    {
        return [
            ['name' => 'United States', 'code' => 'USA', 'region' => 'Americas', 'currency_code' => 'USD', 'currency_name' => 'United States dollar', 'flag_url' => 'https://flagcdn.com/w320/us.png', 'latitude' => 38, 'longitude' => -97],
            ['name' => 'China', 'code' => 'CHN', 'region' => 'Asia', 'currency_code' => 'CNY', 'currency_name' => 'Chinese yuan', 'flag_url' => 'https://flagcdn.com/w320/cn.png', 'latitude' => 35, 'longitude' => 105],
            ['name' => 'Japan', 'code' => 'JPN', 'region' => 'Asia', 'currency_code' => 'JPY', 'currency_name' => 'Japanese yen', 'flag_url' => 'https://flagcdn.com/w320/jp.png', 'latitude' => 36, 'longitude' => 138],
            ['name' => 'Germany', 'code' => 'DEU', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/de.png', 'latitude' => 51, 'longitude' => 9],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'region' => 'Europe', 'currency_code' => 'GBP', 'currency_name' => 'British pound', 'flag_url' => 'https://flagcdn.com/w320/gb.png', 'latitude' => 54, 'longitude' => -2],
            ['name' => 'France', 'code' => 'FRA', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/fr.png', 'latitude' => 46, 'longitude' => 2],
            ['name' => 'India', 'code' => 'IND', 'region' => 'Asia', 'currency_code' => 'INR', 'currency_name' => 'Indian rupee', 'flag_url' => 'https://flagcdn.com/w320/in.png', 'latitude' => 20, 'longitude' => 77],
            ['name' => 'Indonesia', 'code' => 'IDN', 'region' => 'Asia', 'currency_code' => 'IDR', 'currency_name' => 'Indonesian rupiah', 'flag_url' => 'https://flagcdn.com/w320/id.png', 'latitude' => -5, 'longitude' => 120],
            ['name' => 'Brazil', 'code' => 'BRA', 'region' => 'Americas', 'currency_code' => 'BRL', 'currency_name' => 'Brazilian real', 'flag_url' => 'https://flagcdn.com/w320/br.png', 'latitude' => -10, 'longitude' => -55],
            ['name' => 'Canada', 'code' => 'CAN', 'region' => 'Americas', 'currency_code' => 'CAD', 'currency_name' => 'Canadian dollar', 'flag_url' => 'https://flagcdn.com/w320/ca.png', 'latitude' => 60, 'longitude' => -95],
            ['name' => 'Mexico', 'code' => 'MEX', 'region' => 'Americas', 'currency_code' => 'MXN', 'currency_name' => 'Mexican peso', 'flag_url' => 'https://flagcdn.com/w320/mx.png', 'latitude' => 23, 'longitude' => -102],
            ['name' => 'South Korea', 'code' => 'KOR', 'region' => 'Asia', 'currency_code' => 'KRW', 'currency_name' => 'South Korean won', 'flag_url' => 'https://flagcdn.com/w320/kr.png', 'latitude' => 37, 'longitude' => 127.5],
            ['name' => 'Australia', 'code' => 'AUS', 'region' => 'Oceania', 'currency_code' => 'AUD', 'currency_name' => 'Australian dollar', 'flag_url' => 'https://flagcdn.com/w320/au.png', 'latitude' => -27, 'longitude' => 133],
            ['name' => 'Spain', 'code' => 'ESP', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/es.png', 'latitude' => 40, 'longitude' => -4],
            ['name' => 'Italy', 'code' => 'ITA', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/it.png', 'latitude' => 42.8333, 'longitude' => 12.8333],
            ['name' => 'Netherlands', 'code' => 'NLD', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/nl.png', 'latitude' => 52.5, 'longitude' => 5.75],
            ['name' => 'Singapore', 'code' => 'SGP', 'region' => 'Asia', 'currency_code' => 'SGD', 'currency_name' => 'Singapore dollar', 'flag_url' => 'https://flagcdn.com/w320/sg.png', 'latitude' => 1.3667, 'longitude' => 103.8],
            ['name' => 'Switzerland', 'code' => 'CHE', 'region' => 'Europe', 'currency_code' => 'CHF', 'currency_name' => 'Swiss franc', 'flag_url' => 'https://flagcdn.com/w320/ch.png', 'latitude' => 47, 'longitude' => 8],
            ['name' => 'Saudi Arabia', 'code' => 'SAU', 'region' => 'Asia', 'currency_code' => 'SAR', 'currency_name' => 'Saudi riyal', 'flag_url' => 'https://flagcdn.com/w320/sa.png', 'latitude' => 25, 'longitude' => 45],
            ['name' => 'Turkey', 'code' => 'TUR', 'region' => 'Asia', 'currency_code' => 'TRY', 'currency_name' => 'Turkish lira', 'flag_url' => 'https://flagcdn.com/w320/tr.png', 'latitude' => 39, 'longitude' => 35],
            ['name' => 'Poland', 'code' => 'POL', 'region' => 'Europe', 'currency_code' => 'PLN', 'currency_name' => 'Polish złoty', 'flag_url' => 'https://flagcdn.com/w320/pl.png', 'latitude' => 52, 'longitude' => 20],
            ['name' => 'Belgium', 'code' => 'BEL', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/be.png', 'latitude' => 50.8333, 'longitude' => 4],
            ['name' => 'Sweden', 'code' => 'SWE', 'region' => 'Europe', 'currency_code' => 'SEK', 'currency_name' => 'Swedish krona', 'flag_url' => 'https://flagcdn.com/w320/se.png', 'latitude' => 62, 'longitude' => 15],
            ['name' => 'Norway', 'code' => 'NOR', 'region' => 'Europe', 'currency_code' => 'NOK', 'currency_name' => 'Norwegian krone', 'flag_url' => 'https://flagcdn.com/w320/no.png', 'latitude' => 62, 'longitude' => 10],
            ['name' => 'Austria', 'code' => 'AUT', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/at.png', 'latitude' => 47.3333, 'longitude' => 13.3333],
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'region' => 'Asia', 'currency_code' => 'AED', 'currency_name' => 'United Arab Emirates dirham', 'flag_url' => 'https://flagcdn.com/w320/ae.png', 'latitude' => 24, 'longitude' => 54],
            ['name' => 'Malaysia', 'code' => 'MYS', 'region' => 'Asia', 'currency_code' => 'MYR', 'currency_name' => 'Malaysian ringgit', 'flag_url' => 'https://flagcdn.com/w320/my.png', 'latitude' => 2.5, 'longitude' => 112.5],
            ['name' => 'Thailand', 'code' => 'THA', 'region' => 'Asia', 'currency_code' => 'THB', 'currency_name' => 'Thai baht', 'flag_url' => 'https://flagcdn.com/w320/th.png', 'latitude' => 15, 'longitude' => 100],
            ['name' => 'Vietnam', 'code' => 'VNM', 'region' => 'Asia', 'currency_code' => 'VND', 'currency_name' => 'Vietnamese đồng', 'flag_url' => 'https://flagcdn.com/w320/vn.png', 'latitude' => 16.1667, 'longitude' => 107.8333],
            ['name' => 'Philippines', 'code' => 'PHL', 'region' => 'Asia', 'currency_code' => 'PHP', 'currency_name' => 'Philippine peso', 'flag_url' => 'https://flagcdn.com/w320/ph.png', 'latitude' => 13, 'longitude' => 122],
            ['name' => 'South Africa', 'code' => 'ZAF', 'region' => 'Africa', 'currency_code' => 'ZAR', 'currency_name' => 'South African rand', 'flag_url' => 'https://flagcdn.com/w320/za.png', 'latitude' => -29, 'longitude' => 24],
            ['name' => 'Egypt', 'code' => 'EGY', 'region' => 'Africa', 'currency_code' => 'EGP', 'currency_name' => 'Egyptian pound', 'flag_url' => 'https://flagcdn.com/w320/eg.png', 'latitude' => 27, 'longitude' => 30],
            ['name' => 'Nigeria', 'code' => 'NGA', 'region' => 'Africa', 'currency_code' => 'NGN', 'currency_name' => 'Nigerian naira', 'flag_url' => 'https://flagcdn.com/w320/ng.png', 'latitude' => 10, 'longitude' => 8],
            ['name' => 'Argentina', 'code' => 'ARG', 'region' => 'Americas', 'currency_code' => 'ARS', 'currency_name' => 'Argentine peso', 'flag_url' => 'https://flagcdn.com/w320/ar.png', 'latitude' => -34, 'longitude' => -64],
            ['name' => 'Chile', 'code' => 'CHL', 'region' => 'Americas', 'currency_code' => 'CLP', 'currency_name' => 'Chilean peso', 'flag_url' => 'https://flagcdn.com/w320/cl.png', 'latitude' => -30, 'longitude' => -71],
            ['name' => 'Colombia', 'code' => 'COL', 'region' => 'Americas', 'currency_code' => 'COP', 'currency_name' => 'Colombian peso', 'flag_url' => 'https://flagcdn.com/w320/co.png', 'latitude' => 4, 'longitude' => -72],
            ['name' => 'Denmark', 'code' => 'DNK', 'region' => 'Europe', 'currency_code' => 'DKK', 'currency_name' => 'Danish krone', 'flag_url' => 'https://flagcdn.com/w320/dk.png', 'latitude' => 56, 'longitude' => 10],
            ['name' => 'Finland', 'code' => 'FIN', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/fi.png', 'latitude' => 64, 'longitude' => 26],
            ['name' => 'Greece', 'code' => 'GRC', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/gr.png', 'latitude' => 39, 'longitude' => 22],
            ['name' => 'Portugal', 'code' => 'PRT', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/pt.png', 'latitude' => 39.5, 'longitude' => -8],
            ['name' => 'Czech Republic', 'code' => 'CZE', 'region' => 'Europe', 'currency_code' => 'CZK', 'currency_name' => 'Czech koruna', 'flag_url' => 'https://flagcdn.com/w320/cz.png', 'latitude' => 49.75, 'longitude' => 15.5],
            ['name' => 'Romania', 'code' => 'ROU', 'region' => 'Europe', 'currency_code' => 'RON', 'currency_name' => 'Romanian leu', 'flag_url' => 'https://flagcdn.com/w320/ro.png', 'latitude' => 46, 'longitude' => 25],
            ['name' => 'New Zealand', 'code' => 'NZL', 'region' => 'Oceania', 'currency_code' => 'NZD', 'currency_name' => 'New Zealand dollar', 'flag_url' => 'https://flagcdn.com/w320/nz.png', 'latitude' => -41, 'longitude' => 174],
            ['name' => 'Ireland', 'code' => 'IRL', 'region' => 'Europe', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'flag_url' => 'https://flagcdn.com/w320/ie.png', 'latitude' => 53, 'longitude' => -8],
            ['name' => 'Israel', 'code' => 'ISR', 'region' => 'Asia', 'currency_code' => 'ILS', 'currency_name' => 'Israeli new shekel', 'flag_url' => 'https://flagcdn.com/w320/il.png', 'latitude' => 31.5, 'longitude' => 34.75],
            ['name' => 'Hong Kong', 'code' => 'HKG', 'region' => 'Asia', 'currency_code' => 'HKD', 'currency_name' => 'Hong Kong dollar', 'flag_url' => 'https://flagcdn.com/w320/hk.png', 'latitude' => 22.25, 'longitude' => 114.1667],
            ['name' => 'Taiwan', 'code' => 'TWN', 'region' => 'Asia', 'currency_code' => 'TWD', 'currency_name' => 'New Taiwan dollar', 'flag_url' => 'https://flagcdn.com/w320/tw.png', 'latitude' => 23.5, 'longitude' => 121],
            ['name' => 'Russia', 'code' => 'RUS', 'region' => 'Europe', 'currency_code' => 'RUB', 'currency_name' => 'Russian ruble', 'flag_url' => 'https://flagcdn.com/w320/ru.png', 'latitude' => 60, 'longitude' => 100],
            ['name' => 'Bangladesh', 'code' => 'BGD', 'region' => 'Asia', 'currency_code' => 'BDT', 'currency_name' => 'Bangladeshi taka', 'flag_url' => 'https://flagcdn.com/w320/bd.png', 'latitude' => 24, 'longitude' => 90],
            ['name' => 'Pakistan', 'code' => 'PAK', 'region' => 'Asia', 'currency_code' => 'PKR', 'currency_name' => 'Pakistani rupee', 'flag_url' => 'https://flagcdn.com/w320/pk.png', 'latitude' => 30, 'longitude' => 70],
        ];
    }
}
