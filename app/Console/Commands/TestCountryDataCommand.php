<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RestCountriesService;

class TestCountryDataCommand extends Command
{
    protected $signature = 'test:country-data {code=IDN}';
    protected $description = 'Test country data from service';

    public function handle()
    {
        $code = $this->argument('code');
        
        $service = app(RestCountriesService::class);
        $countries = $service->getAllCountries();
        
        $country = collect($countries)->firstWhere('code', $code);
        
        if ($country) {
            $this->info("Country data for {$code}:");
            print_r($country);
        } else {
            $this->error("Country {$code} not found");
        }
        
        return Command::SUCCESS;
    }
}
