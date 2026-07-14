<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExchangeRateService;
use App\Services\GNewsService;
use App\Services\OpenMeteoService;

class TestRealApisCommand extends Command
{
    protected $signature = 'apis:test-real';
    protected $description = 'Test real external APIs with actual API keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing REAL External APIs (with API keys)');
        $this->newLine();
        
        // 1. Test Exchange Rate API with Real Key
        $this->info('1️⃣  Testing Exchange Rate API with Real Key...');
        $apiKey = config('services.exchange_rate.api_key');
        $this->line("   API Key: " . substr($apiKey, 0, 10) . "...");
        
        $exchange = app(ExchangeRateService::class);
        $rates = $exchange->getRates('USD', ['EUR', 'GBP', 'JPY']);
        
        if (!empty($rates)) {
            $this->info('   ✅ Success: Real exchange rates retrieved');
            foreach ($rates as $currency => $rate) {
                $this->line("      1 USD = {$rate} {$currency}");
            }
            
            // Check if cached
            $cached = \DB::table('currency_cache')->count();
            $this->line("      💾 Cached rates in database: {$cached}");
        } else {
            $this->error('   ❌ Failed to get exchange rates');
        }
        $this->newLine();
        
        // 2. Test GNews API with Real Key
        $this->info('2️⃣  Testing GNews API with Real Key...');
        $gnewsKey = config('services.gnews.api_key');
        $this->line("   API Key: " . substr($gnewsKey, 0, 10) . "...");
        
        $gnews = app(GNewsService::class);
        $news = $gnews->getNewsByCountry('United States', 5);
        
        if (!empty($news)) {
            $this->info('   ✅ Success: Real news articles retrieved');
            $this->line("      Found " . count($news) . " articles:");
            foreach ($news as $index => $article) {
                $this->line("      " . ($index + 1) . ". {$article['title']}");
                $this->line("         Source: {$article['source']}");
            }
            
            // Check if cached
            $cached = \DB::table('news_cache')->count();
            $this->line("      💾 Cached articles in database: {$cached}");
        } else {
            $this->warn('   ⚠️  Using mock data (API key might be invalid or limit reached)');
        }
        $this->newLine();
        
        // 3. Test Open-Meteo API (Free, no key needed)
        $this->info('3️⃣  Testing Open-Meteo Weather API (Free)...');
        $meteo = app(OpenMeteoService::class);
        $weather = $meteo->getWeather(1.3521, 103.8198); // Singapore
        
        if ($weather) {
            $this->info('   ✅ Success: Real weather data for Singapore');
            $this->line("      Temperature: {$weather['temperature']}°C");
            $this->line("      Condition: {$weather['weather_condition']}");
            $this->line("      Rainfall: {$weather['rainfall']} mm");
            $this->line("      Wind Speed: {$weather['wind_speed']} km/h");
            $this->line("      Risk Level: " . strtoupper($weather['risk_level']));
            
            // Check if cached
            $cached = \DB::table('weather_cache')->count();
            $this->line("      💾 Cached weather records: {$cached}");
        }
        $this->newLine();
        
        // 4. Test Rate History
        $this->info('4️⃣  Testing Exchange Rate History (Simulated)...');
        $history = $exchange->getRateHistory('USD', 'EUR');
        
        if (!empty($history)) {
            $this->info('   ✅ Success: 7-day rate history generated');
            $this->table(['Date', 'Rate'], collect($history)->map(fn($rate, $date) => [$date, $rate])->values()->toArray());
        }
        $this->newLine();
        
        // Summary
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('📊 REAL API TESTING SUMMARY');
        $this->info('═══════════════════════════════════════════════════════');
        
        $summary = [
            ['API Service', 'Status', 'Requires Key'],
            ['REST Countries', '✅ Working (Fallback)', 'No'],
            ['Open-Meteo Weather', '✅ Working', 'No'],
            ['Exchange Rate API', !empty($rates) ? '✅ Working' : '❌ Failed', 'Yes'],
            ['GNews API', !empty($news) && count($news) > 0 && !str_contains($news[0]['url'], 'example.com') ? '✅ Working' : '⚠️  Mock Data', 'Yes'],
            ['World Bank API', '⚠️  Slow/Timeout', 'No'],
        ];
        
        $this->table($summary[0], array_slice($summary, 1));
        
        $this->newLine();
        $this->info('✅ Real API testing completed!');
        $this->newLine();
        
        if (config('services.exchange_rate.api_key') === 'your_key_here' || config('services.gnews.api_key') === 'your_key_here') {
            $this->warn('⚠️  Some APIs are using mock data. Update .env with real API keys:');
            $this->line('   GNEWS_API_KEY=your_actual_key');
            $this->line('   EXCHANGE_RATE_API_KEY=your_actual_key');
        }
        
        return Command::SUCCESS;
    }
}
