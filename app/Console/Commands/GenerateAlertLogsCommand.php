<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateAlertLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate alert logs from current risk scores and news (one-time setup)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Generating alert logs from current data...");
        
        $alertsGenerated = 0;
        
        // 1. Generate economic alerts from TOP risk countries (regardless of threshold)
        $highRiskCountries = DB::table('risk_scores')
            ->join('countries', 'risk_scores.country_code', '=', 'countries.code')
            ->select('countries.name', 'countries.code', 'countries.flag_url', 
                    'risk_scores.total_score', 'risk_scores.calculated_at')
            ->orderBy('risk_scores.total_score', 'desc')
            ->limit(10) // Top 10 highest risk countries
            ->get();

        foreach ($highRiskCountries as $country) {
            // Determine severity based on score
            if ($country->total_score >= 76) {
                $severity = 'critical';
            } elseif ($country->total_score >= 51) {
                $severity = 'high';
            } elseif ($country->total_score >= 26) {
                $severity = 'medium';
            } else {
                $severity = 'low';
            }
            
            // Only create alerts for medium and above
            if ($severity === 'low') {
                continue;
            }
            
            // Check if alert already exists
            $exists = DB::table('alert_logs')
                ->where('type', 'economic')
                ->where('country_code', $country->code)
                ->where('is_resolved', false)
                ->exists();
                
            if (!$exists) {
                $createdAt = $country->calculated_at ?? now()->subHours(rand(1, 48));
                
                DB::table('alert_logs')->insert([
                    'type' => 'economic',
                    'severity' => $severity,
                    'title' => "Economic Risk Alert: {$country->name}",
                    'description' => "Risk score: " . number_format($country->total_score, 1) . "/100. Monitor inflation, currency volatility, and economic indicators.",
                    'country_code' => $country->code,
                    'country_name' => $country->name,
                    'flag_url' => $country->flag_url,
                    'link' => "/country-monitor?country={$country->code}",
                    'icon' => 'fa-chart-line',
                    'color' => $severity === 'critical' ? 'danger' : ($severity === 'high' ? 'warning' : 'info'),
                    'is_resolved' => false,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]);
                $alertsGenerated++;
            }
        }
        
        // 2. Generate news alerts from ALL news (with sentiment distribution)
        $allNews = DB::table('news_cache')
            ->join('countries', 'news_cache.country_code', '=', 'countries.code')
            ->select('news_cache.*', 'countries.name as country_name', 'countries.flag_url')
            ->orderBy('news_cache.published_at', 'desc')
            ->limit(15)
            ->get();

        foreach ($allNews as $news) {
            // Determine severity based on sentiment
            $severity = 'low';
            $color = 'info';
            $icon = 'fa-newspaper';
            
            if ($news->sentiment === 'negative') {
                $severity = 'medium';
                $color = 'warning';
                $icon = 'fa-exclamation-triangle';
            } elseif ($news->sentiment === 'positive') {
                $severity = 'low';
                $color = 'success';
                $icon = 'fa-check-circle';
            }
            
            // Only create alerts for negative sentiment
            if ($news->sentiment !== 'negative') {
                continue;
            }
            
            // Check if alert already exists
            $exists = DB::table('alert_logs')
                ->where('type', 'news')
                ->where('country_code', $news->country_code)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();
                
            if (!$exists) {
                DB::table('alert_logs')->insert([
                    'type' => 'news',
                    'severity' => $severity,
                    'title' => "News Alert: {$news->country_name}",
                    'description' => substr($news->title, 0, 150),
                    'country_code' => $news->country_code,
                    'country_name' => $news->country_name,
                    'flag_url' => $news->flag_url,
                    'link' => $news->url,
                    'icon' => $icon,
                    'color' => $color,
                    'is_resolved' => false,
                    'created_at' => $news->published_at,
                    'updated_at' => now(),
                ]);
                $alertsGenerated++;
            }
        }
        
        $this->info("✅ Generated {$alertsGenerated} new alert logs!");
        
        // Show summary
        $total = DB::table('alert_logs')->where('is_resolved', false)->count();
        $critical = DB::table('alert_logs')->where('is_resolved', false)->where('severity', 'critical')->count();
        $high = DB::table('alert_logs')->where('is_resolved', false)->where('severity', 'high')->count();
        $medium = DB::table('alert_logs')->where('is_resolved', false)->where('severity', 'medium')->count();
        
        $this->table(
            ['Severity', 'Count'],
            [
                ['Critical', $critical],
                ['High', $high],
                ['Medium', $medium],
                ['Total', $total],
            ]
        );
        
        return 0;
    }
}
