<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\SentimentAnalysisService;

class MigrateArticleDataCommand extends Command
{
    protected $signature = 'articles:migrate-data';
    protected $description = 'Migrate existing article data to new optimized structure';

    public function handle()
    {
        $this->info('Starting article data migration...');
        
        $sentimentService = app(SentimentAnalysisService::class);
        
        // Get articles that need migration (where description is null)
        $articles = DB::table('articles')
            ->whereNull('description')
            ->orWhereNull('sentiment')
            ->get();
        
        if ($articles->isEmpty()) {
            $this->info('No articles need migration.');
            return 0;
        }
        
        $this->info('Found ' . $articles->count() . ' articles to migrate.');
        $bar = $this->output->createProgressBar($articles->count());
        
        foreach ($articles as $article) {
            // Extract description from content
            $description = strip_tags($article->content);
            $description = substr($description, 0, 300);
            
            // Extract URL from content
            preg_match('/href="([^"]+)"/', $article->content, $matches);
            $url = $matches[1] ?? null;
            
            // Extract source from content
            preg_match('/<strong>Source:<\/strong>\s*([^<]+)/', $article->content, $sourceMatches);
            $source = isset($sourceMatches[1]) ? trim($sourceMatches[1]) : 'Admin';
            
            // Analyze sentiment
            $text = $article->title . ' ' . $description;
            $sentiment = $sentimentService->analyzeSentiment($text);
            
            // Update article
            DB::table('articles')
                ->where('id', $article->id)
                ->update([
                    'description' => $description,
                    'url' => $url,
                    'source' => $source,
                    'sentiment' => $sentiment['sentiment'],
                    'sentiment_score' => $sentiment['score'],
                    'sentiment_confidence' => $sentiment['confidence'],
                    'updated_at' => now()
                ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Migration completed successfully!');
        
        return 0;
    }
}
