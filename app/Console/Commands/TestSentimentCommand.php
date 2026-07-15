<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SentimentAnalysisService;
use App\Services\GNewsService;

class TestSentimentCommand extends Command
{
    protected $signature = 'test:sentiment {country=Indonesia}';
    protected $description = 'Test sentiment analysis for a country';

    public function handle()
    {
        $country = $this->argument('country');
        
        $this->info("🧪 Testing Sentiment Analysis for: {$country}");
        $this->newLine();
        
        // Test 1: Simple text sentiment
        $this->info('1️⃣  Testing Simple Text Sentiment');
        $sentimentService = app(SentimentAnalysisService::class);
        
        $testTexts = [
            "Positive" => "Strong economic growth and stable export increase profit",
            "Negative" => "Inflation crisis and supply chain disruption cause major delays",
            "Neutral" => "The government announced a new policy regarding trade"
        ];
        
        foreach ($testTexts as $label => $text) {
            $result = $sentimentService->analyzeSentiment($text);
            $icon = $sentimentService->getSentimentIcon($result['sentiment']);
            
            $this->line("   {$label}: \"{$text}\"");
            $this->line("   {$icon} Sentiment: " . strtoupper($result['sentiment']) . 
                       " (score: {$result['score']}, confidence: {$result['confidence']}%)");
            $this->line("   Positive words: {$result['positive_count']}, Negative words: {$result['negative_count']}");
            $this->newLine();
        }
        
        // Test 2: News sentiment
        $this->info('2️⃣  Testing News Sentiment Analysis');
        $newsService = app(GNewsService::class);
        
        $newsData = $newsService->getNewsByCountryWithSentiment($country, 5);
        
        $this->info("   Found {$newsData['sentiment_analysis']['total']} articles");
        $this->line("   Overall: " . strtoupper($newsData['sentiment_analysis']['overall_sentiment']));
        $this->line("   📊 Breakdown:");
        $this->line("      ✅ Positive: {$newsData['sentiment_analysis']['positive']} ({$newsData['sentiment_analysis']['positive_percentage']}%)");
        $this->line("      ➖ Neutral: {$newsData['sentiment_analysis']['neutral']} ({$newsData['sentiment_analysis']['neutral_percentage']}%)");
        $this->line("      ❌ Negative: {$newsData['sentiment_analysis']['negative']} ({$newsData['sentiment_analysis']['negative_percentage']}%)");
        
        $this->newLine();
        $this->info('📰 Sample Articles:');
        foreach (array_slice($newsData['articles'], 0, 3) as $i => $article) {
            $icon = $sentimentService->getSentimentIcon($article['sentiment']);
            $this->line("   " . ($i + 1) . ". {$article['title']}");
            $this->line("      {$icon} {$article['sentiment']} (score: {$article['sentiment_score']})");
            $this->line("      Source: {$article['source']}");
            $this->newLine();
        }
        
        // Test 3: Risk Score
        $this->info('3️⃣  Testing News Risk Score Calculation');
        $riskScore = $newsService->getNewsSentimentRiskScore($country, 5);
        $this->line("   News Risk Score: {$riskScore}/100");
        
        if ($riskScore < 30) {
            $this->info("   ✅ Low Risk (Positive news dominant)");
        } elseif ($riskScore < 60) {
            $this->warn("   ⚠️  Medium Risk (Mixed sentiment)");
        } else {
            $this->error("   ❌ High Risk (Negative news dominant)");
        }
        
        $this->newLine();
        $this->info('✅ Sentiment analysis test completed!');
        
        return Command::SUCCESS;
    }
}
