<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GNewsService
{
    protected $baseUrl = 'https://gnews.io/api/v4';
    protected $apiKey;
    protected $sentimentService;

    public function __construct(SentimentAnalysisService $sentimentService)
    {
        $this->apiKey = config('services.gnews.api_key');
        $this->sentimentService = $sentimentService;
    }

    /**
     * Get news by country with sentiment analysis
     */
    public function getNewsByCountryWithSentiment($countryName, $limit = 10)
    {
        $articles = $this->getNewsByCountry($countryName, $limit);
        
        if (empty($articles)) {
            return [
                'articles' => [],
                'sentiment_analysis' => [
                    'overall_sentiment' => 'neutral',
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0,
                    'total' => 0,
                    'positive_percentage' => 0,
                    'neutral_percentage' => 0,
                    'negative_percentage' => 0
                ]
            ];
        }
        
        // Analyze sentiment for each article
        $analyzedArticles = [];
        foreach ($articles as $article) {
            $text = ($article['title'] ?? '') . ' ' . ($article['description'] ?? '');
            $sentiment = $this->sentimentService->analyzeSentiment($text);
            
            $analyzedArticles[] = array_merge($article, [
                'sentiment' => $sentiment['sentiment'],
                'sentiment_score' => $sentiment['score'],
                'sentiment_confidence' => $sentiment['confidence']
            ]);
        }
        
        // Get batch sentiment analysis
        $texts = array_map(function($article) {
            return ($article['title'] ?? '') . ' ' . ($article['description'] ?? '');
        }, $articles);
        
        $batchAnalysis = $this->sentimentService->analyzeBatch($texts);
        
        return [
            'articles' => $analyzedArticles,
            'sentiment_analysis' => $batchAnalysis
        ];
    }
    
    /**
     * Get news sentiment score for risk calculation
     */
    public function getNewsSentimentRiskScore($countryName, $limit = 10, $forceRefresh = false)
    {
        $newsData = $this->getNewsByCountryWithSentiment($countryName, $limit, $forceRefresh);
        $analysis = $newsData['sentiment_analysis'];
        
        return $this->getSentimentRiskScoreFromAnalysis($analysis);
    }
    
    /**
     * Calculate risk score from sentiment analysis result (avoid re-fetching)
     */
    public function getSentimentRiskScoreFromAnalysis($analysis)
    {
        return $this->sentimentService->getSentimentRiskScore(
            $analysis['overall_sentiment'],
            $analysis['positive'],
            $analysis['negative'],
            $analysis['neutral']
        );
    }
    
    /**
     * Get news by country with database caching (without sentiment)
     * Cache expires after 6 hours or can be force refreshed
     */
    public function getNewsByCountry($countryName, $limit = 10, $forceRefresh = false)
    {
        // IMPROVED QUERY: Use broader keywords for better coverage
        // Old: "Indonesia logistics OR trade OR shipping OR economy"
        // New: Just country name gets more results from GNews
        $query = $countryName;
        
        // Check cache in database first (valid for 6 hours)
        // News updates every 6 hours - balance between freshness and API quota
        $countryCode = strtoupper(substr($countryName, 0, 3));
        
        // IMPROVEMENT: Clear cache yang lebih tua dari 6 jam
        \DB::table('news_cache')
            ->where('country_code', $countryCode)
            ->where('created_at', '<', now()->subHours(6))
            ->delete();
        
        // Count how many cached articles we have for this country  
        $cachedCount = \DB::table('news_cache')
            ->where('country_code', $countryCode)
            ->where('created_at', '>=', now()->subHours(6))
            ->count();
        
        // If cache has enough articles AND not force refresh, use cache
        if ($cachedCount >= $limit && !$forceRefresh) {
            Log::info("GNews: Using cached news for {$countryName} (cached {$cachedCount} articles)");
            
            $cached = \DB::table('news_cache')
                ->where('country_code', $countryCode)
                ->where('created_at', '>=', now()->subHours(6))
                ->orderBy('published_at', 'desc')  // Sort by published date, not cache date
                ->limit($limit)
                ->get();
                
            return $cached->map(function ($item) {
                return [
                    'title' => $item->title,
                    'description' => $item->description,
                    'url' => $item->url,
                    'source' => $item->source,
                    'published_at' => $item->published_at,
                ];
            })->toArray();
        }
        
        // Cache doesn't have enough articles OR force refresh → fetch from API
        Log::info("GNews: Fetching FRESH news from API for {$countryName}" . ($forceRefresh ? " (force refresh)" : " (cache empty)"));
        
        // Clear old cache before fetching new
        if ($forceRefresh) {
            \DB::table('news_cache')->where('country_code', $countryCode)->delete();
        }
        
        return $this->searchNews($query, null, null, $limit, $countryName);
    }

    /**
     * Get general news about a topic
     */
    public function getNewsGeneral($topic = 'supply chain', $limit = 10)
    {
        $query = "{$topic} logistics trade shipping";
        return $this->searchNews($query, null, null, $limit);
    }

    /**
     * Search news articles with caching
     */
    public function searchNews($query, $country = null, $category = null, $max = 10, $cacheCountryName = null)
    {
        try {
            // Create cache key for API results (30 menit cache untuk admin fetches)
            $cacheKey = 'gnews_search_' . md5($query . $country . $category . $max);
            
            // Check if we have cached API results (30 minutes TTL)
            $cachedResults = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($cachedResults) {
                Log::info('GNews API: Using cached results for query: ' . $query);
                return $cachedResults;
            }
            
            if (empty($this->apiKey) || $this->apiKey === 'your_key_here' || strlen($this->apiKey) < 10) {
                Log::warning('GNews API: Using mock data (invalid or missing API key)');
                return $this->getMockNews($query);
            }

            $params = [
                'q' => $query,
                'apikey' => $this->apiKey,
                'max' => $max,
                'lang' => 'en',
                'sortby' => 'publishedAt'
            ];

            if ($country) {
                $params['country'] = $country;
            }

            if ($category) {
                $params['category'] = $category;
            }

            Log::info('GNews API: Fetching news for query: ' . $query);
            $response = Http::withOptions(['verify' => false])->timeout(15)->get("{$this->baseUrl}/search", $params);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check for API errors
                if (isset($data['errors'])) {
                    Log::error('GNews API Error: ' . json_encode($data['errors']));
                    return $this->getMockNews($query);
                }
                
                $articles = $data['articles'] ?? [];
                
                // Map articles to standard format
                $formattedArticles = collect($articles)->map(function ($article) {
                    return [
                        'title' => $article['title'] ?? '',
                        'description' => $article['description'] ?? '',
                        'url' => $article['url'] ?? '',
                        'source' => $article['source']['name'] ?? 'Unknown',
                        'published_at' => $article['publishedAt'] ?? now()->toIso8601String(),
                    ];
                })->toArray();
                
                // Cache API results for 30 minutes to prevent excessive API calls
                \Illuminate\Support\Facades\Cache::put($cacheKey, $formattedArticles, 30 * 60);
                
                // Also cache articles to database for long-term storage
                if ($cacheCountryName && !empty($formattedArticles)) {
                    $this->cacheNews($articles, $cacheCountryName);
                }
                
                return $formattedArticles;
            }

            Log::error('GNews API failed', ['status' => $response->status()]);
            return $this->getMockNews($query);
        } catch (\Exception $e) {
            Log::error('GNews API Error: ' . $e->getMessage());
            return $this->getMockNews($query);
        }
    }

    /**
     * Get top headlines
     */
    public function getTopHeadlines($category = 'business', $country = 'us', $max = 10)
    {
        try {
            if (empty($this->apiKey) || $this->apiKey === 'your_key_here') {
                return $this->getMockNews('headlines');
            }

            $response = Http::withOptions(['verify' => false])->timeout(15)->get("{$this->baseUrl}/top-headlines", [
                'category' => $category,
                'country' => $country,
                'apikey' => $this->apiKey,
                'max' => $max,
                'lang' => 'en'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['articles'] ?? [];
            }

            return $this->getMockNews('headlines');
        } catch (\Exception $e) {
            Log::error('GNews Headlines API Error: ' . $e->getMessage());
            return $this->getMockNews('headlines');
        }
    }
    
    /**
     * Cache news to database
     */
    private function cacheNews($articles, $countryName)
    {
        try {
            $countryCode = strtoupper(substr($countryName, 0, 3));
            
            // Clear old cache for this country first (avoid duplicates)
            \DB::table('news_cache')
                ->where('country_code', $countryCode)
                ->where('created_at', '<', now()->subHours(6))
                ->delete();
            
            foreach ($articles as $article) {
                \DB::table('news_cache')->insert([
                    'country_code' => $countryCode,
                    'title' => $article['title'] ?? '',
                    'description' => $article['description'] ?? '',
                    'url' => $article['url'] ?? '',
                    'source' => $article['source']['name'] ?? 'Unknown',
                    'sentiment' => 'neutral', // Will be analyzed later
                    'published_at' => isset($article['publishedAt']) ? \Carbon\Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cache news: ' . $e->getMessage());
        }
    }

    /**
     * Get mock news for development
     */
    private function getMockNews($query)
    {
        return [
            [
                'title' => 'Supply Chain Disruption in Asia-Pacific Region',
                'description' => 'Major supply chain disruptions affecting global trade due to port congestion and shipping delays...',
                'url' => 'https://example.com/news/1',
                'source' => 'Supply Chain News',
                'published_at' => now()->subHours(2)->toIso8601String(),
            ],
            [
                'title' => 'Global Shipping Costs Rise Amid Port Congestion',
                'description' => 'Shipping costs continue to increase due to port delays and container shortages worldwide...',
                'url' => 'https://example.com/news/2',
                'source' => 'Global Trade Magazine',
                'published_at' => now()->subHours(5)->toIso8601String(),
            ],
            [
                'title' => 'New Technology Improves Supply Chain Visibility',
                'description' => 'Companies are adopting AI and blockchain technology for better supply chain management and tracking...',
                'url' => 'https://example.com/news/3',
                'source' => 'Tech Business Daily',
                'published_at' => now()->subHours(8)->toIso8601String(),
            ],
        ];
    }
}
