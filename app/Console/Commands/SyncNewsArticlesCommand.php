<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\GNewsService;
use Illuminate\Console\Command;

class SyncNewsArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:sync {--limit=20 : Number of articles to fetch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync news articles from GNews API to database';

    protected $gnewsService;

    public function __construct(GNewsService $gnewsService)
    {
        parent::__construct();
        $this->gnewsService = $gnewsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting news sync...');
        
        $limit = $this->option('limit');
        $query = 'supply chain logistics trade economy';
        
        try {
            // Fetch news from GNews API
            $this->info("Fetching {$limit} news articles from GNews API...");
            $news = $this->gnewsService->searchNews($query, null, null, $limit);
            
            if (empty($news)) {
                $this->warn('No news articles found.');
                return Command::FAILURE;
            }
            
            $this->info("Found " . count($news) . " articles. Starting import...");
            
            $imported = 0;
            $skipped = 0;
            $admin = \App\Models\User::where('role', 'admin')->first();
            
            if (!$admin) {
                $this->error('No admin user found. Please create an admin user first.');
                return Command::FAILURE;
            }
            
            foreach ($news as $article) {
                // Check if already imported (by URL)
                $existing = Article::where('content', 'LIKE', '%' . $article['url'] . '%')->first();
                
                if ($existing) {
                    $skipped++;
                    $this->warn("Skipped (already exists): {$article['title']}");
                    continue;
                }
                
                // Auto-categorize based on title and description
                $category = $this->categorizeArticle($article['title'], $article['description']);
                
                // Create article content
                $content = "<p><strong>Source:</strong> {$article['source']}</p>\n\n";
                $content .= "<p>" . nl2br(htmlspecialchars($article['description'])) . "</p>\n\n";
                $content .= "<p><a href=\"{$article['url']}\" target=\"_blank\">Read original article →</a></p>";
                
                // Create article
                Article::create([
                    'user_id' => $admin->id,
                    'title' => $article['title'],
                    'content' => $content,
                    'category' => $category,
                    'status' => 'draft', // Always import as draft
                    'published_at' => null,
                ]);
                
                $imported++;
                $this->info("✓ Imported: {$article['title']} (Category: {$category})");
            }
            
            $this->newLine();
            $this->info("Sync completed!");
            $this->info("✓ Imported: {$imported} articles");
            $this->info("⊗ Skipped: {$skipped} articles (already exist)");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error syncing news: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Auto-categorize article based on keywords
     */
    private function categorizeArticle($title, $description)
    {
        $text = strtolower($title . ' ' . $description);
        
        // Keywords for each category
        $categories = [
            'logistics' => ['logistics', 'shipping', 'port', 'cargo', 'freight', 'transport', 'delivery', 'warehouse'],
            'geopolitics' => ['war', 'politics', 'government', 'conflict', 'sanction', 'trade war', 'diplomatic', 'military'],
            'weather' => ['weather', 'climate', 'storm', 'hurricane', 'flood', 'drought', 'disaster', 'typhoon'],
            'economy' => ['economy', 'market', 'price', 'inflation', 'trade', 'finance', 'investment', 'currency'],
        ];
        
        $scores = [];
        foreach ($categories as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $score++;
                }
            }
            $scores[$category] = $score;
        }
        
        // Return category with highest score, default to logistics
        arsort($scores);
        $topCategory = array_key_first($scores);
        
        return $scores[$topCategory] > 0 ? $topCategory : 'logistics';
    }
}
