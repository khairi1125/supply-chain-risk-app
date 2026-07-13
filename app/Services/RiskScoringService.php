<?php

namespace App\Services;

class RiskScoringService
{
    /**
     * Calculate risk score for a country
     * 
     * Risk factors:
     * - Economic stability (GDP growth, inflation)
     * - Weather conditions
     * - Currency volatility
     * - News sentiment
     * - Port congestion
     */
    public function calculateRiskScore($data)
    {
        $scores = [];
        
        // Economic Risk (0-100)
        $scores['economic'] = $this->calculateEconomicRisk($data);
        
        // Weather Risk (0-100)
        $scores['weather'] = $this->calculateWeatherRisk($data);
        
        // Currency Risk (0-100)
        $scores['currency'] = $this->calculateCurrencyRisk($data);
        
        // Sentiment Risk (0-100)
        $scores['sentiment'] = $this->calculateSentimentRisk($data);
        
        // Overall Risk Score (weighted average)
        $weights = [
            'economic' => 0.35,
            'weather' => 0.20,
            'currency' => 0.25,
            'sentiment' => 0.20
        ];
        
        $totalScore = 0;
        foreach ($scores as $key => $score) {
            $totalScore += $score * $weights[$key];
        }
        
        return [
            'overall_score' => round($totalScore, 2),
            'risk_level' => $this->getRiskLevel($totalScore),
            'breakdown' => $scores,
            'weights' => $weights
        ];
    }

    /**
     * Calculate economic risk based on GDP growth and inflation
     */
    private function calculateEconomicRisk($data)
    {
        $risk = 50; // Base risk
        
        if (isset($data['gdp_growth'])) {
            // Lower GDP growth = higher risk
            $gdpGrowth = $data['gdp_growth'];
            if ($gdpGrowth < 0) {
                $risk += 30;
            } elseif ($gdpGrowth < 2) {
                $risk += 15;
            } elseif ($gdpGrowth > 5) {
                $risk -= 15;
            }
        }
        
        if (isset($data['inflation'])) {
            // Higher inflation = higher risk
            $inflation = $data['inflation'];
            if ($inflation > 10) {
                $risk += 25;
            } elseif ($inflation > 5) {
                $risk += 10;
            } elseif ($inflation < 2) {
                $risk -= 5;
            }
        }
        
        return max(0, min(100, $risk));
    }

    /**
     * Calculate weather risk
     */
    private function calculateWeatherRisk($data)
    {
        $risk = 30; // Base risk
        
        if (isset($data['weather_code'])) {
            $weatherCode = $data['weather_code'];
            
            // Severe weather codes increase risk
            if (in_array($weatherCode, [95, 96, 99])) { // Thunderstorms
                $risk += 40;
            } elseif (in_array($weatherCode, [71, 73, 75, 85, 86])) { // Snow
                $risk += 30;
            } elseif (in_array($weatherCode, [61, 63, 65, 80, 81, 82])) { // Rain
                $risk += 20;
            }
        }
        
        if (isset($data['wind_speed'])) {
            $windSpeed = $data['wind_speed'];
            if ($windSpeed > 50) {
                $risk += 30;
            } elseif ($windSpeed > 30) {
                $risk += 15;
            }
        }
        
        return max(0, min(100, $risk));
    }

    /**
     * Calculate currency risk
     */
    private function calculateCurrencyRisk($data)
    {
        $risk = 40; // Base risk
        
        if (isset($data['currency_volatility'])) {
            // Higher volatility = higher risk
            $volatility = $data['currency_volatility'];
            if ($volatility > 5) {
                $risk += 35;
            } elseif ($volatility > 2) {
                $risk += 20;
            }
        }
        
        return max(0, min(100, $risk));
    }

    /**
     * Calculate sentiment risk from news
     */
    private function calculateSentimentRisk($data)
    {
        $risk = 50; // Base risk
        
        if (isset($data['sentiment_score'])) {
            // Negative sentiment = higher risk
            $sentiment = $data['sentiment_score']; // -1 to 1
            $risk += (($sentiment * -1) * 30);
        }
        
        if (isset($data['negative_news_count'])) {
            $negativeCount = $data['negative_news_count'];
            $risk += ($negativeCount * 5);
        }
        
        return max(0, min(100, $risk));
    }

    /**
     * Get risk level category
     */
    private function getRiskLevel($score)
    {
        if ($score >= 80) {
            return 'Critical';
        } elseif ($score >= 60) {
            return 'High';
        } elseif ($score >= 40) {
            return 'Medium';
        } elseif ($score >= 20) {
            return 'Low';
        } else {
            return 'Minimal';
        }
    }

    /**
     * Get risk color for UI
     */
    public function getRiskColor($score)
    {
        if ($score >= 80) {
            return '#dc3545'; // Red
        } elseif ($score >= 60) {
            return '#fd7e14'; // Orange
        } elseif ($score >= 40) {
            return '#ffc107'; // Yellow
        } elseif ($score >= 20) {
            return '#20c997'; // Teal
        } else {
            return '#28a745'; // Green
        }
    }
}
