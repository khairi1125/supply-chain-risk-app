<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SentimentAnalysisService
{
    protected $positiveWords = [];
    protected $negativeWords = [];
    
    public function __construct()
    {
        // Load sentiment words from database and cache them
        $this->loadSentimentWords();
    }
    
    /**
     * Load positive and negative words from database
     */
    protected function loadSentimentWords()
    {
        $this->positiveWords = Cache::remember('positive_words', 86400, function () {
            return DB::table('positive_words')->pluck('word')->toArray();
        });
        
        $this->negativeWords = Cache::remember('negative_words', 86400, function () {
            return DB::table('negative_words')->pluck('word')->toArray();
        });
    }
    
    /**
     * Analyze sentiment of a single text
     * 
     * @param string $text
     * @return array ['sentiment' => 'positive|neutral|negative', 'score' => float, 'positive_count' => int, 'negative_count' => int]
     */
    public function analyzeSentiment($text)
    {
        if (empty($text)) {
            return [
                'sentiment' => 'neutral',
                'score' => 0,
                'positive_count' => 0,
                'negative_count' => 0,
                'confidence' => 0
            ];
        }
        
        // Convert to lowercase and split into words
        $text = strtolower($text);
        $words = $this->tokenize($text);
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        // Count positive and negative words
        foreach ($words as $word) {
            if (in_array($word, $this->positiveWords)) {
                $positiveCount++;
            }
            if (in_array($word, $this->negativeWords)) {
                $negativeCount++;
            }
        }
        
        // Calculate sentiment
        $totalSentimentWords = $positiveCount + $negativeCount;
        
        if ($totalSentimentWords === 0) {
            return [
                'sentiment' => 'neutral',
                'score' => 0,
                'positive_count' => 0,
                'negative_count' => 0,
                'confidence' => 0
            ];
        }
        
        // Calculate sentiment score (-1 to 1)
        $sentimentScore = ($positiveCount - $negativeCount) / $totalSentimentWords;
        
        // Determine sentiment category
        if ($sentimentScore > 0.2) {
            $sentiment = 'positive';
        } elseif ($sentimentScore < -0.2) {
            $sentiment = 'negative';
        } else {
            $sentiment = 'neutral';
        }
        
        // Calculate confidence (0-100)
        $confidence = min(100, ($totalSentimentWords / count($words)) * 100);
        
        return [
            'sentiment' => $sentiment,
            'score' => round($sentimentScore, 3),
            'positive_count' => $positiveCount,
            'negative_count' => $negativeCount,
            'confidence' => round($confidence, 1)
        ];
    }
    
    /**
     * Analyze sentiment of multiple texts (e.g., news articles)
     * 
     * @param array $texts
     * @return array ['overall_sentiment' => string, 'positive' => int, 'neutral' => int, 'negative' => int, 'articles' => array]
     */
    public function analyzeBatch($texts)
    {
        $results = [];
        $sentimentCounts = [
            'positive' => 0,
            'neutral' => 0,
            'negative' => 0
        ];
        
        foreach ($texts as $text) {
            $analysis = $this->analyzeSentiment($text);
            $results[] = $analysis;
            $sentimentCounts[$analysis['sentiment']]++;
        }
        
        // Determine overall sentiment
        $total = count($texts);
        $overallSentiment = 'neutral';
        
        if ($sentimentCounts['positive'] > $sentimentCounts['negative'] && 
            $sentimentCounts['positive'] > $total * 0.4) {
            $overallSentiment = 'positive';
        } elseif ($sentimentCounts['negative'] > $sentimentCounts['positive'] && 
                  $sentimentCounts['negative'] > $total * 0.4) {
            $overallSentiment = 'negative';
        }
        
        return [
            'overall_sentiment' => $overallSentiment,
            'positive' => $sentimentCounts['positive'],
            'neutral' => $sentimentCounts['neutral'],
            'negative' => $sentimentCounts['negative'],
            'total' => $total,
            'positive_percentage' => $total > 0 ? round(($sentimentCounts['positive'] / $total) * 100, 1) : 0,
            'neutral_percentage' => $total > 0 ? round(($sentimentCounts['neutral'] / $total) * 100, 1) : 0,
            'negative_percentage' => $total > 0 ? round(($sentimentCounts['negative'] / $total) * 100, 1) : 0,
            'articles' => $results
        ];
    }
    
    /**
     * Get sentiment risk score (0-100) for risk calculation
     * Positive news = low risk, Negative news = high risk
     * 
     * @param string $overallSentiment
     * @param int $positiveCount
     * @param int $negativeCount
     * @return float
     */
    public function getSentimentRiskScore($overallSentiment, $positiveCount, $negativeCount, $neutralCount = 0)
    {
        $total = $positiveCount + $negativeCount + $neutralCount;
        
        if ($total === 0) {
            return 50; // Default neutral risk
        }
        
        // Calculate weighted risk
        // Positive news = low risk (20)
        // Neutral news = medium risk (45)
        // Negative news = high risk (75)
        // Very negative = critical risk (90)
        
        $negativeRatio = $negativeCount / $total;
        $positiveRatio = $positiveCount / $total;
        
        if ($negativeRatio >= 0.6) {
            return 90; // Very negative - critical risk
        } elseif ($negativeRatio >= 0.4) {
            return 75; // Negative dominant - high risk
        } elseif ($positiveRatio >= 0.5) {
            return 20; // Positive dominant - low risk
        } else {
            return 45; // Neutral - medium risk
        }
    }
    
    /**
     * Tokenize text into words
     * 
     * @param string $text
     * @return array
     */
    protected function tokenize($text)
    {
        // Remove punctuation and split by whitespace
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        return array_map('trim', $words);
    }
    
    /**
     * Get sentiment display color
     * 
     * @param string $sentiment
     * @return string
     */
    public function getSentimentColor($sentiment)
    {
        return match($sentiment) {
            'positive' => 'success',
            'negative' => 'danger',
            default => 'secondary'
        };
    }
    
    /**
     * Get sentiment icon
     * 
     * @param string $sentiment
     * @return string
     */
    public function getSentimentIcon($sentiment)
    {
        return match($sentiment) {
            'positive' => '👍',
            'negative' => '👎',
            default => '➖'
        };
    }
}
