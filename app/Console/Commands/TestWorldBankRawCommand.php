<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestWorldBankRawCommand extends Command
{
    protected $signature = 'test:worldbank-raw {country=IN}';
    protected $description = 'Test raw World Bank API response';

    public function handle()
    {
        $country = $this->argument('country');
        
        $this->info("Testing World Bank API for: {$country}");
        $this->newLine();
        
        // Test GDP
        $this->info('📊 Testing GDP endpoint...');
        $url = "https://api.worldbank.org/v2/country/{$country}/indicator/NY.GDP.MKTP.CD?format=json&mrv=5&per_page=100";
        $this->line("URL: {$url}");
        
        try {
            $response = Http::withoutVerifying()->timeout(15)->get($url);
            
            $this->line("Status: {$response->status()}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data[0]['message'])) {
                    $this->error("Error message: " . json_encode($data[0]['message']));
                } else {
                    $this->info("Success! Data structure:");
                    $this->line(json_encode($data, JSON_PRETTY_PRINT, 512));
                }
            } else {
                $this->error("Request failed");
            }
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
}
