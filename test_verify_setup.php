#!/usr/bin/env php
<?php
/**
 * Quick verification script for Country Monitor setup
 */

// Check PHP version
if (version_compare(PHP_VERSION, '8.1', '<')) {
    echo "PHP 8.1+ required\n";
    exit(1);
}

// Load Laravel bootstrap
$basePath = __DIR__;
$bootstrap = $basePath . '/bootstrap/app.php';

if (!file_exists($bootstrap)) {
    echo "Laravel bootstrap file not found\n";
    exit(1);
}

// Set timezone
date_default_timezone_set('UTC');

// Start output
echo "\n=== COUNTRY MONITOR SETUP VERIFICATION ===\n\n";

try {
    // Load Laravel app
    $app = require $bootstrap;
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');

    // 1. Check database tables
    echo "1. Checking database tables...\n";
    $schema = $app['db']->connection()->getSchemaBuilder();
    
    $tables = ['countries', 'positive_words', 'negative_words', 'news_cache'];
    foreach ($tables as $table) {
        if ($schema->hasTable($table)) {
            $count = $app['db']->table($table)->count();
            echo "   ✓ $table: $count records\n";
        } else {
            echo "   ✗ $table: NOT FOUND\n";
        }
    }
    
    echo "\n2. Checking configuration...\n";
    $gnewsKey = config('services.gnews.api_key');
    if ($gnewsKey && $gnewsKey !== 'your_key_here' && strlen($gnewsKey) > 10) {
        echo "   ✓ GNews API key configured\n";
    } else {
        echo "   ✗ GNews API key not configured or invalid\n";
    }
    
    $exchangeKey = config('services.exchange_rate.api_key');
    if ($exchangeKey && strlen($exchangeKey) > 5) {
        echo "   ✓ Exchange Rate API key configured\n";
    } else {
        echo "   ✗ Exchange Rate API key not configured\n";
    }
    
    echo "\n3. Checking services...\n";
    try {
        $sentimentService = $app->make('App\Services\SentimentAnalysisService');
        echo "   ✓ SentimentAnalysisService loaded\n";
    } catch (\Exception $e) {
        echo "   ✗ SentimentAnalysisService error: " . $e->getMessage() . "\n";
    }
    
    try {
        $newsService = $app->make('App\Services\GNewsService');
        echo "   ✓ GNewsService loaded\n";
    } catch (\Exception $e) {
        echo "   ✗ GNewsService error: " . $e->getMessage() . "\n";
    }
    
    echo "\n4. Testing sample country...\n";
    $country = $app['db']->table('countries')->first();
    if ($country) {
        echo "   ✓ Sample country: {$country->name} ({$country->code})\n";
    } else {
        echo "   ✗ No countries in database\n";
    }
    
    echo "\n=== VERIFICATION COMPLETE ===\n\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
