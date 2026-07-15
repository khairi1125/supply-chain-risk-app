<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\WorldBankService;

class CheckWorldBankCoverageCommand extends Command
{
    protected $signature = 'worldbank:check-coverage {limit=10}';
    protected $description = 'Check which countries have World Bank data';

    public function handle()
    {
        $this->info('🔍 Checking World Bank API coverage...');
        $this->newLine();
        
        $service = app(WorldBankService::class);
        
        // Get sample countries
        $limit = $this->argument('limit');
        $countries = DB::table('countries')
            ->select('code', 'cca2', 'name')
            ->limit($limit)
            ->get();
        
        $withData = 0;
        $withoutData = 0;
        $results = [];
        
        $bar = $this->output->createProgressBar(count($countries));
        $bar->start();
        
        foreach ($countries as $country) {
            $worldBankCode = $country->cca2 ?? $country->code;
            
            $gdp = $service->getGDP($worldBankCode);
            $inflation = $service->getInflation($worldBankCode);
            
            $hasData = !empty($gdp) || !empty($inflation);
            
            if ($hasData) {
                $withData++;
            } else {
                $withoutData++;
            }
            
            $results[] = [
                'name' => $country->name,
                'cca2' => $country->cca2 ?? 'N/A',
                'has_gdp' => !empty($gdp) ? '✅' : '❌',
                'has_inflation' => !empty($inflation) ? '✅' : '❌',
            ];
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info('📊 Results:');
        $this->table(
            ['Country', 'CCA2', 'GDP Data', 'Inflation Data'],
            $results
        );
        
        $this->newLine();
        $this->info("✅ Countries with data: {$withData}");
        $this->warn("❌ Countries without data: {$withoutData}");
        $this->line("Coverage: " . round(($withData / count($countries)) * 100, 1) . "%");
        
        return Command::SUCCESS;
    }
}
