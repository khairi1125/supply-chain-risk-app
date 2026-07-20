<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\OpenMeteoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('weather:fetch-global')]
#[Description('Fetches global weather data for all countries from OpenMeteo and stores it in cache')]
class FetchGlobalWeather extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(OpenMeteoService $weatherService)
    {
        $this->info('Starting global weather data fetch...');

        $countries = DB::table('countries')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get();

        $successCount = 0;
        $failCount = 0;
        $total = $countries->count();

        $this->output->progressStart($total);

        foreach ($countries as $index => $country) {
            try {
                // Fetch fresh data from API and cache it
                $weather = $weatherService->getWeatherWithCountryCode(
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
                Log::warning("Failed to fetch weather for {$country->name}: " . $e->getMessage());
                $failCount++;
            }
            
            $this->output->progressAdvance();

            // 50ms delay between requests to avoid rate limiting
            usleep(50000); 
        }
        
        $this->output->progressFinish();
        $this->info("Completed weather fetch. Success: {$successCount}, Failed: {$failCount}");
    }
}
