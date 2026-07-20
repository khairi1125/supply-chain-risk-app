<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\GNewsService;

class NewsController extends Controller
{
    protected $gNewsService;
    
    public function __construct(GNewsService $gNewsService)
    {
        $this->gNewsService = $gNewsService;
    }
    public function getNews($country_code)
    {
        // 1. Cari nama negara
        $country = DB::table('countries')->where('code', strtoupper($country_code))->first();

        if (!$country) {
            return response()->json(['success' => false, 'message' => 'Negara tidak ditemukan'], 404);
        }

        // 2. Ambil kamus kata dari database dan ubah jadi Array
        $positiveWords = DB::table('positive_words')->pluck('word')->toArray();
        $negativeWords = DB::table('negative_words')->pluck('word')->toArray();

        // 3. Tembak GNews API (Silakan daftar di gnews.io nanti untuk dapat API key asli)
        $apiKey = 'GANTI_DENGAN_API_KEY_GNEWS_NANTI'; 
        $query = urlencode($country->name . ' economy logistics');
        
        $response = Http::withoutVerifying()
            ->timeout(15)
            ->get("https://gnews.io/api/v4/search?q={$query}&lang=en&max=3&apikey={$apiKey}");

        $articles = [];

        // 4. Sistem Fallback: Jika GNews error (karena API Key salah), gunakan berita Dummy!
        if ($response->successful() && isset($response->json()['articles'])) {
            $articles = $response->json()['articles'];
        } else {
            // Berita dummy sengaja dirancang mengandung kata dari database kita
            $articles = [
                [
                    'title' => 'Economic growth and profit increase in ' . $country->name,
                    'description' => 'The economy shows stable growth, bringing profit to investors.'
                ],
                [
                    'title' => 'Unexpected disaster causes logistics delay',
                    'description' => 'A crisis and high inflation caused massive delay in shipping.'
                ],
                [
                    'title' => 'Normal day in ' . $country->name,
                    'description' => 'Just a regular news without much happening.'
                ]
            ];
        }

        // 5. Proses Artikel dengan AI Lexicon Based Sentiment Analysis buatan kita
        $analyzedArticles = [];

        // Ganti bagian looping di dalam fungsi getNews kamu dengan kode ini:

foreach ($articles as $article) {
    // 1. Gabungkan dan bersihkan teks
    $text = strtolower($article['title'] . ' ' . $article['description']);
    // Mengganti semua karakter non-huruf/angka dengan spasi
    $text = preg_replace('/[^a-z0-9]/', ' ', $text); 
    $words = explode(' ', $text);

    $positiveScore = 0;
    $negativeScore = 0;

    foreach ($words as $word) {
        $word = trim($word);
        if (empty($word)) continue;

        // Cek ke Database (ini memastikan kita tidak salah input array)
        if (DB::table('positive_words')->where('word', $word)->exists()) {
            $positiveScore++;
        }
        if (DB::table('negative_words')->where('word', $word)->exists()) {
            $negativeScore++;
        }
    }

    // Penentuan Sentimen
    $sentiment = "Neutral";
    if ($positiveScore > $negativeScore) $sentiment = "Positive";
    elseif ($negativeScore > $positiveScore) $sentiment = "Negative";

    $analyzedArticles[] = [
        'title'          => $article['title'],
        'description'    => $article['description'],
        'positive_score' => $positiveScore,
        'negative_score' => $negativeScore,
        'sentiment'      => $sentiment
    ];
}

        // 6. Kembalikan Output
        return response()->json([
            'success'      => true,
            'country'      => $country->name,
            'total_news'   => count($analyzedArticles),
            'news_data'    => $analyzedArticles
        ]);
    }
    
    /**
     * Search news with sentiment analysis (for News Dashboard)
     * OPTIMIZED VERSION: Fast loading with caching from database
     */
    public function searchNews(Request $request)
    {
        $query = $request->input('q', '');
        $limit = min($request->input('limit', 20), 50); // Max 50 for performance
        
        // Create cache key based on query parameters
        $cacheKey = 'news_search_' . md5($query . $limit);
        
        try {
            // Use cache for 5 minutes to reduce database load
            $result = cache()->remember($cacheKey, 300, function() use ($query, $limit) {
                // OPTIMIZED QUERY: Select only needed columns
                $articlesQuery = DB::table('articles')
                    ->select(
                        'id',
                        'title',
                        'description',
                        'url',
                        'source',
                        'category',
                        'sentiment',
                        'sentiment_score',
                        'sentiment_confidence',
                        'published_at'
                    )
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
                
                // Optimized search: Use FULLTEXT search if query exists
                if (!empty($query)) {
                    // Check if description column exists
                    $columns = DB::select("SHOW COLUMNS FROM articles LIKE 'description'");
                    
                    if (!empty($columns)) {
                        // Use FULLTEXT search (much faster than LIKE)
                        $articlesQuery->whereRaw(
                            "MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE)",
                            [$query]
                        );
                    } else {
                        // Fallback to LIKE search only on title
                        $articlesQuery->where('title', 'LIKE', "%{$query}%");
                    }
                }
                
                $articlesQuery->orderBy('published_at', 'desc');
                
                // Get articles
                $articles = $articlesQuery->limit($limit)->get();
                
                // If no articles found, return MOCK DATA
                if ($articles->isEmpty()) {
                    $mockArticles = $this->getMockNewsData();
                    return $this->prepareMockResponse($mockArticles);
                }
                
                // Format articles - NO PROCESSING, direct column mapping
                $formattedArticles = $articles->map(function($article) {
                    return [
                        'title' => $article->title,
                        'description' => $article->description ?? substr(strip_tags($article->content ?? ''), 0, 200) . '...',
                        'url' => $article->url ?? '#',
                        'source' => $article->source ?? 'Admin',
                        'published_at' => $article->published_at,
                        'category' => $article->category,
                        'sentiment' => $article->sentiment ?? 'neutral',
                        'sentiment_score' => $article->sentiment_score ?? 0,
                        'sentiment_confidence' => $article->sentiment_confidence ?? 0
                    ];
                })->toArray();
                
                // Calculate sentiment statistics
                $sentimentCounts = collect($formattedArticles)->countBy('sentiment');
                $total = count($formattedArticles);
                
                $positive = $sentimentCounts['positive'] ?? 0;
                $negative = $sentimentCounts['negative'] ?? 0;
                $neutral = $sentimentCounts['neutral'] ?? 0;
                
                // Determine overall sentiment
                $overallSentiment = 'neutral';
                if ($positive > $negative && $positive > $neutral) {
                    $overallSentiment = 'positive';
                } elseif ($negative > $positive && $negative > $neutral) {
                    $overallSentiment = 'negative';
                }
                
                $sentimentAnalysis = [
                    'overall_sentiment' => $overallSentiment,
                    'positive' => $positive,
                    'neutral' => $neutral,
                    'negative' => $negative,
                    'total' => $total,
                    'positive_percentage' => $total > 0 ? round(($positive / $total) * 100, 1) : 0,
                    'neutral_percentage' => $total > 0 ? round(($neutral / $total) * 100, 1) : 0,
                    'negative_percentage' => $total > 0 ? round(($negative / $total) * 100, 1) : 0
                ];
                
                return [
                    'articles' => $formattedArticles,
                    'sentiment_analysis' => $sentimentAnalysis,
                    'source' => 'database'
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'query' => $query,
                'limit' => $limit,
                'cached' => cache()->has($cacheKey)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('News search error: ' . $e->getMessage());
            
            // Return mock data on error
            return response()->json([
                'success' => true,
                'data' => $this->prepareMockResponse($this->getMockNewsData()),
                'message' => 'Error fetching news, showing demo data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }
    
    /**
     * Prepare mock response with proper structure
     */
    private function prepareMockResponse($mockArticles)
    {
        $sentimentCounts = collect($mockArticles)->countBy('sentiment');
        $total = count($mockArticles);
        
        $positive = $sentimentCounts['positive'] ?? 0;
        $negative = $sentimentCounts['negative'] ?? 0;
        $neutral = $sentimentCounts['neutral'] ?? 0;
        
        return [
            'articles' => $mockArticles,
            'sentiment_analysis' => [
                'overall_sentiment' => 'neutral',
                'positive' => $positive,
                'neutral' => $neutral,
                'negative' => $negative,
                'total' => $total,
                'positive_percentage' => $total > 0 ? round(($positive / $total) * 100, 1) : 0,
                'neutral_percentage' => $total > 0 ? round(($neutral / $total) * 100, 1) : 0,
                'negative_percentage' => $total > 0 ? round(($negative / $total) * 100, 1) : 0
            ],
            'source' => 'mock'
        ];
    }
    
    /**
     * Clear news cache
     */
    public function clearCache(Request $request)
    {
        try {
            // Clear all news cache
            $cacheKeys = cache()->get('news_cache_keys', []);
            
            foreach ($cacheKeys as $key) {
                cache()->forget($key);
            }
            
            // Also use wildcard clear (if using Redis)
            cache()->flush();
            
            \Log::info('News cache cleared by user');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error clearing cache: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get mock news data for demo purposes
     */
    private function getMockNewsData()
    {
        return [
            [
                'title' => 'Global Supply Chain Recovery Shows Positive Signs',
                'description' => 'International shipping routes are stabilizing as port congestion eases worldwide. Industry experts predict continued improvement in Q4 2026...',
                'url' => 'https://example.com/news/1',
                'source' => 'Supply Chain Today',
                'published_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
                'category' => 'Logistics',
                'sentiment' => 'positive',
                'sentiment_score' => 0.7,
                'sentiment_confidence' => 85
            ],
            [
                'title' => 'Port Strikes Cause Delays in Major Asian Hubs',
                'description' => 'Labor disputes at several key ports in Asia are causing significant shipping delays. Companies are seeking alternative routes...',
                'url' => 'https://example.com/news/2',
                'source' => 'Maritime News',
                'published_at' => now()->subHours(5)->format('Y-m-d H:i:s'),
                'category' => 'Ports',
                'sentiment' => 'negative',
                'sentiment_score' => -0.6,
                'sentiment_confidence' => 78
            ],
            [
                'title' => 'New Technology Enhances Supply Chain Visibility',
                'description' => 'AI-powered tracking systems are revolutionizing how companies monitor their supply chains in real-time...',
                'url' => 'https://example.com/news/3',
                'source' => 'Tech Business',
                'published_at' => now()->subHours(8)->format('Y-m-d H:i:s'),
                'category' => 'Technology',
                'sentiment' => 'positive',
                'sentiment_score' => 0.5,
                'sentiment_confidence' => 90
            ],
            [
                'title' => 'Fuel Prices Impact Shipping Costs',
                'description' => 'Rising fuel costs are putting pressure on shipping companies to increase freight rates...',
                'url' => 'https://example.com/news/4',
                'source' => 'Economic Times',
                'published_at' => now()->subHours(12)->format('Y-m-d H:i:s'),
                'category' => 'Economy',
                'sentiment' => 'negative',
                'sentiment_score' => -0.4,
                'sentiment_confidence' => 72
            ],
            [
                'title' => 'Green Shipping Initiatives Gain Momentum',
                'description' => 'Environmental regulations are driving shipping companies to adopt cleaner technologies and sustainable practices...',
                'url' => 'https://example.com/news/5',
                'source' => 'Green Business',
                'published_at' => now()->subHours(16)->format('Y-m-d H:i:s'),
                'category' => 'Environment',
                'sentiment' => 'positive',
                'sentiment_score' => 0.6,
                'sentiment_confidence' => 88
            ],
            [
                'title' => 'Trade War Concerns Affect Global Markets',
                'description' => 'Ongoing trade tensions between major economies are creating uncertainty in international trade...',
                'url' => 'https://example.com/news/6',
                'source' => 'Global Trade Journal',
                'published_at' => now()->subHours(20)->format('Y-m-d H:i:s'),
                'category' => 'Trade',
                'sentiment' => 'negative',
                'sentiment_score' => -0.5,
                'sentiment_confidence' => 80
            ],
            [
                'title' => 'Warehouse Automation Increases Efficiency',
                'description' => 'Automated warehousing solutions are helping companies handle increased demand while reducing costs...',
                'url' => 'https://example.com/news/7',
                'source' => 'Logistics Weekly',
                'published_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'category' => 'Logistics',
                'sentiment' => 'positive',
                'sentiment_score' => 0.8,
                'sentiment_confidence' => 92
            ],
            [
                'title' => 'Container Shortage Situation Improves',
                'description' => 'The global container shortage that plagued supply chains is showing signs of resolution...',
                'url' => 'https://example.com/news/8',
                'source' => 'Shipping Herald',
                'published_at' => now()->subDay()->subHours(4)->format('Y-m-d H:i:s'),
                'category' => 'Shipping',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.1,
                'sentiment_confidence' => 65
            ],
            [
                'title' => 'E-commerce Growth Drives Logistics Innovation',
                'description' => 'The continued expansion of e-commerce is pushing logistics companies to innovate and adapt...',
                'url' => 'https://example.com/news/9',
                'source' => 'Retail Insight',
                'published_at' => now()->subDay()->subHours(8)->format('Y-m-d H:i:s'),
                'category' => 'E-commerce',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.2,
                'sentiment_confidence' => 70
            ],
            [
                'title' => 'Weather Disruptions Affect Shipping Schedules',
                'description' => 'Severe weather in the Pacific Ocean is causing delays in major shipping routes...',
                'url' => 'https://example.com/news/10',
                'source' => 'Weather & Trade',
                'published_at' => now()->subDay()->subHours(12)->format('Y-m-d H:i:s'),
                'category' => 'Weather',
                'sentiment' => 'neutral',
                'sentiment_score' => -0.1,
                'sentiment_confidence' => 68
            ]
        ];
    }
}