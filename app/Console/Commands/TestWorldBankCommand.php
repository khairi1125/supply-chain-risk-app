<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorldBankService;

class TestWorldBankCommand extends Command
{
    protected $signature = 'apis:test-worldbank {country=USA}';
    protected $description = 'Test World Bank API for a specific country';

    public function handle()
    {
        $countryCode = $this->argument('country');
        
        $this->info("🌍 Testing World Bank API for: {$countryCode}");
        $this->newLine();
        
        $service = app(WorldBankService::class);
        
        // Test GDP
        $this->info('📊 Fetching GDP data (5 years)...');
        $gdp = $service->getGDP($countryCode);
        
        if (!empty($gdp)) {
            $this->info('✅ GDP Data Retrieved:');
            foreach ($gdp as $year => $value) {
                $formatted = number_format($value / 1000000000, 2);
                $this->line("   {$year}: \${$formatted}B USD");
            }
        } else {
            $this->error('❌ No GDP data available');
        }
        $this->newLine();
        
        // Test Inflation
        $this->info('📈 Fetching Inflation data (5 years)...');
        $inflation = $service->getInflation($countryCode);
        
        if (!empty($inflation)) {
            $this->info('✅ Inflation Data Retrieved:');
            foreach ($inflation as $year => $value) {
                $this->line("   {$year}: " . number_format($value, 2) . "%");
            }
        } else {
            $this->error('❌ No inflation data available');
        }
        $this->newLine();
        
        // Test Population
        $this->info('👥 Fetching Population data...');
        $population = $service->getPopulation($countryCode);
        
        if ($population > 0) {
            $formatted = number_format($population);
            $this->info("✅ Population: {$formatted}");
        } else {
            $this->error('❌ No population data available');
        }
        $this->newLine();
        
        $this->info('✅ World Bank API test completed!');
        
        return Command::SUCCESS;
    }
}
