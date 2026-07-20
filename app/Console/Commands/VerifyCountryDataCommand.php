<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GNewsService;
use App\Services\SentimentAnalysisService;
use App\Http\Controllers\Api\CountryController;

class VerifyCountryDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'country:verify {code=IDN : Country code to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that country modal data is complete with real data (not dummy)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $code = $this->argument('code');
        
        $this->line("\n=== COUNTRY DATA VERIFICATION REPORT ===");
        $this->line("Country Code: $code");
        $this->line("Time: " . now()->format('Y-m-d H:i:s'));
        $this->newLine();
        
        // 1. Check country exists
        $this->line("1️⃣  CHECKING COUNTRY DATA...");
        $country = DB::table('countries')->where('code', $code)->first();
        
        if (!$country) {
            $this->error("   ✗ Country not found in database");
            return 1;
        }
        
        $this->info("   ✓ Country found: {$country->name}");
        $this->line("   - Code: {$country->code}");
        $this->line("   - Region: {$country->region}");
        $this->line("   - Currency: {$country->currency_code}");
        $this->newLine();
        
        // 2. Check sentiment words
        $this->line("2️⃣  CHECKING SENTIMENT WORD DICTIONARIES...");
        $positiveCount = DB::table('positive_words')->count();
        $negativeCount = DB::table('negative_words')->count();
        
        if ($positiveCount > 0) {
            $this->info("   ✓ Positive words: $positiveCount");
        } else {
            $this->error("   ✗ No positive words in database (need to run: php artisan db:seed --class=SentimentWordsSeeder)");
        }
        
        if ($negativeCount > 0) {
            $this->info("   ✓ Negative words: $negativeCount");
        } else {
            $this->error("   ✗ No negative words in database (need to run: php artisan db:seed --class=SentimentWordsSeeder)");
        }
        $this->newLine();
        
        // 3. Check GNews API key
        $this->line("3️⃣  CHECKING GNews API CONFIGURATION...");
        $apiKey = config('services.gnews.api_key');
        
        if (empty($apiKey) || $apiKey === 'your_key_here' || strlen($apiKey) < 10) {
            $this->error("   ✗ GNews API key not configured or invalid");
            $this->line("   Please set GNEWS_API_KEY in .env file");
        } else {
            $this->info("   ✓ GNews API key configured");
            $this->line("   - Key: " . substr($apiKey, 0, 10) . "...");
        }
        $this->newLine();
        
        // 4. Test news fetching
        $this->line("4️⃣  TESTING NEWS FETCHING...");
        try {
            $newsService = app(GNewsService::class);
            $newsData = $newsService->getNewsByCountry($country->name, 5);
            
            if (empty($newsData)) {
                $this->error("   ✗ No news articles fetched (using mock data)");
            } else {
                $this->info("   ✓ News articles fetched: " . count($newsData));
                foreach ($newsData as $idx => $article) {
                    $this->line("   Article " . ($idx + 1) . ": " . substr($article['title'], 0, 50) . "...");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Error fetching news: " . $e->getMessage());
        }
        $this->newLine();
        
        // 5. Test sentiment analysis
        $this->line("5️⃣  TESTING SENTIMENT ANALYSIS...");
        try {
            $sentimentService = app(SentimentAnalysisService::class);
            
            if ($positiveCount > 0 && $negativeCount > 0) {
                $testText = "Great supply chain growth with strong partnerships and positive momentum";
                $sentiment = $sentimentService->analyzeSentiment($testText);
                
                $this->info("   ✓ Sentiment analysis working");
                $this->line("   - Test text: \"" . $testText . "\"");
                $this->line("   - Result: " . $sentiment['sentiment']);
                $this->line("   - Score: " . $sentiment['score']);
                $this->line("   - Confidence: " . $sentiment['confidence'] . "%");
            } else {
                $this->error("   ✗ Cannot test sentiment analysis (no word dictionaries)");
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Error testing sentiment: " . $e->getMessage());
        }
        $this->newLine();
        
        // 6. Test full API response
        $this->line("6️⃣  TESTING FULL API RESPONSE...");
        try {
            $controller = app(CountryController::class);
            $response = $controller->show($code);
            $data = $response->getData(true);
            
            $checks = [
                'country' => 'Country info',
                'weather' => 'Weather data',
                'economic' => 'Economic data',
                'currency' => 'Currency data',
                'news' => 'News & sentiment',
                'risk' => 'Risk scores'
            ];
            
            foreach ($checks as $key => $label) {
                if (isset($data[$key]) && !empty($data[$key])) {
                    $this->info("   ✓ $label present");
                    
                    // Check news articles
                    if ($key === 'news' && isset($data[$key]['articles'])) {
                        $articleCount = count($data[$key]['articles']);
                        $this->line("      - Articles: $articleCount");
                        
                        if (isset($data[$key]['sentiment'])) {
                            $sentiment = $data[$key]['sentiment'];
                            $this->line("      - Overall sentiment: " . ($sentiment['overall_sentiment'] ?? 'N/A'));
                            $this->line("      - Positive: {$sentiment['positive']}, Neutral: {$sentiment['neutral']}, Negative: {$sentiment['negative']}");
                        }
                    }
                } else {
                    $this->error("   ✗ $label missing or empty");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Error calling API: " . $e->getMessage());
        }
        $this->newLine();
        
        // Summary
        $this->line("=== VERIFICATION COMPLETE ===");
        $this->line("All data sources checked. Review any ✗ marks above for issues.\n");
        
        return 0;
    }
}
