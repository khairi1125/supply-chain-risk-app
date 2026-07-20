<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateHistoricalSnapshotsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:generate-historical {--days=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate historical risk score snapshots for trend analysis (one-time setup)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Generating {$days} days of historical snapshots...");
        
        // Get current average as baseline
        $currentAvg = DB::table('risk_scores')->avg('total_score') ?? 50;
        
        // Get current distribution
        $currentCounts = DB::table('risk_scores')
            ->select('risk_level', DB::raw('count(*) as count'))
            ->groupBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level')
            ->toArray();
        
        $bar = $this->output->createProgressBar($days);
        $bar->start();
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            
            // Check if already exists
            $exists = DB::table('risk_score_histories')
                ->where('snapshot_date', $date)
                ->exists();
                
            if ($exists) {
                $bar->advance();
                continue;
            }
            
            // Generate realistic variation around current average
            // Older data has more variation to show trend
            $variation = ($i / $days) * 15; // Max 15 points variation for oldest data
            $avgScore = $currentAvg + rand(-$variation, $variation);
            $avgScore = max(0, min(100, $avgScore)); // Keep between 0-100
            
            // Generate realistic distribution with slight variation
            $totalCountries = array_sum($currentCounts);
            $critical = max(0, ($currentCounts['critical'] ?? 0) + rand(-5, 5));
            $high = max(0, ($currentCounts['high'] ?? 0) + rand(-10, 10));
            $medium = max(0, ($currentCounts['medium'] ?? 0) + rand(-10, 10));
            $low = $totalCountries - $critical - $high - $medium;
            $low = max(0, $low);
            
            DB::table('risk_score_histories')->insert([
                'snapshot_date' => $date,
                'avg_total_score' => round($avgScore, 2),
                'critical_count' => $critical,
                'high_count' => $high,
                'medium_count' => $medium,
                'low_count' => $low,
                'created_at' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
                'updated_at' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
            ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Historical snapshots generated successfully!");
        
        return 0;
    }
}
