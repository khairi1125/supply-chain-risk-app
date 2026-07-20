<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\GNewsService;

#[Signature('news:refresh-all')]
#[Description('Refresh news for all countries from GNews API (auto-called by scheduler every 6 hours)')]
class RefreshAllNewsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GNewsService $newsService)
    {
        $this->info('🌍 Starting auto-refresh of news for all countries...');
        $startTime = now();
        
        // Get all countries
        $countries = DB::table('countries')
            ->orderBy('name')
            ->pluck('name');
        
        $totalCountries = $countries->count();
        $this->info("📊 Total countries to refresh: {$totalCountries}");
        
        $successCount = 0;
        $failCount = 0;
        
        // Progress bar
        $progressBar = $this->output->createProgressBar($totalCountries);
        $progressBar->start();
        
        foreach ($countries as $country) {
            try {
                // Fetch news for each country with force refresh
                $newsService->getNewsByCountryWithSentiment($country, 10, true);
                $successCount++;
            } catch (\Exception $e) {
                \Log::error("Failed to refresh news for {$country}: " . $e->getMessage());
                $failCount++;
            }
            
            $progressBar->advance();
            
            // Rate limiting: wait 0.5 seconds between requests
            usleep(500000);
        }
        
        $progressBar->finish();
        
        $duration = now()->diffInSeconds($startTime);
        
        $this->newLine();
        $this->info('✅ News refresh completed!');
        $this->line("   ✓ Success: <fg=green>{$successCount}</>");
        $this->line("   ✗ Failed: <fg=red>{$failCount}</>");
        $this->line("   ⏱ Duration: <fg=cyan>{$duration}s</>");
        $this->line("   📅 Completed at: <fg=yellow>" . now()->format('Y-m-d H:i:s') . "</>");
        
        return 0;
    }
}
