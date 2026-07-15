<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiskScoringService
{
    /**
     * Calculate comprehensive risk score for a country
     * Based on weighted risk model with 4 components
     * 
     * @param string $countryCode - 3-letter country code
     * @param array $data - Optional data override
     * @return array
     */
    public function calculateRiskScore($countryCode, $data = [])
    {
        // Calculate individual risk components
        $weatherRisk = $this->calculateWeatherRisk($data['weather'] ?? null);
        $inflationRisk = $this->calculateInflationRisk($data['inflation'] ?? null);
        $currencyRisk = $this->calculateCurrencyRisk($data['currency_change'] ?? null);
        $newsRisk = $this->calculateNewsRisk($data['news_sentiment'] ?? null);
        
        // Weighted calculation
        // Weather: 25%, Inflation: 30%, Currency: 20%, News: 25%
        $totalScore = ($weatherRisk * 0.25) + 
                      ($inflationRisk * 0.30) + 
                      ($currencyRisk * 0.20) + 
                      ($newsRisk * 0.25);
        
        $riskLevel = $this->getRiskLevel($totalScore);
        
        $result = [
            'country_code' => $countryCode,
            'weather_score' => $weatherRisk,
            'inflation_score' => $inflationRisk,
            'currency_score' => $currencyRisk,
            'news_score' => $newsRisk,
            'total_score' => round($totalScore, 2),
            'risk_level' => $riskLevel,
            'color' => $this->getRiskColor($totalScore),
            'calculated_at' => now()->toIso8601String()
        ];
        
        // Save to database
        $this->saveRiskScore($countryCode, $result);
        
        return $result;
    }

    /**
     * Calculate Weather Risk (Weight: 25%)
     * Based on weather risk level from OpenMeteoService
     */
    private function calculateWeatherRisk($weatherData)
    {
        if (!$weatherData || !isset($weatherData['risk_level'])) {
            return 30; // Default if no data
        }
        
        $riskLevel = strtolower($weatherData['risk_level']);
        
        switch ($riskLevel) {
            case 'low':
                return 10;
            case 'medium':
                return 50;
            case 'high':
                return 90;
            default:
                return 30;
        }
    }

    /**
     * Calculate Inflation Risk (Weight: 30%)
     * Based on inflation percentage from World Bank
     */
    private function calculateInflationRisk($inflation)
    {
        if ($inflation === null) {
            return 50; // Default if no data
        }
        
        if ($inflation < 3) {
            return 20;
        } elseif ($inflation >= 3 && $inflation < 7) {
            return 50;
        } elseif ($inflation >= 7 && $inflation < 15) {
            return 75;
        } else { // > 15%
            return 95;
        }
    }

    /**
     * Calculate Currency Risk (Weight: 20%)
     * Based on 7-day currency change percentage
     */
    private function calculateCurrencyRisk($currencyChange)
    {
        if ($currencyChange === null) {
            return 40; // Default if no data
        }
        
        $absChange = abs($currencyChange);
        
        if ($absChange < 2) {
            return 15;
        } elseif ($absChange >= 2 && $absChange < 5) {
            return 45;
        } elseif ($absChange >= 5 && $absChange < 10) {
            return 70;
        } else { // > 10%
            return 90;
        }
    }

    /**
     * Calculate News Sentiment Risk (Weight: 25%)
     * Based on sentiment analysis of news articles
     * Can accept either numeric risk score or sentiment string
     */
    private function calculateNewsRisk($newsData)
    {
        // If already a numeric score (0-100), use it directly
        if (is_numeric($newsData) && $newsData >= 0 && $newsData <= 100) {
            return $newsData;
        }
        
        // If null, use default
        if ($newsData === null) {
            return 50; // Default if no data
        }
        
        // If string sentiment, convert to score
        // Sentiment can be: 'positive', 'neutral', 'negative', 'very_negative'
        switch (strtolower($newsData)) {
            case 'positive':
                return 20;
            case 'neutral':
                return 45;
            case 'negative':
                return 75;
            case 'very_negative':
                return 90;
            default:
                return 50;
        }
    }

    /**
     * Get risk level category based on total score
     * 
     * @param float $score
     * @return string
     */
    private function getRiskLevel($score)
    {
        if ($score >= 76) {
            return 'critical';
        } elseif ($score >= 51) {
            return 'high';
        } elseif ($score >= 26) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get badge color for risk level
     * 
     * @param float $score
     * @return string
     */
    public function getRiskColor($score)
    {
        if ($score >= 76) {
            return 'danger'; // Red - Critical
        } elseif ($score >= 51) {
            return 'warning'; // Orange - High
        } elseif ($score >= 26) {
            return 'info'; // Yellow - Medium
        } else {
            return 'success'; // Green - Low
        }
    }

    /**
     * Save risk score to database
     */
    private function saveRiskScore($countryCode, $result)
    {
        try {
            DB::table('risk_scores')->updateOrInsert(
                ['country_code' => $countryCode],
                [
                    'weather_score' => $result['weather_score'],
                    'inflation_score' => $result['inflation_score'],
                    'currency_score' => $result['currency_score'],
                    'news_score' => $result['news_score'],
                    'total_score' => $result['total_score'],
                    'risk_level' => $result['risk_level'],
                    'calculated_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to save risk score: ' . $e->getMessage());
        }
    }

    /**
     * Get cached risk score from database
     * 
     * @param string $countryCode
     * @return array|null
     */
    public function getCachedRiskScore($countryCode)
    {
        $cached = DB::table('risk_scores')
            ->where('country_code', $countryCode)
            ->where('calculated_at', '>=', now()->subHours(6)) // Valid for 6 hours
            ->first();
            
        if ($cached) {
            return [
                'country_code' => $cached->country_code,
                'weather_score' => (float) $cached->weather_score,
                'inflation_score' => (float) $cached->inflation_score,
                'currency_score' => (float) $cached->currency_score,
                'news_score' => (float) $cached->news_score,
                'total_score' => (float) $cached->total_score,
                'risk_level' => $cached->risk_level,
                'color' => $this->getRiskColor($cached->total_score),
                'calculated_at' => $cached->calculated_at,
            ];
        }
        
        return null;
    }
}
