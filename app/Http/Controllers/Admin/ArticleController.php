<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    protected $activityLogService;
    protected $gnewsService;

    public function __construct(ActivityLogService $activityLogService, \App\Services\GNewsService $gnewsService)
    {
        $this->activityLogService = $activityLogService;
        $this->gnewsService = $gnewsService;
    }

    /**
     * Display a listing of articles with search, filter, and pagination
     */
    public function index(Request $request)
    {
        $query = Article::with('user');
        
        // Search by title or content
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter by author
        if ($request->has('author') && $request->author != '') {
            $query->where('user_id', $request->author);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $articles = $query->paginate(20)->withQueryString();
        
        // Get list of authors for filter
        $authors = \App\Models\User::select('id', 'name')
            ->whereIn('id', Article::distinct()->pluck('user_id'))
            ->orderBy('name')
            ->get();
        
        return view('admin.articles.index', compact('articles', 'authors'));
    }

    /**
     * Show the form for creating a new article
     */
    public function create()
    {
        return view('admin.articles.create');
    }

    /**
     * Store a newly created article in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url' => 'nullable|url',
            'source' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:logistics,economy,geopolitics,weather',
            'status' => 'required|in:draft,published',
        ]);

        // Set user_id to current admin
        $validated['user_id'] = Auth::id();

        // Set published_at if status is published
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }
        
        // Auto-generate description from content if not provided
        if (empty($validated['description'])) {
            $validated['description'] = substr(strip_tags($validated['content']), 0, 300);
        }
        
        // Analyze sentiment
        $sentimentService = app(\App\Services\SentimentAnalysisService::class);
        $text = $validated['title'] . ' ' . $validated['description'];
        $sentiment = $sentimentService->analyzeSentiment($text);
        
        $validated['sentiment'] = $sentiment['sentiment'];
        $validated['sentiment_score'] = $sentiment['score'];
        $validated['sentiment_confidence'] = $sentiment['confidence'];

        $article = Article::create($validated);

        // Clear cache if article is published
        if ($validated['status'] === 'published') {
            $this->clearNewsCache();
        }

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'article_created',
            'description' => "Created new article: {$article->title} (Sentiment: {$sentiment['sentiment']})",
        ]);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article created successfully with sentiment analysis!');
    }

    /**
     * Display the specified article details
     */
    public function show(Article $article)
    {
        $article->load('user');
        return view('admin.articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified article
     */
    public function edit(Article $article)
    {
        return view('admin.articles.edit', compact('article'));
    }

    /**
     * Update the specified article in database
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url' => 'nullable|url',
            'source' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:logistics,economy,geopolitics,weather',
            'status' => 'required|in:draft,published',
        ]);

        // Update published_at when changing to published
        if ($validated['status'] === 'published' && $article->status !== 'published') {
            $validated['published_at'] = now();
        }

        // Clear published_at when changing to draft
        if ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }
        
        // Auto-generate description from content if not provided
        if (empty($validated['description'])) {
            $validated['description'] = substr(strip_tags($validated['content']), 0, 300);
        }
        
        // Re-analyze sentiment if title or description changed
        if ($article->title != $validated['title'] || $article->description != $validated['description']) {
            $sentimentService = app(\App\Services\SentimentAnalysisService::class);
            $text = $validated['title'] . ' ' . $validated['description'];
            $sentiment = $sentimentService->analyzeSentiment($text);
            
            $validated['sentiment'] = $sentiment['sentiment'];
            $validated['sentiment_score'] = $sentiment['score'];
            $validated['sentiment_confidence'] = $sentiment['confidence'];
        }

        $article->update($validated);

        // Clear cache if status changed to published
        if ($validated['status'] === 'published' && $article->status !== 'published') {
            $this->clearNewsCache();
        }

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'article_updated',
            'description' => "Updated article: {$article->title}",
        ]);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully!');
    }

    /**
     * Remove the specified article from database
     */
    public function destroy(Article $article)
    {
        $articleTitle = $article->title;
        $article->delete();

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'article_deleted',
            'description' => "Deleted article: {$articleTitle}",
        ]);

        return back()->with('success', 'Article deleted successfully!');
    }

    /**
     * Toggle article status (Publish/Unpublish)
     */
    public function toggleStatus(Article $article)
    {
        if ($article->status === 'draft') {
            $article->status = 'published';
            $article->published_at = now();
            $message = 'Article published successfully!';
            $shouldClearCache = true;
        } else {
            $article->status = 'draft';
            $article->published_at = null;
            $message = 'Article unpublished successfully!';
            $shouldClearCache = true;
        }

        $article->save();

        // Clear cache when status changes
        if ($shouldClearCache) {
            $this->clearNewsCache();
        }

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'article_status_changed',
            'description' => "Article '{$article->title}' status changed to {$article->status}",
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $article->status
        ]);
    }
    
    /**
     * Show import news page
     */
    public function import()
    {
        return view('admin.articles.import');
    }
    
    /**
     * Fetch news from GNews API
     */
    public function fetchNews(Request $request)
    {
        $query = $request->get('query', 'supply chain logistics trade');
        $limit = $request->get('limit', 20);
        
        try {
            $news = $this->gnewsService->searchNews($query, null, null, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $news
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch news: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import a news article
     */
    public function importNews(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'url' => 'required|url',
            'source' => 'nullable|string',
            'category' => 'required|in:logistics,economy,geopolitics,weather',
        ]);
        
        // Check if article with same URL already exists
        $existing = Article::where('url', $validated['url'])->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This news article has already been imported!'
            ], 422);
        }
        
        // Analyze sentiment
        $sentimentService = app(\App\Services\SentimentAnalysisService::class);
        $text = $validated['title'] . ' ' . $validated['description'];
        $sentiment = $sentimentService->analyzeSentiment($text);
        
        // Create article content with source attribution
        $content = "<p><strong>Source:</strong> {$validated['source']}</p>\n\n";
        $content .= "<p>" . nl2br(htmlspecialchars($validated['content'])) . "</p>\n\n";
        $content .= "<p><a href=\"{$validated['url']}\" target=\"_blank\">Read original article →</a></p>";
        
        $article = Article::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'url' => $validated['url'],
            'source' => $validated['source'] ?? 'Unknown',
            'content' => $content,
            'category' => $validated['category'],
            'sentiment' => $sentiment['sentiment'],
            'sentiment_score' => $sentiment['score'],
            'sentiment_confidence' => $sentiment['confidence'],
            'status' => 'draft', // Always import as draft
            'published_at' => null,
        ]);
        
        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'article_imported',
            'description' => "Imported news article: {$article->title} (Sentiment: {$sentiment['sentiment']})",
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'News article imported successfully with sentiment analysis!',
            'article_id' => $article->id,
            'sentiment' => $sentiment
        ]);
    }
    
    /**
     * Clear all news-related caches
     * Call this method after publishing, updating, or deleting articles
     */
    private function clearNewsCache()
    {
        // Clear user-facing news search caches
        $this->clearCachePattern('news_search_*');
        
        // Also clear GNews API caches for admin
        $this->clearCachePattern('gnews_search_*');
        
        \Log::info('All news caches cleared at ' . now());
    }
    
    /**
     * Helper method to clear cache by pattern
     */
    private function clearCachePattern($pattern)
    {
        try {
            // For file cache driver
            if (\Illuminate\Support\Facades\Cache::getStore() instanceof \Illuminate\Cache\FileStore) {
                $keys = glob(\Illuminate\Support\Facades\Cache::getStore()->getDirectory() . '/' . str_replace('*', '*', $pattern));
                foreach ($keys as $key) {
                    @unlink($key);
                }
            }
            
            // For Redis or other drivers that support pattern deletion
            $connection = \Illuminate\Support\Facades\Cache::getStore()->getConnection();
            if (method_exists($connection, 'keys')) {
                $keys = $connection->keys($pattern);
                foreach ($keys as $key) {
                    \Illuminate\Support\Facades\Cache::forget(str_replace('laravel_cache:', '', $key));
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to clear cache pattern: ' . $e->getMessage());
        }
    }
}
