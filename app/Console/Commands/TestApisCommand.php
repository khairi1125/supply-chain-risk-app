<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RestCountriesService;
use App\Services\OpenMeteoService;
use App\Services\WorldBankService;
use App\Services\ExchangeRateService;
use App\Services\GNewsService;

class TestApisCommand extends Command
{
    protected $signature = 'apis:test';
    protected $description = 'Test all external API services';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing All External API Services');
        $this->newLine();
        
        // 1. Test REST Countries API
        $this->info('1️⃣  Testing REST Countries API...');
        $restCountries = app(RestCountriesService::class);
        $country = $restCountries->getCountryByCode('USA');
        if ($country) {
            $this->info("   ✅ Success: Found {$country['name']}");
            $this->line("      Region: {$country['region']}");
            $this->line("      Currency: {$country['currency_name']} ({$country['currency_code']})");
        } else {
            $this->error('   ❌ Failed to get country data');
        }
        $this->newLine();
        
        // 2. Test Open-Meteo API
        $this->info('2️⃣  Testing Open-Meteo Weather API...');
        $meteo = app(OpenMeteoService::class);
        $weather = $meteo->getWeather(38, -97); // USA coordinates
        if ($weather) {
            $this->info("   ✅ Success: Weather data retrieved");
            $this->line("      Temperature: {$weather['temperature']}°C");
            $this->line("      Condition: {$weather['weather_condition']}");
            $this->line("      Wind Speed: {$weather['wind_speed']} km/h");
            $this->line("      Risk Level: " . strtoupper($weather['risk_level']));
        } else {
            $this->error('   ❌ Failed to get weather data');
        }
        $this->newLine();
        
        // 3. Test World Bank API
        $this->info('3️⃣  Testing World Bank API...');
        $worldBank = app(WorldBankService::class);
        $gdp = $worldBank->getGDP('USA');
        if (!empty($gdp)) {
            $this->info('   ✅ Success: GDP data retrieved');
            $latestYear = array_key_first($gdp);
            $latestValue = $gdp[$latestYear];
            $this->line("      Latest GDP ({$latestYear}): $" . number_format($latestValue, 0));
        } else {
            $this->warn('   ⚠️  No GDP data available (API might be slow)');
        }
        $this->newLine();
        
        // 4. Test Exchange Rate API
        $this->info('4️⃣  Testing Exchange Rate API...');
        $exchange = app(ExchangeRateService::class);
        $rate = $exchange->getRate('USD', 'EUR');
        if ($rate > 0) {
            $this->info('   ✅ Success: Exchange rate retrieved');
            $this->line("      1 USD = {$rate} EUR");
            
            // Test caching
            $cached = \DB::table('currency_cache')
                ->where('base_currency', 'USD')
                ->where('target_currency', 'EUR')
                ->first();
            if ($cached) {
                $this->line("      ✅ Cached to database successfully");
            }
        } else {
            $this->error('   ❌ Failed to get exchange rate');
        }
        $this->newLine();
        
        // 5. Test GNews API
        $this->info('5️⃣  Testing GNews API...');
        $gnews = app(GNewsService::class);
        $news = $gnews->getNewsGeneral('supply chain', 3);
        if (!empty($news)) {
            $this->info('   ✅ Success: News articles retrieved');
            $this->line("      Found " . count($news) . " articles");
            foreach ($news as $index => $article) {
                $this->line("      " . ($index + 1) . ". {$article['title']}");
            }
        } else {
            $this->error('   ❌ Failed to get news articles');
        }
        $this->newLine();
        
        // Summary
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('📊 API TESTING SUMMARY');
        $this->info('═══════════════════════════════════════════════════════');
        
        $cacheStats = [
            ['Service', 'Cached Records'],
            ['Weather Cache', \DB::table('weather_cache')->count()],
            ['Currency Cache', \DB::table('currency_cache')->count()],
            ['News Cache', \DB::table('news_cache')->count()],
            ['Countries', \DB::table('countries')->count()],
        ];
        
        $this->table($cacheStats[0], array_slice($cacheStats, 1));
        
        $this->newLine();
        $this->info('✅ All API tests completed!');
        
        return Command::SUCCESS;
    }
}
