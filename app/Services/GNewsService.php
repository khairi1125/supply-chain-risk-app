<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GNewsService
{
    protected $baseUrl = 'https://gnews.io/api/v4';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gnews.api_key');
    }

    /**
     * Search news articles
     */
    public function searchNews($query, $country = null, $category = null, $max = 10)
    {
        $cacheKey = "gnews_" . md5($query . $country . $category);
        
        return Cache::remember($cacheKey, 1800, function () use ($query, $country, $category, $max) {
            try {
                if (empty($this->apiKey) || $this->apiKey === 'your_key_here') {
                    return $this->getMockNews($query);
                }

                $params = [
                    'q' => $query,
                    'apikey' => $this->apiKey,
                    'max' => $max,
                    'lang' => 'en'
                ];

                if ($country) {
                    $params['country'] = $country;
                }

                if ($category) {
                    $params['category'] = $category;
                }

                $response = Http::get("{$this->baseUrl}/search", $params);

                if ($response->successful()) {
                    return $response->json();
                }

                return $this->getMockNews($query);
            } catch (\Exception $e) {
                \Log::error('GNews API Error: ' . $e->getMessage());
                return $this->getMockNews($query);
            }
        });
    }

    /**
     * Get top headlines
     */
    public function getTopHeadlines($category = 'business', $country = 'us', $max = 10)
    {
        $cacheKey = "gnews_headlines_{$category}_{$country}";
        
        return Cache::remember($cacheKey, 1800, function () use ($category, $country, $max) {
            try {
                if (empty($this->apiKey) || $this->apiKey === 'your_key_here') {
                    return $this->getMockNews('headlines');
                }

                $response = Http::get("{$this->baseUrl}/top-headlines", [
                    'category' => $category,
                    'country' => $country,
                    'apikey' => $this->apiKey,
                    'max' => $max,
                    'lang' => 'en'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return $this->getMockNews('headlines');
            } catch (\Exception $e) {
                \Log::error('GNews Headlines API Error: ' . $e->getMessage());
                return $this->getMockNews('headlines');
            }
        });
    }

    /**
     * Get mock news for development
     */
    private function getMockNews($query)
    {
        return [
            'totalArticles' => 3,
            'articles' => [
                [
                    'title' => 'Supply Chain Disruption in Asia-Pacific Region',
                    'description' => 'Major supply chain disruptions affecting global trade...',
                    'content' => 'Full article content here...',
                    'url' => 'https://example.com/news/1',
                    'image' => 'https://via.placeholder.com/400x300',
                    'publishedAt' => now()->subHours(2)->toIso8601String(),
                    'source' => [
                        'name' => 'Supply Chain News',
                        'url' => 'https://example.com'
                    ]
                ],
                [
                    'title' => 'Global Shipping Costs Rise Amid Port Congestion',
                    'description' => 'Shipping costs continue to increase due to port delays...',
                    'content' => 'Full article content here...',
                    'url' => 'https://example.com/news/2',
                    'image' => 'https://via.placeholder.com/400x300',
                    'publishedAt' => now()->subHours(5)->toIso8601String(),
                    'source' => [
                        'name' => 'Global Trade Magazine',
                        'url' => 'https://example.com'
                    ]
                ],
                [
                    'title' => 'New Technology Improves Supply Chain Visibility',
                    'description' => 'Companies adopting AI for better supply chain management...',
                    'content' => 'Full article content here...',
                    'url' => 'https://example.com/news/3',
                    'image' => 'https://via.placeholder.com/400x300',
                    'publishedAt' => now()->subHours(8)->toIso8601String(),
                    'source' => [
                        'name' => 'Tech Business Daily',
                        'url' => 'https://example.com'
                    ]
                ]
            ]
        ];
    }
}
