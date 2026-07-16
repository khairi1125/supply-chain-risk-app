<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorldPortIndexService;

class ImportWorldPortIndexCommand extends Command
{
    protected $signature = 'ports:import-full 
                            {--fresh : Truncate ports table before import}
                            {--stats : Show statistics after import}';

    protected $description = 'Import full World Port Index dataset (3,898 ports) from GitHub';

    public function handle(WorldPortIndexService $service)
    {
        $this->info('🚢 Starting World Port Index Import...');
        $this->newLine();

        // Fresh import option
        if ($this->option('fresh')) {
            if ($this->confirm('This will delete all existing ports. Continue?', false)) {
                $this->warn('Truncating ports table...');
                \Illuminate\Support\Facades\DB::table('ports')->truncate();
                $this->info('✓ Ports table cleared');
                $this->newLine();
            } else {
                $this->error('Import cancelled.');
                return Command::FAILURE;
            }
        }

        // Start import with progress bar
        $this->info('📥 Fetching port data from GitHub...');
        
        $startTime = microtime(true);
        
        // Import ports
        $result = $service->importPorts();

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();

        if ($result['success']) {
            $this->info('✅ Import completed successfully!');
            $this->newLine();
            
            // Display results
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total in dataset', $result['total']],
                    ['Successfully imported', $result['imported']],
                    ['Skipped (duplicates/invalid)', $result['skipped']],
                    ['Errors', $result['errors']],
                    ['Duration', $duration . ' seconds'],
                ]
            );

            // Show statistics if requested
            if ($this->option('stats')) {
                $this->newLine();
                $this->info('📊 Port Distribution:');
                $this->newLine();
                
                $stats = $service->getImportStatistics();
                
                $this->info('By Region:');
                $regionData = [];
                foreach ($stats['by_region'] as $region => $count) {
                    $regionData[] = [$region, $count];
                }
                $this->table(['Region', 'Ports'], $regionData);
                
                $this->newLine();
                $this->info('Top 10 Countries:');
                $countryData = [];
                foreach ($stats['top_countries'] as $code => $count) {
                    $countryData[] = [$code, $count];
                }
                $this->table(['Country Code', 'Ports'], $countryData);
            }

            $this->newLine();
            $this->info('💡 Next steps:');
            $this->line('  - Visit Port Map: http://localhost:8000/dashboard/port-map');
            $this->line('  - Test API: GET /api/ports');
            $this->line('  - Search ports: GET /api/ports/search?q=jakarta');
            
            return Command::SUCCESS;

        } else {
            $this->error('❌ Import failed: ' . $result['message']);
            return Command::FAILURE;
        }
    }
}
