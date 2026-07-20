<?php

namespace App\Http\Controllers;

use App\Services\WatchlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WatchlistController extends Controller
{
    protected $watchlistService;

    public function __construct(WatchlistService $watchlistService)
    {
        // Middleware sudah di-handle di routes/web.php
        $this->watchlistService = $watchlistService;
    }

    /**
     * Display the watchlist dashboard (Task 4.2)
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $userId = Auth::id();

        // Get watchlist data with enriched information
        $watchlists = $this->watchlistService->getUserWatchlist($userId);

        // Get summary statistics
        $summaryStats = $this->watchlistService->calculateSummaryStats($userId);

        // Get recent activity (last 10 entries)
        $recentActivity = $this->watchlistService->getRecentActivity($userId, 10);

        // Get all countries for dropdown (ordered alphabetically)
        $countries = DB::table('countries')->orderBy('name')->get();

        // Get distinct regions for filter dropdown
        $regions = DB::table('countries')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return view('watchlist.index', compact(
            'watchlists',
            'summaryStats',
            'recentActivity',
            'countries',
            'regions'
        ));
    }
}
