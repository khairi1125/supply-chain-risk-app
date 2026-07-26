<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\OpenMeteoService;
use App\Services\WorldBankService;
use App\Services\RiskScoringService;

class SeedRiskScoresCommand extends Command
{
    protected $signature = 'risk:seed-all {--clear : Clear existing risk scores first}';
    protected $description = 'Calculate and seed real risk scores for all countries using Weather + World Bank APIs';

    protected $meteoService;
    protected $worldBankService;
    protected $riskService;

    public function __construct(
        OpenMeteoService $meteoService,
        WorldBankService $worldBankService,
        RiskScoringService $riskService
    ) {
        parent::__construct();
        $this->meteoService   = $meteoService;
        $this->worldBankService = $worldBankService;
        $this->riskService    = $riskService;
    }

    public function handle()
    {
        if ($this->option('clear')) {
            DB::table('risk_scores')->truncate();
            $this->info('🗑️  Existing risk scores cleared.');
        }

        $countries = DB::table('countries')
            ->select('code', 'name', 'cca2', 'latitude', 'longitude')
            ->get();

        if ($countries->isEmpty()) {
            $this->error('No countries found. Run countries:fetch first.');
            return 1;
        }

        $this->info("🌍 Calculating real risk scores for {$countries->count()} countries...");
        $this->info("   (Uses Weather API + World Bank API — same data as View Details)");
        $this->newLine();

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $done = 0;
        $errors = 0;

        foreach ($countries as $country) {
            try {
                // Skip if already has a recent score (within 6 hours)
                $existing = DB::table('risk_scores')
                    ->where('country_code', $country->code)
                    ->where('calculated_at', '>=', now()->subHours(6))
                    ->exists();

                if ($existing) {
                    $bar->advance();
                    $done++;
                    continue;
                }

                // Get weather data
                $weather = null;
                if ($country->latitude && $country->longitude) {
                    try {
                        $weather = $this->meteoService->getWeather(
                            (float) $country->latitude,
                            (float) $country->longitude
                        );
                    } catch (\Exception $e) {
                        // weather stays null → default 30
                    }
                }

                // Get inflation from World Bank
                $latestInflation = null;
                try {
                    $wbCode = $country->cca2 ?? $country->code;
                    $inflationData = $this->worldBankService->getInflation($wbCode);
                    if (!empty($inflationData)) {
                        $latestInflation = round(reset($inflationData), 2);
                    }
                } catch (\Exception $e) {
                    // inflation stays null → default 50
                }

                $riskData = [
                    'weather'         => $weather,
                    'inflation'       => $latestInflation,
                    'currency_change' => 0,       // neutral default
                    'news_sentiment'  => 'neutral' // neutral default
                ];

                $this->riskService->calculateRiskScore($country->code, $riskData);
                $done++;

            } catch (\Exception $e) {
                $errors++;
            }

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

        $this->info("✅ Done! Calculated scores for {$done} countries. Errors: {$errors}");
        $this->table(
            ['Risk Level', 'Count'],
            [
                ['Critical', $dist['critical'] ?? 0],
                ['High',     $dist['high']     ?? 0],
                ['Medium',   $dist['medium']   ?? 0],
                ['Low',      $dist['low']      ?? 0],
            ]
        );

        // Generate historical snapshots
        $this->info('📈 Generating 30-day historical trend data...');
        $this->call('risk:generate-historical', ['--days' => 30]);

        return 0;
    }
}
