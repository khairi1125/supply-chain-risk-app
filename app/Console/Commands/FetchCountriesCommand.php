<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RestCountriesService;
use Illuminate\Support\Facades\DB;

class FetchCountriesCommand extends Command
{
    protected $signature = 'countries:fetch';
    protected $description = 'Fetch all countries from REST Countries API and save to database';

    protected $restCountriesService;

    public function __construct(RestCountriesService $restCountriesService)
    {
        parent::__construct();
        $this->restCountriesService = $restCountriesService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌍 Fetching countries from REST Countries API...');
        $this->newLine();
        
        $countries = $this->restCountriesService->getAllCountries();
        
        if (empty($countries)) {
            $this->error('❌ Failed to fetch countries from API!');
            return Command::FAILURE;
        }
        
        $this->info("📥 Found " . count($countries) . " countries. Starting import...");
        $this->newLine();
        
        $bar = $this->output->createProgressBar(count($countries));
        $bar->start();
        
        $imported = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($countries as $country) {
            try {
                // Check if country already exists
                $exists = DB::table('countries')->where('code', $country['code'])->exists();
                
                if ($exists) {
                    // Update existing country
                    DB::table('countries')->where('code', $country['code'])->update([
                        'name' => $country['name'],
                        'cca2' => $country['cca2'] ?? null,
                        'region' => $country['region'],
                        'currency_code' => $country['currency_code'],
                        'currency_name' => $country['currency_name'],
                        'flag_url' => $country['flag_url'],
                        'latitude' => $country['latitude'],
                        'longitude' => $country['longitude'],
                        'updated_at' => now(),
                    ]);
                    $skipped++;
                } else {
                    // Insert new country
                    DB::table('countries')->insert([
                        'name' => $country['name'],
                        'code' => $country['code'],
                        'cca2' => $country['cca2'] ?? null,
                        'region' => $country['region'],
                        'currency_code' => $country['currency_code'],
                        'currency_name' => $country['currency_name'],
                        'flag_url' => $country['flag_url'],
                        'latitude' => $country['latitude'],
                        'longitude' => $country['longitude'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $imported++;
                }
                
                $bar->advance();
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Error importing {$country['name']}: " . $e->getMessage());
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display summary
        $this->info('✅ Import completed!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['New countries imported', $imported],
                ['Existing countries updated', $skipped],
                ['Errors', $errors],
                ['Total processed', count($countries)],
            ]
        );
        
        // Display sample countries
        $this->newLine();
        $this->info('📋 Sample countries in database:');
        $samples = DB::table('countries')->limit(5)->get(['name', 'code', 'region']);
        $this->table(
            ['Name', 'Code', 'Region'],
            $samples->map(fn($c) => [$c->name, $c->code, $c->region])->toArray()
        );
        
        return Command::SUCCESS;
    }
}
