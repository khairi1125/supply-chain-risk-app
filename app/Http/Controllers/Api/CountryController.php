<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenMeteoService;
use App\Services\WorldBankService;
use App\Services\ExchangeRateService;
use App\Services\GNewsService;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    protected $meteoService;
    protected $worldBankService;
    protected $exchangeService;
    protected $newsService;
    protected $riskService;

    public function __construct(
        OpenMeteoService $meteoService,
        WorldBankService $worldBankService,
        ExchangeRateService $exchangeService,
        GNewsService $newsService,
        RiskScoringService $riskService
    ) {
        $this->meteoService = $meteoService;
        $this->worldBankService = $worldBankService;
        $this->exchangeService = $exchangeService;
        $this->newsService = $newsService;
        $this->riskService = $riskService;
    }

    /**
     * Get all countries with optional filters
     * GET /api/countries?search=xxx&region=xxx
     */
    public function index(Request $request)
    {
        $query = DB::table('countries');

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Region filter
        if ($request->has('region') && !empty($request->region) && $request->region !== 'All') {
            $query->where('region', $request->region);
        }

        // Paginate results
        $perPage = $request->get('per_page', 20);
        $countries = $query->paginate($perPage);

        // Add risk scores to each country
        $countries->getCollection()->transform(function ($country) {
            $riskScore = $this->riskService->getCachedRiskScore($country->code);
            $country->risk_score = $riskScore ? $riskScore['total_score'] : null;
            $country->risk_level = $riskScore ? $riskScore['risk_level'] : 'unknown';
            return $country;
        });

        return response()->json($countries);
    }

    /**
     * Get detailed country information
     * GET /api/countries/{code}
     */
    public function show($code)
    {
        // Get country basic info
        $country = DB::table('countries')->where('code', $code)->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        // Get weather data
        $weather = $this->meteoService->getWeather($country->latitude, $country->longitude);

        // Get economic data from World Bank (use cca2 for World Bank API)
        $worldBankCode = $country->cca2 ?? $code; // Fallback to cca3 if cca2 not available
        $gdpData = $this->worldBankService->getGDP($worldBankCode);
        $inflationData = $this->worldBankService->getInflation($worldBankCode);
        $population = $this->worldBankService->getPopulation($worldBankCode);

        // Get latest values
        $latestGdp = !empty($gdpData) ? reset($gdpData) : null;
        $latestInflation = !empty($inflationData) ? round(reset($inflationData), 2) : null;

        // Get currency info
        $currencyRate = null;
        $currencyChange = 0;
        if ($country->currency_code && $country->currency_code !== 'USD') {
            try {
                $rate = $this->exchangeService->getRate('USD', $country->currency_code);
                $currencyRate = $rate;
                
                // Get 7-day history to calculate change
                $history = $this->exchangeService->getRateHistory('USD', $country->currency_code);
                if (count($history) >= 2) {
                    $oldestRate = array_values($history)[0];
                    $newestRate = end($history);
                    $currencyChange = (($newestRate - $oldestRate) / $oldestRate) * 100;
                }
            } catch (\Exception $e) {
                // Use default values if API fails
            }
        } else {
            $currencyRate = 1.0; // USD to USD
        }
        
        // Get news with sentiment analysis
        $newsData = $this->newsService->getNewsByCountryWithSentiment($country->name, 5);
        $newsRiskScore = $this->newsService->getNewsSentimentRiskScore($country->name, 5);

        // Calculate risk score with real news sentiment
        $riskData = [
            'weather' => $weather,
            'inflation' => $latestInflation,
            'currency_change' => $currencyChange,
            'news_sentiment' => $newsRiskScore // Use numeric risk score
        ];

        $riskScore = $this->riskService->calculateRiskScore($code, $riskData);

        // Prepare response
        return response()->json([
            'country' => [
                'name' => $country->name,
                'code' => $country->code,
                'region' => $country->region,
                'currency_code' => $country->currency_code,
                'currency_name' => $country->currency_name,
                'flag_url' => $country->flag_url,
                'latitude' => (float) $country->latitude,
                'longitude' => (float) $country->longitude,
            ],
            'weather' => $weather,
            'economic' => [
                'gdp' => $this->formatTrendData($gdpData),
                'inflation' => $this->formatTrendData($inflationData),
                'population' => $population,
                'latest_gdp' => $latestGdp,
                'latest_inflation' => $latestInflation,
            ],
            'currency' => [
                'code' => $country->currency_code,
                'name' => $country->currency_name,
                'rate_to_usd' => $currencyRate,
                'change_7d' => round($currencyChange, 2),
            ],
            'news' => [
                'articles' => $newsData['articles'],
                'sentiment' => $newsData['sentiment_analysis']
            ],
            'risk' => $riskScore,
        ]);
    }

    /**
     * Get risk score only
     * GET /api/risk/{code}
     */
    public function getRisk($code)
    {
        // Check cached score first
        $cached = $this->riskService->getCachedRiskScore($code);
        
        if ($cached) {
            return response()->json($cached);
        }

        // Calculate fresh score
        $country = DB::table('countries')->where('code', $code)->first();
        
        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $weather = $this->meteoService->getWeather($country->latitude, $country->longitude);
        
        // Use cca2 for World Bank API
        $worldBankCode = $country->cca2 ?? $code;
        $inflationData = $this->worldBankService->getInflation($worldBankCode);
        $latestInflation = !empty($inflationData) ? reset($inflationData) : null;

        $riskData = [
            'weather' => $weather,
            'inflation' => $latestInflation,
            'currency_change' => 0, // Simplified for quick calculation
            'news_sentiment' => 'neutral'
        ];

        $riskScore = $this->riskService->calculateRiskScore($code, $riskData);

        return response()->json($riskScore);
    }

    /**
     * Get World Bank trend data
     * GET /api/worldbank/{code}
     */
    public function getWorldBankData($code)
    {
        // Get country to fetch cca2
        $country = DB::table('countries')->where('code', $code)->first();
        
        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }
        
        // Use cca2 for World Bank API
        $worldBankCode = $country->cca2 ?? $code;
        $gdpData = $this->worldBankService->getGDP($worldBankCode);
        $inflationData = $this->worldBankService->getInflation($worldBankCode);

        return response()->json([
            'gdp_trend' => $this->formatTrendData($gdpData),
            'inflation_trend' => $this->formatTrendData($inflationData),
        ]);
    }

    /**
     * Format trend data for charts
     */
    private function formatTrendData($data, $decimals = 2)
    {
        if (empty($data)) {
            return [];
        }

        $formatted = [];
        foreach ($data as $year => $value) {
            $formatted[] = [
                'year' => $year,
                'value' => round($value, $decimals)
            ];
        }

        // Sort by year ascending
        usort($formatted, function ($a, $b) {
            return $a['year'] <=> $b['year'];
        });

        return $formatted;
    }
}
