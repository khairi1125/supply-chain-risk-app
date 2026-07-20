<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    protected $exchangeService;

    public function __construct(ExchangeRateService $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    /**
     * Get exchange rate for specific country
     * GET /api/currency/{country_code}
     */
    public function getExchangeRate($country_code)
    {
        $country = DB::table('countries')->where('code', strtoupper($country_code))->first();

        if (!$country) {
            return response()->json([
                'success' => false, 
                'message' => 'Country not found'
            ], 404);
        }

        $currencyCode = $country->currency_code;

        if (!$currencyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Currency code not available for this country'
            ], 404);
        }

        try {
            $rate = $this->exchangeService->getRate('USD', $currencyCode);
            $history = $this->exchangeService->getRateHistory('USD', $currencyCode);

            // Calculate 7-day change
            $oldestRate = reset($history);
            $latestRate = end($history);
            $change = (($latestRate - $oldestRate) / $oldestRate) * 100;

            return response()->json([
                'success' => true,
                'country_name' => $country->name,
                'base_currency' => 'USD',
                'target_currency' => $currencyCode,
                'currency_name' => $country->currency_name,
                'exchange_rate' => $rate,
                'change_7d' => round($change, 2),
                'history' => $history,
                'message' => "1 USD = {$rate} {$currencyCode}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch exchange rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all currencies exchange rates
     * GET /api/currency
     */
    public function getAllRates(Request $request)
    {
        $baseCurrency = $request->get('base', 'USD');
        $forceRefresh = $request->get('refresh', false);
        
        // Clear cache if force refresh requested
        if ($forceRefresh) {
            $this->exchangeService->clearCache();
        }
        
        // Get ALL countries (don't use unique - show all countries even with duplicate currencies)
        $countries = DB::table('countries')
            ->select('code', 'name', 'currency_code', 'currency_name', 'flag_url', 'region')
            ->whereNotNull('currency_code')
            ->where('currency_code', '!=', '')
            ->orderBy('name')
            ->get();

        $currencyCodes = $countries->pluck('currency_code')->unique()->toArray();
        
        try {
            $rates = $this->exchangeService->getRates($baseCurrency, $currencyCodes);
            
            $result = [];
            foreach ($countries as $country) {
                $currency = $country->currency_code;
                
                if (isset($rates[$currency]) && $rates[$currency] > 0) {
                    // Get 7-day history
                    $history = $this->exchangeService->getRateHistory($baseCurrency, $currency);
                    
                    // Calculate change
                    $oldestRate = reset($history);
                    $latestRate = end($history);
                    $change = $oldestRate > 0 ? (($latestRate - $oldestRate) / $oldestRate) * 100 : 0;
                    
                    $result[] = [
                        'country_code' => $country->code,
                        'country_name' => $country->name,
                        'currency_code' => $currency,
                        'currency_name' => $country->currency_name,
                        'flag_url' => $country->flag_url,
                        'region' => $country->region,
                        'rate' => $rates[$currency],
                        'change_7d' => round($change, 2),
                        'trend' => $change > 0.5 ? 'up' : ($change < -0.5 ? 'down' : 'stable'),
                        'history' => $history
                    ];
                }
            }

            // Sort by currency code
            usort($result, function($a, $b) {
                return strcmp($a['currency_code'], $b['currency_code']);
            });

            return response()->json([
                'success' => true,
                'base_currency' => $baseCurrency,
                'total' => count($result),
                'data' => $result,
                'last_updated' => now()->toIso8601String(),
                'data_source' => 'real-time',
                'note' => 'Showing all countries (includes countries sharing same currency)'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch exchange rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currency history for chart
     * GET /api/currency/history/{from}/{to}
     */
    public function getHistory($from, $to)
    {
        try {
            $history = $this->exchangeService->getRateHistory($from, $to);
            
            return response()->json([
                'success' => true,
                'from' => $from,
                'to' => $to,
                'history' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history: ' . $e->getMessage()
            ], 500);
        }
    }
}
