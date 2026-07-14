<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WorldBankService
{
    protected $baseUrl = 'https://api.worldbank.org/v2';

    /**
     * Get GDP data for a country (5 years)
     */
    public function getGDP($countryCode)
    {
        $cacheKey = "worldbank_gdp_{$countryCode}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode) {
            try {
                $response = Http::withOptions(['verify' => false])->timeout(15)->get("{$this->baseUrl}/country/{$countryCode}/indicator/NY.GDP.MKTP.CD", [
                    'format' => 'json',
                    'mrv' => 5,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $indicators = $data[1] ?? [];
                    
                    $result = [];
                    foreach ($indicators as $item) {
                        if ($item['value'] !== null) {
                            $result[$item['date']] = $item['value'];
                        }
                    }
                    
                    return $result;
                }

                Log::error('World Bank GDP API failed', ['country' => $countryCode]);
                return [];
            } catch (\Exception $e) {
                Log::error('World Bank GDP API Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get inflation data for a country (5 years)
     */
    public function getInflation($countryCode)
    {
        $cacheKey = "worldbank_inflation_{$countryCode}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode) {
            try {
                $response = Http::withOptions(['verify' => false])->timeout(15)->get("{$this->baseUrl}/country/{$countryCode}/indicator/FP.CPI.TOTL.ZG", [
                    'format' => 'json',
                    'mrv' => 5,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $indicators = $data[1] ?? [];
                    
                    $result = [];
                    foreach ($indicators as $item) {
                        if ($item['value'] !== null) {
                            $result[$item['date']] = $item['value'];
                        }
                    }
                    
                    return $result;
                }

                Log::error('World Bank Inflation API failed', ['country' => $countryCode]);
                return [];
            } catch (\Exception $e) {
                Log::error('World Bank Inflation API Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get population data for a country (latest)
     */
    public function getPopulation($countryCode)
    {
        $cacheKey = "worldbank_population_{$countryCode}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode) {
            try {
                $response = Http::withOptions(['verify' => false])->timeout(15)->get("{$this->baseUrl}/country/{$countryCode}/indicator/SP.POP.TOTL", [
                    'format' => 'json',
                    'mrv' => 1,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $indicators = $data[1] ?? [];
                    
                    return isset($indicators[0]['value']) ? (int) $indicators[0]['value'] : 0;
                }

                return 0;
            } catch (\Exception $e) {
                Log::error('World Bank Population API Error: ' . $e->getMessage());
                return 0;
            }
        });
    }
    
    /**
     * Get exports data for a country (latest)
     */
    public function getExports($countryCode)
    {
        $cacheKey = "worldbank_exports_{$countryCode}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode) {
            try {
                $response = Http::withOptions(['verify' => false])->timeout(15)->get("{$this->baseUrl}/country/{$countryCode}/indicator/NE.EXP.GNFS.CD", [
                    'format' => 'json',
                    'mrv' => 1,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $indicators = $data[1] ?? [];
                    
                    return isset($indicators[0]['value']) ? $indicators[0]['value'] : 0;
                }

                return 0;
            } catch (\Exception $e) {
                Log::error('World Bank Exports API Error: ' . $e->getMessage());
                return 0;
            }
        });
    }
    
    /**
     * Get imports data for a country (latest)
     */
    public function getImports($countryCode)
    {
        $cacheKey = "worldbank_imports_{$countryCode}";
        
        return Cache::remember($cacheKey, 86400, function () use ($countryCode) {
            try {
                $response = Http::withOptions(['verify' => false])->timeout(15)->get("{$this->baseUrl}/country/{$countryCode}/indicator/NE.IMP.GNFS.CD", [
                    'format' => 'json',
                    'mrv' => 1,
                    'per_page' => 100
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $indicators = $data[1] ?? [];
                    
                    return isset($indicators[0]['value']) ? $indicators[0]['value'] : 0;
                }

                return 0;
            } catch (\Exception $e) {
                Log::error('World Bank Imports API Error: ' . $e->getMessage());
                return 0;
            }
        });
    }
    
    /**
     * Get all country data in one call
     */
    public function getCountryData($countryCode)
    {
        return [
            'gdp' => $this->getGDP($countryCode),
            'inflation' => $this->getInflation($countryCode),
            'population' => $this->getPopulation($countryCode),
            'exports' => $this->getExports($countryCode),
            'imports' => $this->getImports($countryCode),
        ];
    }
}
