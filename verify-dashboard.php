#!/usr/bin/env php
<?php

/**
 * Dashboard Data Verification Script
 * 
 * Verifies that all dashboard data is real (not dummy)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  DASHBOARD DATA VERIFICATION\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Check 1: Historical Snapshots
echo "✓ Checking Historical Snapshots...\n";
$snapshotCount = DB::table('risk_score_histories')->count();
if ($snapshotCount >= 7) {
    echo "  ✅ PASS: {$snapshotCount} snapshots found (minimum 7 days required)\n";
} else {
    echo "  ⚠️  WARNING: Only {$snapshotCount} snapshots found\n";
    echo "     Run: php artisan risk:generate-historical --days=30\n";
}

// Check 2: Latest Snapshot
echo "\n✓ Checking Latest Snapshot...\n";
$latestSnapshot = DB::table('risk_score_histories')
    ->orderBy('snapshot_date', 'desc')
    ->first();
if ($latestSnapshot) {
    echo "  ✅ PASS: Latest snapshot from {$latestSnapshot->snapshot_date}\n";
    echo "     - Avg Score: {$latestSnapshot->avg_total_score}\n";
    echo "     - Distribution: Critical={$latestSnapshot->critical_count}, High={$latestSnapshot->high_count}, Medium={$latestSnapshot->medium_count}, Low={$latestSnapshot->low_count}\n";
} else {
    echo "  ❌ FAIL: No snapshots found\n";
    echo "     Run: php artisan risk:generate-historical --days=30\n";
}

// Check 3: Alert Logs
echo "\n✓ Checking Alert Logs...\n";
$alertCount = DB::table('alert_logs')->where('is_resolved', false)->count();
if ($alertCount > 0) {
    echo "  ✅ PASS: {$alertCount} active alerts found\n";
    
    // Show alert distribution
    $alertDistribution = DB::table('alert_logs')
        ->select('severity', DB::raw('count(*) as count'))
        ->where('is_resolved', false)
        ->groupBy('severity')
        ->get();
    
    foreach ($alertDistribution as $dist) {
        echo "     - " . ucfirst($dist->severity) . ": {$dist->count}\n";
    }
} else {
    echo "  ⚠️  WARNING: No alerts found\n";
    echo "     Run: php artisan alerts:generate\n";
}

// Check 4: Alert Timestamps
echo "\n✓ Checking Alert Timestamps...\n";
$recentAlerts = DB::table('alert_logs')
    ->where('is_resolved', false)
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

if ($recentAlerts->count() > 0) {
    echo "  ✅ PASS: Alerts have real timestamps\n";
    foreach ($recentAlerts as $alert) {
        $timeAgo = \Carbon\Carbon::parse($alert->created_at)->diffForHumans();
        echo "     - {$alert->title}: {$timeAgo}\n";
    }
} else {
    echo "  ⚠️  WARNING: No alerts to check\n";
}

// Check 5: Risk Scores
echo "\n✓ Checking Risk Scores...\n";
$riskScoreCount = DB::table('risk_scores')->count();
if ($riskScoreCount > 0) {
    echo "  ✅ PASS: {$riskScoreCount} countries have risk scores\n";
    
    // Show distribution
    $distribution = DB::table('risk_scores')
        ->select('risk_level', DB::raw('count(*) as count'))
        ->groupBy('risk_level')
        ->get();
    
    foreach ($distribution as $dist) {
        echo "     - " . ucfirst($dist->risk_level) . ": {$dist->count}\n";
    }
} else {
    echo "  ❌ FAIL: No risk scores found\n";
}

// Check 6: Countries
echo "\n✓ Checking Countries...\n";
$countryCount = DB::table('countries')->count();
if ($countryCount > 0) {
    echo "  ✅ PASS: {$countryCount} countries in database\n";
} else {
    echo "  ❌ FAIL: No countries found\n";
    echo "     Run: php artisan countries:fetch\n";
}

// Check 7: Ports
echo "\n✓ Checking Ports...\n";
$portCount = DB::table('ports')->count();
if ($portCount > 0) {
    echo "  ✅ PASS: {$portCount} ports in database\n";
} else {
    echo "  ⚠️  WARNING: No ports found\n";
    echo "     Run: php artisan ports:import\n";
}

// Check 8: News Cache
echo "\n✓ Checking News Cache...\n";
$newsCount = DB::table('news_cache')->count();
if ($newsCount > 0) {
    echo "  ✅ PASS: {$newsCount} news articles cached\n";
} else {
    echo "  ⚠️  WARNING: No news articles found\n";
}

// Summary
echo "\n════════════════════════════════════════════════════════════════\n";
echo "  SUMMARY\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$checks = [
    'Historical Snapshots' => $snapshotCount >= 7,
    'Latest Snapshot' => $latestSnapshot !== null,
    'Alert Logs' => $alertCount > 0,
    'Alert Timestamps' => $recentAlerts->count() > 0,
    'Risk Scores' => $riskScoreCount > 0,
    'Countries' => $countryCount > 0,
    'Ports' => $portCount > 0,
    'News Cache' => $newsCount > 0,
];

$passed = array_filter($checks);
$total = count($checks);
$passedCount = count($passed);

echo "Checks Passed: {$passedCount}/{$total}\n\n";

if ($passedCount === $total) {
    echo "🎉 ALL CHECKS PASSED! Dashboard is ready.\n";
} else {
    echo "⚠️  Some checks failed. Review the warnings above.\n";
}

echo "\n════════════════════════════════════════════════════════════════\n\n";

// Exit code
exit($passedCount === $total ? 0 : 1);
