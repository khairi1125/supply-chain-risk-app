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
        // Check cache in database first (valid for 1 hour for consistency)
        $cached = \DB::table('currency_cache')
            ->where('base_currency', $baseCurrency)
            ->where('target_currency', $targetCurrency)
            ->where('fetched_at', '>=', now()->subHour())
            ->first();
            
        if ($cached) {
            Log::info("Using cached rate for {$baseCurrency}/{$targetCurrency}: {$cached->rate}");
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

            $url = "{$this->baseUrl}/{$this->apiKey}/latest/{$baseCurrency}";
            Log::info('Fetching exchange rates from: ' . $url);
            
            $response = Http::withOptions(['verify' => false])->timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Exchange Rate API Response:', ['result' => $data['result'] ?? 'unknown']);
                
                // Check if API returned an error
                if (isset($data['result']) && $data['result'] === 'error') {
                    Log::error('Exchange Rate API Error: ' . ($data['error-type'] ?? 'Unknown error'));
                    return $this->getMockRates($baseCurrency, $currencies);
                }
                
                $allRates = $data['conversion_rates'] ?? [];
                
                if (empty($allRates)) {
                    Log::warning('Exchange Rate API returned empty rates');
                    return $this->getMockRates($baseCurrency, $currencies);
                }
                
                Log::info('Exchange Rate API: Retrieved ' . count($allRates) . ' rates successfully');
                
                $result = [];
                foreach ($currencies as $currency) {
                    $rate = $allRates[$currency] ?? 0;
                    $result[$currency] = $rate;
                    
                    // Cache to database only for valid rates
                    if ($rate > 0) {
                        $this->cacheRate($baseCurrency, $currency, $rate);
                    }
                }
                
                return $result;
            }

            Log::error('Exchange Rate API failed', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->getMockRates($baseCurrency, $currencies);
        } catch (\Exception $e) {
            Log::error('Exchange Rate API Error: ' . $e->getMessage());
            return $this->getMockRates($baseCurrency, $currencies);
        }
    }
    
    /**
     * Get rate history (simulated for 7 days based on current rate)
     * More realistic simulation with smaller variations
     */
    public function getRateHistory($baseCurrency, $targetCurrency)
    {
        $currentRate = $this->getRate($baseCurrency, $targetCurrency);
        
        // Simulate 7 days of history with ±1% variation for more realistic data
        $history = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $variation = (rand(-100, 100) / 10000); // -1% to +1% variation
            $rate = $currentRate * (1 + $variation);
            
            // Ensure rate is positive and reasonable
            if ($rate > 0) {
                $history[$date] = round($rate, 6);
            } else {
                $history[$date] = $currentRate;
            }
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
     * Get mock rates for development (comprehensive list for all currencies)
     */
    private function getMockRates($baseCurrency, $currencies)
    {
        // Comprehensive mock rates for all major and minor currencies
        $mockRates = [
            'USD' => 1.0, 'EUR' => 0.92, 'GBP' => 0.79, 'JPY' => 149.50, 'CNY' => 7.24,
            'IDR' => 15850.0, 'SGD' => 1.34, 'AUD' => 1.57, 'CAD' => 1.42, 'CHF' => 0.88,
            'HKD' => 7.81, 'KRW' => 1415.0, 'INR' => 83.50, 'MYR' => 4.48, 'THB' => 35.20,
            'NZD' => 1.72, 'PHP' => 57.50, 'TWD' => 31.80, 'VND' => 25350.0, 'BRL' => 5.85,
            'MXN' => 17.50, 'ZAR' => 18.40, 'AED' => 3.67, 'SAR' => 3.75, 'QAR' => 3.64,
            'KWD' => 0.31, 'BHD' => 0.38, 'OMR' => 0.38, 'JOD' => 0.71, 'ILS' => 3.65,
            'TRY' => 32.50, 'RUB' => 99.50, 'PLN' => 4.00, 'CZK' => 23.50, 'HUF' => 360.0,
            'RON' => 4.56, 'BGN' => 1.81, 'HRK' => 6.88, 'RSD' => 105.0, 'HNL' => 24.80,
            'GTQ' => 7.85, 'CRC' => 530.0, 'COP' => 4050.0, 'PEN' => 3.75, 'CLP' => 870.0,
            'ARS' => 900.0, 'UYU' => 39.50, 'BOB' => 6.90, 'PYG' => 7350.0, 'UGX' => 3700.0,
            'KES' => 130.0, 'TZS' => 2550.0, 'NGN' => 780.0, 'GHS' => 13.20, 'EGP' => 48.50,
            'MAD' => 9.80, 'TND' => 3.10, 'LBP' => 89500.0, 'PKR' => 278.0, 'BDT' => 106.0,
            'LKR' => 315.0, 'AFN' => 70.50, 'BND' => 1.35, 'KHR' => 4100.0, 'LAK' => 20800.0,
            'MMK' => 2100.0, 'KZT' => 460.0, 'UZS' => 13450.0, 'TJS' => 10.85, 'KGS' => 87.50,
            'TMT' => 3.50, 'AZN' => 1.70, 'GEL' => 2.75, 'AMD' => 390.0, 'BYN' => 3.25,
            'UAH' => 40.50, 'ISK' => 135.0, 'NOK' => 10.50, 'SEK' => 10.80, 'DKK' => 6.88,
            'FJD' => 2.25, 'WST' => 2.80, 'SBD' => 8.35, 'VUV' => 120.0, 'XPF' => 107.0,
            'TOP' => 2.35, 'PGK' => 3.55, 'BSD' => 1.0, 'BMD' => 1.0, 'BZD' => 2.0,
            'JMD' => 155.0, 'BBD' => 2.0, 'TTD' => 6.75, 'XCD' => 2.70, 'SRD' => 35.0,
            'FKP' => 0.79, 'SHP' => 0.79, 'GIP' => 0.79, 'GBP' => 0.79,
        ];
        
        $result = [];
        foreach ($currencies as $currency) {
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

    /**
     * Clear all cached rates
     */
    public function clearCache()
    {
        try {
            \DB::table('currency_cache')->truncate();
            Log::info('Currency cache cleared successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear currency cache: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear cache for specific currency pair
     */
    public function clearCachePair($baseCurrency, $targetCurrency)
    {
        try {
            \DB::table('currency_cache')
                ->where('base_currency', $baseCurrency)
                ->where('target_currency', $targetCurrency)
                ->delete();
            Log::info("Cleared cache for {$baseCurrency}/{$targetCurrency}");
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear currency cache pair: ' . $e->getMessage());
            return false;
        }
    }
}
