<?php

namespace App\Console\Commands;

use App\Services\OpenMeteoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarmWeatherCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:warm-cache 
                            {--limit=100 : Number of countries to fetch per run}
                            {--force : Force update even if cache is fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-warm weather cache for all countries (run in background)';
    
    protected $weatherService;

    public function __construct(OpenMeteoService $weatherService)
    {
        parent::__construct();
        $this->weatherService = $weatherService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌦️  Starting weather cache warming...');
        
        $limit = $this->option('limit');
        $force = $this->option('force');
        
        // Get countries that need weather data update
        $query = DB::table('countries as c')
            ->leftJoin('weather_cache as wc', 'c.code', '=', 'wc.country_code')
            ->select('c.id', 'c.name', 'c.code', 'c.latitude', 'c.longitude')
            ->whereNotNull('c.latitude')
            ->whereNotNull('c.longitude');
        
        if (!$force) {
            // Only fetch countries with no cache or expired cache (> 4 hours old)
            $query->where(function($q) {
                $q->whereNull('wc.fetched_at')
                  ->orWhere('wc.fetched_at', '<', now()->subHours(4));
            });
        }
        
        $countries = $query->limit($limit)->get();
        
        if ($countries->isEmpty()) {
            $this->info('✅ All countries have fresh weather cache. Nothing to update.');
            return 0;
        }
        
        $this->info("📍 Found {$countries->count()} countries to update");
        
        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($countries as $country) {
            try {
                $weather = $this->weatherService->getWeatherWithCountryCode(
                    $country->latitude,
                    $country->longitude,
                    $country->code
                );
                
                if ($weather) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                $this->warn("\n❌ Failed to fetch weather for {$country->name}: " . $e->getMessage());
                $failCount++;
            }
            
            $bar->advance();
            
            // Small delay to avoid rate limiting (100ms per request)
            usleep(100000);
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Weather cache updated!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $successCount],
                ['Failed', $failCount],
                ['Total', $countries->count()],
            ]
        );
        
        Log::info("Weather cache warmed: {$successCount} success, {$failCount} failed");
        
        return 0;
    }
}
