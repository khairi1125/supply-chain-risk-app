<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExchangeRateService;

class TestExchangeRateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Exchange Rate API connectivity and data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Exchange Rate API...');
        $this->newLine();
        
        $service = new ExchangeRateService();
        
        // Test 1: Check API Key
        $apiKey = config('services.exchange_rate.api_key');
        $this->info('API Key: ' . ($apiKey ? substr($apiKey, 0, 10) . '...' : 'NOT SET'));
        $this->newLine();
        
        // Test 2: Get major currencies
        $this->info('Fetching major currencies (USD base)...');
        $currencies = ['EUR', 'GBP', 'JPY', 'CNY', 'IDR', 'SGD'];
        
        $rates = $service->getRates('USD', $currencies);
        
        $this->table(
            ['Currency', 'Rate (per 1 USD)'],
            collect($rates)->map(function($rate, $currency) {
                return [$currency, number_format($rate, 4)];
            })->toArray()
        );
        
        // Test 3: Specific IDR rate
        $this->newLine();
        $this->info('Testing IDR rate specifically...');
        $idrRate = $service->getRate('USD', 'IDR');
        $this->line('1 USD = ' . number_format($idrRate, 2) . ' IDR');
        
        // Test 4: History
        $this->newLine();
        $this->info('Testing 7-day history for IDR...');
        $history = $service->getRateHistory('USD', 'IDR');
        
        foreach ($history as $date => $rate) {
            $this->line($date . ': ' . number_format($rate, 2) . ' IDR');
        }
        
        $this->newLine();
        $this->info('✓ Test completed. Check logs for API errors.');
        
        return Command::SUCCESS;
    }
}
