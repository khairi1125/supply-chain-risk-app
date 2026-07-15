<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestRawCountryDataCommand extends Command
{
    protected $signature = 'test:raw-country';
    protected $description = 'Test raw country data from GitHub';

    public function handle()
    {
        $this->info('Fetching raw data from GitHub...');
        
        $response = Http::withoutVerifying()->timeout(30)
            ->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json');
        
        if ($response->successful()) {
            $countries = $response->json();
            
            // Find Indonesia
            $indonesia = collect($countries)->firstWhere('cca3', 'IDN');
            
            if ($indonesia) {
                $this->info('Indonesia raw data:');
                $this->line('Name: ' . ($indonesia['name']['common'] ?? 'N/A'));
                $this->line('CCA2: ' . ($indonesia['cca2'] ?? 'N/A'));
                $this->line('CCA3: ' . ($indonesia['cca3'] ?? 'N/A'));
                $this->line('Region: ' . ($indonesia['region'] ?? 'N/A'));
                
                $this->newLine();
                $this->info('Full data structure (first 500 chars):');
                $this->line(substr(json_encode($indonesia, JSON_PRETTY_PRINT), 0, 500));
            } else {
                $this->error('Indonesia not found');
            }
        } else {
            $this->error('Failed to fetch data');
        }
        
        return Command::SUCCESS;
    }
}
