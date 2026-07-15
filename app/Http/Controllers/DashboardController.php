<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [
            'total_countries' => DB::table('countries')->count(),
            'total_ports' => DB::table('ports')->count(),
            'high_risk_countries' => DB::table('risk_scores')
                ->where('total_score', '>', 50)
                ->count(),
            'total_news' => DB::table('news_cache')->count(),
        ];

        // Get top 5 high risk countries
        $highRiskCountries = DB::table('risk_scores')
            ->join('countries', 'risk_scores.country_code', '=', 'countries.code')
            ->select('countries.name', 'countries.code', 'countries.flag_url', 'risk_scores.total_score', 'risk_scores.risk_level')
            ->orderBy('risk_scores.total_score', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'highRiskCountries'));
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
