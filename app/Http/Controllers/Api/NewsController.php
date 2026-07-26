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
     * Search news with sentiment analysis (for News Dashboard - User Facing)
     *
     * Alur kerja (BENAR):
     * 1. Cek apakah tabel news_cache punya data segar (< 6 jam)
     * 2. Jika ADA → langsung kembalikan dari cache + hitung sentimen
     * 3. Jika TIDAK ADA / USANG → panggil GNewsService (fetch dari GNews API)
     *    → GNewsService simpan hasilnya ke tabel news_cache
     *    → Jalankan SentimentAnalysisService pada setiap artikel
     *    → Simpan sentimen ke news_cache
     *    → Kembalikan hasilnya ke frontend
     */
    public function searchNews(Request $request)
    {
        $query    = $request->input('q', '');
        $limit    = min($request->input('limit', 20), 50);
        $forceRefresh = (bool) $request->input('force_refresh', false);

        try {
            // ─── Tentukan topik pencarian ─────────────────────────────────────────
            $baseKeywords = '"supply chain" OR logistics OR trade OR shipping';

            // Kalau user ketik nama negara, tambahkan konteks supply chain
            if (!empty($query)) {
                $searchTopic = "\"{$query}\" AND ({$baseKeywords})";
                $cacheKey    = 'country_' . strtolower(trim($query));
            } else {
                $searchTopic = $baseKeywords;
                $cacheKey    = 'global';
            }

            // ─── Cek cache KHUSUS untuk query ini ────────────────────────────────
            // Setiap query (negara/topik) punya slot cache tersendiri di tabel.
            // Ini memastikan "Indonesia" tidak memunculkan hasil cache "GLOBAL".
            $freshCacheCount = DB::table('news_cache')
                ->where('cache_key', $cacheKey)
                ->where('created_at', '>=', now()->subHours(6))
                ->count();

            // ─── Fetch dari API jika cache kosong/usang ATAU force refresh ────────
            if ($freshCacheCount < 3 || $forceRefresh) {
                \Log::info("NewsController@searchNews: cache miss for key='{$cacheKey}'. Fetching from API: '{$searchTopic}'");

                // Hapus cache lama untuk query ini saja
                DB::table('news_cache')->where('cache_key', $cacheKey)->delete();

                $fetchedArticles = $this->gNewsService->searchNews(
                    $searchTopic,
                    null, null, $limit,
                    strtoupper($cacheKey)
                );

                if (!empty($fetchedArticles)) {
                    $sentimentService = app(\App\Services\SentimentAnalysisService::class);

                    foreach ($fetchedArticles as $art) {
                        $text      = ($art['title'] ?? '') . ' ' . ($art['description'] ?? '');
                        $sentiment = $sentimentService->analyzeSentiment($text);

                        DB::table('news_cache')->insert([
                            'cache_key'      => $cacheKey,
                            'country_code'   => !empty($query) ? strtoupper(substr($query, 0, 3)) : 'GLO',
                            'title'          => $art['title'] ?? '',
                            'description'    => $art['description'] ?? '',
                            'url'            => $art['url'] ?? '',
                            'source'         => $art['source'] ?? 'Unknown',
                            'image_url'      => $art['image_url'] ?? null,
                            'category'       => $art['category'] ?? 'logistics',
                            'sentiment'      => $sentiment['sentiment'],
                            'positive_score' => $sentiment['positive_count'] ?? 0,
                            'negative_score' => $sentiment['negative_count'] ?? 0,
                            'published_at'   => isset($art['published_at'])
                                ? \Carbon\Carbon::parse($art['published_at'])->format('Y-m-d H:i:s')
                                : now()->format('Y-m-d H:i:s'),
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }

                    \Log::info("NewsController: Saved " . count($fetchedArticles) . " articles for cache_key='{$cacheKey}'");
                }
            } else {
                \Log::info("NewsController@searchNews: cache hit for key='{$cacheKey}' ({$freshCacheCount} items)");
            }

            // ─── Ambil data dari tabel news_cache (khusus cache_key ini) ──────────
            $cachedNews = DB::table('news_cache')
                ->select('id', 'title', 'description', 'url', 'source', 'sentiment',
                         'positive_score', 'negative_score', 'published_at', 'country_code',
                         'image_url', 'category')
                ->where('cache_key', $cacheKey)
                ->where('created_at', '>=', now()->subHours(6))
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();

            // ─── Kembalikan data kosong jika tidak ada berita ──────────────────────
            if ($cachedNews->isEmpty()) {
                \Log::info('NewsController@searchNews: No news found for query: ' . $query);
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'articles' => [],
                        'sentiment_analysis' => [
                            'positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0,
                            'positive_percentage' => 0, 'neutral_percentage' => 0, 'negative_percentage' => 0,
                            'overall_sentiment' => 'neutral'
                        ]
                    ],
                    'source'  => 'api',
                    'message' => 'No supply chain news found for this country today.',
                ]);
            }

            // ─── Format hasil untuk dikirim ke frontend ───────────────────────
            $formattedArticles = $cachedNews->map(function ($item) {
                // Konversi score ke confidence percentage (estimasi)
                $total = ($item->positive_score ?? 0) + ($item->negative_score ?? 0);
                $confidence = $total > 0
                    ? round((max($item->positive_score, $item->negative_score) / $total) * 100, 1)
                    : 70; // default confidence

                // Hitung sentiment_score (-1 to 1)
                $sentimentScore = $total > 0
                    ? round(($item->positive_score - $item->negative_score) / $total, 3)
                    : 0;

                return [
                    'title'                => $item->title,
                    'description'          => $item->description ?? 'No description available.',
                    'url'                  => $item->url ?? '#',
                    'source'               => $item->source ?? 'Unknown',
                    'published_at'         => $item->published_at,
                    'category'             => $item->category ?? 'logistics',
                    'image_url'            => $item->image_url ?? null,
                    'sentiment'            => $item->sentiment,
                    'sentiment_score'      => $sentimentScore,
                    'sentiment_confidence' => $confidence,
                ];
            })->toArray();

            // ─── Hitung statistik sentimen ────────────────────────────────────
            $sentimentCounts = collect($formattedArticles)->countBy('sentiment');
            $total           = count($formattedArticles);
            $positive        = $sentimentCounts['positive'] ?? 0;
            $negative        = $sentimentCounts['negative'] ?? 0;
            $neutral         = $sentimentCounts['neutral'] ?? 0;

            $overallSentiment = 'neutral';
            if ($positive > $negative && $positive > $neutral) {
                $overallSentiment = 'positive';
            } elseif ($negative > $positive && $negative > $neutral) {
                $overallSentiment = 'negative';
            }

            $sentimentAnalysis = [
                'overall_sentiment'    => $overallSentiment,
                'positive'             => $positive,
                'neutral'              => $neutral,
                'negative'             => $negative,
                'total'                => $total,
                'positive_percentage'  => $total > 0 ? round(($positive / $total) * 100, 1) : 0,
                'neutral_percentage'   => $total > 0 ? round(($neutral / $total) * 100, 1) : 0,
                'negative_percentage'  => $total > 0 ? round(($negative / $total) * 100, 1) : 0,
            ];

            return response()->json([
                'success' => true,
                'data'    => [
                    'articles'          => $formattedArticles,
                    'sentiment_analysis' => $sentimentAnalysis,
                    'source'            => 'news_cache',
                ],
                'query'  => $query,
                'limit'  => $limit,
                'cached' => true,
            ]);

        } catch (\Exception $e) {
            \Log::error('NewsController@searchNews Error: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'data'    => [
                    'articles' => [],
                    'sentiment_analysis' => [
                        'positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0,
                        'positive_percentage' => 0, 'neutral_percentage' => 0, 'negative_percentage' => 0,
                        'overall_sentiment' => 'neutral'
                    ]
                ],
                'message' => 'An error occurred: ' . (config('app.debug') ? $e->getMessage() : 'Please try again.'),
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
    private function getMockNewsData($query = '')
    {
        $countryName = !empty($query) ? $query : 'Global';
        
        return [
            [
                'title' => $countryName . ' Supply Chain Shows Strong Recovery Signs',
                'description' => 'Recent data indicates robust improvement in supply chain operations across ' . $countryName . '. Logistics networks are stabilizing with reduced congestion at major distribution hubs...',
                'url' => 'https://example.com/news/1',
                'source' => 'Supply Chain Today',
                'published_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
                'category' => 'logistics',
                'sentiment' => 'positive',
                'sentiment_score' => 0.75,
                'sentiment_confidence' => 88
            ],
            [
                'title' => 'Trade Volume in ' . $countryName . ' Increases by 15% This Quarter',
                'description' => 'Import and export activities show significant growth as economic conditions improve. Port throughput reaches record levels...',
                'url' => 'https://example.com/news/2',
                'source' => 'Economic Times',
                'published_at' => now()->subHours(5)->format('Y-m-d H:i:s'),
                'category' => 'economy',
                'sentiment' => 'positive',
                'sentiment_score' => 0.68,
                'sentiment_confidence' => 85
            ],
            [
                'title' => 'Labor Negotiations at ' . $countryName . ' Ports Remain Ongoing',
                'description' => 'Union discussions continue regarding working conditions at major port facilities. Both parties express commitment to reaching resolution...',
                'url' => 'https://example.com/news/3',
                'source' => 'Maritime News',
                'published_at' => now()->subHours(8)->format('Y-m-d H:i:s'),
                'category' => 'logistics',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.05,
                'sentiment_confidence' => 70
            ],
            [
                'title' => 'New Technology Enhances ' . $countryName . ' Logistics Efficiency',
                'description' => 'AI-powered tracking systems are revolutionizing supply chain visibility in ' . $countryName . ', enabling real-time monitoring and predictive analytics...',
                'url' => 'https://example.com/news/4',
                'source' => 'Tech Business',
                'published_at' => now()->subHours(12)->format('Y-m-d H:i:s'),
                'category' => 'technology',
                'sentiment' => 'positive',
                'sentiment_score' => 0.82,
                'sentiment_confidence' => 92
            ],
            [
                'title' => 'Fuel Price Fluctuations Impact ' . $countryName . ' Shipping Costs',
                'description' => 'Rising energy prices create challenges for logistics operators. Industry experts recommend efficiency improvements to offset increased operational expenses...',
                'url' => 'https://example.com/news/5',
                'source' => 'Shipping Herald',
                'published_at' => now()->subHours(16)->format('Y-m-d H:i:s'),
                'category' => 'economy',
                'sentiment' => 'negative',
                'sentiment_score' => -0.45,
                'sentiment_confidence' => 78
            ],
            [
                'title' => $countryName . ' Implements Green Shipping Standards',
                'description' => 'Environmental regulations drive adoption of sustainable practices in maritime industry. Clean energy initiatives gain momentum across port facilities...',
                'url' => 'https://example.com/news/6',
                'source' => 'Green Business',
                'published_at' => now()->subHours(20)->format('Y-m-d H:i:s'),
                'category' => 'environment',
                'sentiment' => 'positive',
                'sentiment_score' => 0.72,
                'sentiment_confidence' => 86
            ],
            [
                'title' => 'Infrastructure Development in ' . $countryName . ' Accelerates',
                'description' => 'Major investments in port expansion and transportation networks support growing trade volumes. Government announces multi-year development plans...',
                'url' => 'https://example.com/news/7',
                'source' => 'Infrastructure Today',
                'published_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'category' => 'logistics',
                'sentiment' => 'positive',
                'sentiment_score' => 0.78,
                'sentiment_confidence' => 89
            ],
            [
                'title' => 'Weather Conditions Cause Temporary Delays in ' . $countryName,
                'description' => 'Adverse weather impacts shipping schedules temporarily. Operations expected to normalize within 48 hours as conditions improve...',
                'url' => 'https://example.com/news/8',
                'source' => 'Weather & Trade',
                'published_at' => now()->subDay()->subHours(4)->format('Y-m-d H:i:s'),
                'category' => 'weather',
                'sentiment' => 'neutral',
                'sentiment_score' => -0.15,
                'sentiment_confidence' => 72
            ],
            [
                'title' => 'E-commerce Growth Drives Logistics Innovation in ' . $countryName,
                'description' => 'Online retail expansion creates opportunities for logistics sector. Last-mile delivery solutions see increased investment and technological advancement...',
                'url' => 'https://example.com/news/9',
                'source' => 'Retail Insight',
                'published_at' => now()->subDay()->subHours(8)->format('Y-m-d H:i:s'),
                'category' => 'economy',
                'sentiment' => 'positive',
                'sentiment_score' => 0.65,
                'sentiment_confidence' => 84
            ],
            [
                'title' => 'Container Shortage Situation Improves in ' . $countryName,
                'description' => 'Equipment availability returns to normal levels after extended supply constraints. Industry reports improved operational flexibility...',
                'url' => 'https://example.com/news/10',
                'source' => 'Global Trade Journal',
                'published_at' => now()->subDay()->subHours(12)->format('Y-m-d H:i:s'),
                'category' => 'logistics',
                'sentiment' => 'neutral',
                'sentiment_score' => 0.25,
                'sentiment_confidence' => 75
            ]
        ];
    }
}