<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GNewsService;

class TestNewsCommand extends Command
{
    protected $signature = 'news:test {query?} {--limit=10}';
    protected $description = 'Test News Intelligence API with sentiment analysis';

    public function handle()
    {
        $query = $this->argument('query') ?? 'supply chain';
        $limit = $this->option('limit');
        
        $this->info('📰 Testing News Intelligence API');
        $this->line("Query: {$query}");
        $this->line("Limit: {$limit}");
        $this->newLine();
        
        try {
            $gNewsService = app(GNewsService::class);
            $newsData = $gNewsService->getNewsByCountryWithSentiment($query, $limit);
            
            $analysis = $newsData['sentiment_analysis'];
            $articles = $newsData['articles'];
            
            // Display sentiment summary
            $this->info('📊 Sentiment Analysis Summary');
            $this->line("Overall Sentiment: " . strtoupper($analysis['overall_sentiment']));
            $this->line("Total Articles: {$analysis['total']}");
            $this->newLine();
            
            $this->table(
                ['Sentiment', 'Count', 'Percentage'],
                [
                    ['😊 Positive', $analysis['positive'], $analysis['positive_percentage'] . '%'],
                    ['😐 Neutral', $analysis['neutral'], $analysis['neutral_percentage'] . '%'],
                    ['😟 Negative', $analysis['negative'], $analysis['negative_percentage'] . '%'],
                ]
            );
            
            $this->newLine();
            $this->info('📄 Sample Articles');
            
            foreach (array_slice($articles, 0, 5) as $index => $article) {
                $this->line(($index + 1) . ". {$article['title']}");
                $this->line("   Source: {$article['source']}");
                $this->line("   Sentiment: {$article['sentiment']} (Confidence: {$article['sentiment_confidence']}%)");
                $this->newLine();
            }
            
            // Test API endpoint
            $this->info('🔗 Testing API Endpoint');
            $apiUrl = url("/api/news/search?q=" . urlencode($query) . "&limit={$limit}");
            $this->line("URL: {$apiUrl}");
            
            $response = \Http::get($apiUrl);
            
            if ($response->successful()) {
                $this->info('✅ API Endpoint Working!');
                $data = $response->json();
                $this->line("Response Success: " . ($data['success'] ? 'true' : 'false'));
                $this->line("Articles Returned: " . count($data['data']['articles']));
            } else {
                $this->error('❌ API Endpoint Failed!');
                $this->line("Status: {$response->status()}");
            }
            
            $this->newLine();
            $this->info('✅ News Intelligence testing completed!');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
