<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected $baseUrl = 'https://v6.exchangerate-api.com/v6';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.exchange_rate.api_key');
    }

    /**
     * Get single exchange rate with database caching
     */
    public function getRate($baseCurrency, $targetCurrency)
    {
        // Check cache in database first (valid for 30 minutes)
        $cached = \DB::table('currency_cache')
            ->where('base_currency', $baseCurrency)
            ->where('target_currency', $targetCurrency)
            ->where('fetched_at', '>=', now()->subMinutes(30))
            ->first();
            
        if ($cached) {
            return (float) $cached->rate;
        }
        
        $rates = $this->getRates($baseCurrency, [$targetCurrency]);
        return $rates[$targetCurrency] ?? 0;
    }

    /**
     * Get multiple exchange rates
     */
    public function getRates($baseCurrency, $currencies = [])
    {
        $defaultCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'IDR', 'SGD', 'AUD'];
        $currencies = !empty($currencies) ? $currencies : $defaultCurrencies;
        
        try {
            if (empty($this->apiKey) || $this->apiKey === 'your_key_here' || strlen($this->apiKey) < 10) {
                Log::warning('Exchange Rate API: Using mock data (invalid or missing API key)');
                return $this->getMockRates($baseCurrency, $currencies);
            }

            $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/{$this->apiKey}/latest/{$baseCurrency}");

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if API returned an error
                if (isset($data['result']) && $data['result'] === 'error') {
                    Log::error('Exchange Rate API Error: ' . ($data['error-type'] ?? 'Unknown error'));
                    return $this->getMockRates($baseCurrency, $currencies);
                }
                
                $allRates = $data['conversion_rates'] ?? [];
                
                $result = [];
                foreach ($currencies as $currency) {
                    $rate = $allRates[$currency] ?? 0;
                    $result[$currency] = $rate;
                    
                    // Cache to database
                    if ($rate > 0) {
                        $this->cacheRate($baseCurrency, $currency, $rate);
                    }
                }
                
                return $result;
            }

            Log::error('Exchange Rate API failed', ['status' => $response->status()]);
            return $this->getMockRates($baseCurrency, $currencies);
        } catch (\Exception $e) {
            Log::error('Exchange Rate API Error: ' . $e->getMessage());
            return $this->getMockRates($baseCurrency, $currencies);
        }
    }
    
    /**
     * Get rate history (simulated for 7 days)
     */
    public function getRateHistory($baseCurrency, $targetCurrency)
    {
        $currentRate = $this->getRate($baseCurrency, $targetCurrency);
        
        // Simulate 7 days of history with ±2% variation
        $history = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $variation = (rand(-200, 200) / 10000); // -2% to +2%
            $rate = $currentRate * (1 + $variation);
            $history[$date] = round($rate, 6);
        }
        
        return $history;
    }

    /**
     * Cache rate to database
     */
    private function cacheRate($baseCurrency, $targetCurrency, $rate)
    {
        try {
            \DB::table('currency_cache')->updateOrInsert(
                [
                    'base_currency' => $baseCurrency,
                    'target_currency' => $targetCurrency,
                ],
                [
                    'rate' => $rate,
                    'fetched_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to cache currency rate: ' . $e->getMessage());
        }
    }

    /**
     * Get mock rates for development
     */
    private function getMockRates($baseCurrency, $currencies)
    {
        $mockRates = [
            'USD' => 1.0,
            'EUR' => 0.85,
            'GBP' => 0.73,
            'JPY' => 110.0,
            'CNY' => 6.45,
            'IDR' => 14250.0,
            'SGD' => 1.35,
            'AUD' => 1.32,
        ];
        
        $result = [];
        foreach ($currencies as $currency) {
            // Simple conversion: if base is not USD, adjust accordingly
            if ($baseCurrency === 'USD') {
                $result[$currency] = $mockRates[$currency] ?? 1.0;
            } else {
                $baseRate = $mockRates[$baseCurrency] ?? 1.0;
                $targetRate = $mockRates[$currency] ?? 1.0;
                $result[$currency] = $targetRate / $baseRate;
            }
        }
        
        return $result;
    }

    /**
     * Convert currency
     */
    public function convertCurrency($from, $to, $amount = 1)
    {
        try {
            if (empty($this->apiKey) || $this->apiKey === 'your_key_here') {
                $rate = $this->getRate($from, $to);
                return [
                    'result' => 'success',
                    'base_code' => $from,
                    'target_code' => $to,
                    'conversion_rate' => $rate,
                    'conversion_result' => $amount * $rate
                ];
            }

            $response = Http::withOptions(['verify' => false])->timeout(10)->get("{$this->baseUrl}/{$this->apiKey}/pair/{$from}/{$to}/{$amount}");

            if ($response->successful()) {
                return $response->json();
            }

            $rate = $this->getRate($from, $to);
            return [
                'result' => 'success',
                'base_code' => $from,
                'target_code' => $to,
                'conversion_rate' => $rate,
                'conversion_result' => $amount * $rate
            ];
        } catch (\Exception $e) {
            Log::error('Exchange Rate Conversion API Error: ' . $e->getMessage());
            $rate = $this->getRate($from, $to);
            return [
                'result' => 'success',
                'base_code' => $from,
                'target_code' => $to,
                'conversion_rate' => $rate,
                'conversion_result' => $amount * $rate
            ];
        }
    }
}
