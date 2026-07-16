<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestPortsDownloadCommand extends Command
{
    protected $signature = 'test:ports-download';
    protected $description = 'Test downloading ports data from GitHub';

    public function handle()
    {
        $this->info('Testing GitHub ports download...');
        
        $url = 'https://raw.githubusercontent.com/tayljordan/ports/main/ports.json';
        
        $this->info("URL: $url");
        $this->newLine();
        
        try {
            $response = Http::withOptions(['verify' => false])->timeout(60)->get($url);
            
            $this->info("Status: " . $response->status());
            $this->info("Body size: " . strlen($response->body()) . " bytes");
            
            // Try to decode manually
            $body = $response->body();
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("JSON decode error: " . json_last_error_msg());
                $this->line("First 500 chars: " . substr($body, 0, 500));
                return;
            }
            
            $this->info("JSON decoded successfully");
            $this->info("Data type: " . gettype($data));
            
            if (is_array($data)) {
                $this->info("Total items: " . count($data));
                
                // Check if it's an object with a ports key
                if (isset($data['ports']) && is_array($data['ports'])) {
                    $this->info("Found 'ports' key with " . count($data['ports']) . " items");
                    $ports = $data['ports'];
                } else {
                    $this->info("Keys: " . implode(', ', array_keys($data)));
                    $ports = $data;
                }
                
                if (count($ports) > 0) {
                    $this->newLine();
                    $this->info("First port sample:");
                    $firstPort = is_array($ports) ? reset($ports) : null;
                    if ($firstPort) {
                        $this->line(json_encode($firstPort, JSON_PRETTY_PRINT));
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
