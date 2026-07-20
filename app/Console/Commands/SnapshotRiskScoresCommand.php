<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SnapshotRiskScoresCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:snapshot {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take a daily snapshot of risk scores for historical trending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? date('Y-m-d', strtotime($this->option('date'))) : date('Y-m-d');
        
        $this->info("Taking risk score snapshot for date: {$date}");
        
        // Check if snapshot already exists for this date
        $exists = DB::table('risk_score_histories')
            ->where('snapshot_date', $date)
            ->exists();
            
        if ($exists) {
            if ($this->confirm("Snapshot for {$date} already exists. Overwrite?")) {
                DB::table('risk_score_histories')
                    ->where('snapshot_date', $date)
                    ->delete();
                $this->info("Existing snapshot deleted.");
            } else {
                $this->warn("Snapshot cancelled.");
                return 0;
            }
        }
        
        // Calculate average risk score
        $avgScore = DB::table('risk_scores')->avg('total_score') ?? 0;
        
        // Count by risk level
        $counts = DB::table('risk_scores')
            ->select('risk_level', DB::raw('count(*) as count'))
            ->groupBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level')
            ->toArray();
        
        // Insert snapshot
        DB::table('risk_score_histories')->insert([
            'snapshot_date' => $date,
            'avg_total_score' => round($avgScore, 2),
            'critical_count' => $counts['critical'] ?? 0,
            'high_count' => $counts['high'] ?? 0,
            'medium_count' => $counts['medium'] ?? 0,
            'low_count' => $counts['low'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->info("✅ Snapshot saved successfully!");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Average Risk Score', round($avgScore, 2)],
                ['Critical', $counts['critical'] ?? 0],
                ['High', $counts['high'] ?? 0],
                ['Medium', $counts['medium'] ?? 0],
                ['Low', $counts['low'] ?? 0],
            ]
        );
        
        return 0;
    }
}
