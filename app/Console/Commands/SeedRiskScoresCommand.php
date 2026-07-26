<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedRiskScoresCommand extends Command
{
    protected $signature = 'risk:seed-all';
    protected $description = 'Seed initial risk scores for all countries using default weighted values';

    public function handle()
    {
        $this->info('🌍 Seeding risk scores for all countries...');

        $countries = DB::table('countries')->get(['code', 'name', 'region']);

        if ($countries->isEmpty()) {
            $this->error('No countries found. Run countries:fetch first.');
            return 1;
        }

        $this->info("Found {$countries->count()} countries. Calculating scores...");
        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $now = now();
        $seeded = 0;

        foreach ($countries as $country) {
            // Assign a base score slightly varied by region for realism
            $regionBase = match ($country->region ?? '') {
                'Africa'  => 62,
                'Asia'    => 52,
                'Europe'  => 35,
                'Americas'=> 45,
                'Oceania' => 38,
                default   => 50,
            };

            // Add small random variation so data looks real, not all identical
            $variation = rand(-8, 8);
            $totalScore = max(10, min(90, $regionBase + $variation));

            $riskLevel = match(true) {
                $totalScore >= 76 => 'critical',
                $totalScore >= 51 => 'high',
                $totalScore >= 26 => 'medium',
                default           => 'low',
            };

            DB::table('risk_scores')->updateOrInsert(
                ['country_code' => $country->code],
                [
                    'weather_score'   => 30,
                    'inflation_score' => 50,
                    'currency_score'  => 40,
                    'news_score'      => 50,
                    'total_score'     => $totalScore,
                    'risk_level'      => $riskLevel,
                    'calculated_at'   => $now,
                    'updated_at'      => $now,
                ]
            );

            $seeded++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $dist = DB::table('risk_scores')
            ->select('risk_level', DB::raw('count(*) as cnt'))
            ->groupBy('risk_level')
            ->get()
            ->pluck('cnt', 'risk_level');

        $this->info("✅ Done! Seeded {$seeded} risk scores.");
        $this->table(
            ['Risk Level', 'Count'],
            [
                ['Critical', $dist['critical'] ?? 0],
                ['High',     $dist['high']     ?? 0],
                ['Medium',   $dist['medium']   ?? 0],
                ['Low',      $dist['low']      ?? 0],
            ]
        );

        // Now generate historical snapshots so the trend chart has data
        $this->info('📈 Generating historical trend data...');
        $this->call('risk:generate-historical', ['--days' => 30]);

        return 0;
    }
}
