<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PortDataService;

class ImportPortsCommand extends Command
{
    protected $signature = 'ports:import';
    protected $description = 'Import major world ports to database';

    public function handle()
    {
        $this->info('🚢 Importing major world ports...');
        $this->newLine();
        
        $service = app(PortDataService::class);
        $result = $service->importPorts();
        
        $this->info('✅ Import completed!');
        $this->newLine();
        
        $this->table(
            ['Status', 'Count'],
            [
                ['New ports imported', $result['imported']],
                ['Existing ports updated', $result['updated']],
                ['Total ports', $result['total']],
            ]
        );
        
        return Command::SUCCESS;
    }
}
