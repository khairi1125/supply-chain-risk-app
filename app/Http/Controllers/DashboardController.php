<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RiskAlertService;

class DashboardController extends Controller
{
    protected $riskAlertService;

    public function __construct(RiskAlertService $riskAlertService)
    {
        $this->riskAlertService = $riskAlertService;
    }

    public function index()
    {
        // Get statistics
        $stats = [
            'total_countries' => DB::table('countries')->count(),
            'total_ports' => DB::table('ports')->count(),
            'high_risk_countries' => $this->riskAlertService->getAlertCount(),
            'total_news' => DB::table('news_cache')->count(),
        ];

        // Get top 5 high risk countries
        $highRiskCountries = DB::table('risk_scores')
            ->join('countries', 'risk_scores.country_code', '=', 'countries.code')
            ->select('countries.name', 'countries.code', 'countries.flag_url', 'risk_scores.total_score', 'risk_scores.risk_level')
            ->orderBy('risk_scores.total_score', 'desc')
            ->limit(5)
            ->get();

        // Get risk alerts
        $riskAlerts = $this->riskAlertService->getAlerts();

        // Get risk distribution for pie chart
        $riskDistributionRaw = DB::table('risk_scores')
            ->select('risk_level', DB::raw('count(*) as count'))
            ->groupBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level')
            ->toArray();

        $chartData = [
            'pie' => [
                'critical' => $riskDistributionRaw['critical'] ?? 0,
                'high' => $riskDistributionRaw['high'] ?? 0,
                'medium' => $riskDistributionRaw['medium'] ?? 0,
                'low' => $riskDistributionRaw['low'] ?? 0,
            ]
        ];

        // Get REAL historical trend data from risk_score_histories table (last 30 days)
        $histories = DB::table('risk_score_histories')
            ->select('snapshot_date', 'avg_total_score')
            ->orderBy('snapshot_date', 'asc')
            ->limit(30)
            ->get();
        
        $trendData = [];
        $trendLabels = [];
        
        if ($histories->count() > 0) {
            // Use real historical data
            foreach ($histories as $history) {
                $date = \Carbon\Carbon::parse($history->snapshot_date);
                $trendLabels[] = $date->format('M d');
                $trendData[] = round($history->avg_total_score, 1);
            }
        } else {
            // Fallback: if no historical data exists, generate today's data only
            $currentAvg = DB::table('risk_scores')->avg('total_score') ?? 50;
            $trendLabels = ['Today'];
            $trendData = [round($currentAvg, 1)];
        }
        
        $chartData['trend'] = [
            'labels' => $trendLabels,
            'data' => $trendData
        ];

        return view('dashboard.index', compact('stats', 'highRiskCountries', 'riskAlerts', 'chartData'));
    }

    public function countryMonitor()
    {
        // Get all countries
        $countries = DB::table('countries')->orderBy('name')->get();

        // Get all regions for filter
        $regions = DB::table('countries')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return view('dashboard.country-monitor', compact('countries', 'regions'));
    }
}
