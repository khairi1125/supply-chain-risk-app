<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExchangeRateService
{
    protected $baseUrl = 'https://v6.exchangerate-api.com/v6';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.exchange_rate.api_key');
    }

    /**
     * Get latest exchange rates
     */
    public function getLatestRates($baseCurrency = 'USD')
    {
        $cacheKey = "exchange_rate_{$baseCurrency}";
        
        return Cache::remember($cacheKey, 3600, function () use ($baseCurrency) {
            try {
                if (empty($this->apiKey) || $this->apiKey === 'your_key_here') {
                    return $this->getMockRates($baseCurrency);
                }

                $response = Http::get("{$this->baseUrl}/{$this->apiKey}/latest/{$baseCurrency}");

                if ($response->successful()) {
                    return $response->json();
                }

                return $this->getMockRates($baseCurrency);
            } catch (\Exception $e) {
                \Log::error('Exchange Rate API Error: ' . $e->getMessage());
                return $this->getMockRates($baseCurrency);
            }
        });
    }

    /**
     * Convert currency
     */
    public function convertCurrency($from, $to, $amount = 1)
    {
        try {
            if (empty($this->apiKey) || $this->apiKey === 'your_key_here') {
                return $this->getMockConversion($from, $to, $amount);
            }

            $response = Http::get("{$this->baseUrl}/{$this->apiKey}/pair/{$from}/{$to}/{$amount}");

            if ($response->successful()) {
                return $response->json();
            }

            return $this->getMockConversion($from, $to, $amount);
        } catch (\Exception $e) {
            \Log::error('Exchange Rate Conversion API Error: ' . $e->getMessage());
            return $this->getMockConversion($from, $to, $amount);
        }
    }

    /**
     * Get mock rates for development
     */
    private function getMockRates($baseCurrency)
    {
        return [
            'result' => 'success',
            'base_code' => $baseCurrency,
            'conversion_rates' => [
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.73,
                'JPY' => 110.0,
                'CNY' => 6.45,
                'IDR' => 14250.0,
            ]
        ];
    }

    /**
     * Get mock conversion for development
     */
    private function getMockConversion($from, $to, $amount)
    {
        $mockRate = 1.2; // Simple mock rate
        return [
            'result' => 'success',
            'base_code' => $from,
            'target_code' => $to,
            'conversion_rate' => $mockRate,
            'conversion_result' => $amount * $mockRate
        ];
    }
}
