<?php

namespace App\Services;

use App\Models\Watchlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WatchlistService
{
    protected $riskScoringService;
    protected $openMeteoService;
    protected $exchangeRateService;
    protected $activityLogService;

    public function __construct(
        RiskScoringService $riskScoringService,
        OpenMeteoService $openMeteoService,
        ExchangeRateService $exchangeRateService,
        ActivityLogService $activityLogService
    ) {
        $this->riskScoringService = $riskScoringService;
        $this->openMeteoService = $openMeteoService;
        $this->exchangeRateService = $exchangeRateService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * Get user's watchlist with enriched data (Task 3.3)
     * 
     * @param int $userId
     * @return array
     */
    public function getUserWatchlist(int $userId): array
    {
        $watchlistEntries = Watchlist::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $enrichedData = [];
        foreach ($watchlistEntries as $entry) {
            $country = DB::table('countries')
                ->where('code', $entry->country_code)
                ->first();

            if (!$country) continue;

            // Get cached risk score (6-hour TTL)
            $riskScore = $this->riskScoringService
                ->getCachedRiskScore($entry->country_code);
            
            // Get weather data (1-hour TTL)
            $weather = DB::table('weather_cache')
                ->where('country_code', $entry->country_code)
                ->where('fetched_at', '>=', now()->subHour())
                ->first();

            // Get exchange rate (only for non-USD currencies)
            $exchangeRate = null;
            if ($country->currency_code && $country->currency_code !== 'USD') {
                $exchangeRate = $this->exchangeRateService
                    ->getRate('USD', $country->currency_code);
            }

            $enrichedData[] = [
                'id' => $entry->id,
                'country_code' => $entry->country_code,
                'country_name' => $entry->country_name,
                'priority' => $entry->priority,
                'notes' => $entry->notes,
                'created_at' => $entry->created_at,
                'updated_at' => $entry->updated_at,
                'country' => $country,
                'risk_score' => $riskScore,
                'weather' => $weather,
                'exchange_rate' => $exchangeRate,
            ];
        }

        return $enrichedData;
    }

    /**
     * Add country to user's watchlist (Task 3.4)
     * 
     * @param int $userId
     * @param string $countryCode
     * @param string $priority
     * @param string|null $notes
     * @return array
     * @throws \Exception
     */
    public function addToWatchlist(
        int $userId,
        string $countryCode,
        string $priority = 'medium',
        ?string $notes = null
    ): array {
        // Check if already exists
        $exists = Watchlist::where('user_id', $userId)
            ->where('country_code', $countryCode)
            ->exists();

        if ($exists) {
            throw new \Exception('This country is already in your watchlist');
        }

        // Get country name
        $country = DB::table('countries')
            ->where('code', $countryCode)
            ->first();

        if (!$country) {
            throw new \Exception('Country not found');
        }

        // Create watchlist entry
        $watchlist = Watchlist::create([
            'user_id' => $userId,
            'country_code' => $countryCode,
            'country_name' => $country->name,
            'priority' => $priority,
            'notes' => $notes,
        ]);

        // Log activity
        $this->activityLogService->log([
            'user_id' => $userId,
            'action' => 'watchlist_added',
            'description' => "Added {$country->name} to watchlist",
        ]);

        return ['success' => true, 'watchlist' => $watchlist];
    }

    /**
     * Remove country from watchlist (Task 3.5)
     * 
     * @param int $userId
     * @param int $watchlistId
     * @return array
     * @throws \Exception
     */
    public function removeFromWatchlist(int $userId, int $watchlistId): array
    {
        $watchlist = Watchlist::where('id', $watchlistId)
            ->where('user_id', $userId)
            ->first();

        if (!$watchlist) {
            throw new \Exception('Watchlist entry not found');
        }

        $countryName = $watchlist->country_name;
        $watchlist->delete();

        // Log activity
        $this->activityLogService->log([
            'user_id' => $userId,
            'action' => 'watchlist_removed',
            'description' => "Removed {$countryName} from watchlist",
        ]);

        return ['success' => true, 'message' => 'Country removed from watchlist'];
    }

    /**
     * Refresh data for a watchlist entry (Task 3.6)
     * 
     * @param int $userId
     * @param int $watchlistId
     * @return array
     * @throws \Exception
     */
    public function refreshWatchlistData(int $userId, int $watchlistId): array
    {
        $watchlist = Watchlist::where('id', $watchlistId)
            ->where('user_id', $userId)
            ->first();

        if (!$watchlist) {
            throw new \Exception('Watchlist entry not found');
        }

        $country = DB::table('countries')
            ->where('code', $watchlist->country_code)
            ->first();

        if (!$country) {
            throw new \Exception('Country data not found');
        }

        // Fetch fresh data from APIs
        $weather = $this->openMeteoService->getWeatherWithCountryCode(
            $country->latitude,
            $country->longitude,
            $country->code
        );

        // Calculate fresh risk score
        $riskScore = $this->riskScoringService->calculateRiskScore(
            $country->code,
            ['weather' => $weather]
        );

        // Get exchange rate
        $exchangeRate = null;
        if ($country->currency_code && $country->currency_code !== 'USD') {
            $exchangeRate = $this->exchangeRateService
                ->getRate('USD', $country->currency_code);
        }

        // Log activity
        $this->activityLogService->log([
            'user_id' => $userId,
            'action' => 'watchlist_refreshed',
            'description' => "Refreshed data for {$country->name}",
        ]);

        return [
            'success' => true,
            'risk_score' => $riskScore,
            'weather' => $weather,
            'exchange_rate' => $exchangeRate,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Calculate summary statistics (Task 3.7)
     * 
     * @param int $userId
     * @return array
     */
    public function calculateSummaryStats(int $userId): array
    {
        $watchlists = Watchlist::where('user_id', $userId)->get();
        $totalCount = $watchlists->count();

        $lowRisk = 0;
        $mediumRisk = 0;
        $highRisk = 0;
        $criticalRisk = 0;
        $totalScore = 0;
        $highestRisk = ['country' => null, 'score' => 0];
        $lowestRisk = ['country' => null, 'score' => 100];
        $mostRecent = null;

        foreach ($watchlists as $entry) {
            $riskScore = $this->riskScoringService
                ->getCachedRiskScore($entry->country_code);

            if ($riskScore) {
                $score = $riskScore['total_score'];
                $totalScore += $score;

                // Count by risk level (0-25 low, 26-50 medium, 51-75 high, 76-100 critical)
                if ($score >= 76) $criticalRisk++;
                elseif ($score >= 51) $highRisk++;
                elseif ($score >= 26) $mediumRisk++;
                else $lowRisk++;

                // Track highest/lowest
                if ($score > $highestRisk['score']) {
                    $highestRisk = [
                        'country' => $entry->country_name,
                        'score' => $score
                    ];
                }
                if ($score < $lowestRisk['score']) {
                    $lowestRisk = [
                        'country' => $entry->country_name,
                        'score' => $score
                    ];
                }
            }

            // Track most recent
            if (!$mostRecent || $entry->created_at > $mostRecent['created_at']) {
                $mostRecent = [
                    'country' => $entry->country_name,
                    'created_at' => $entry->created_at
                ];
            }
        }

        $averageScore = $totalCount > 0 ? $totalScore / $totalCount : 0;

        return [
            'total_count' => $totalCount,
            'low_risk' => $lowRisk,
            'medium_risk' => $mediumRisk,
            'high_risk' => $highRisk,
            'critical_risk' => $criticalRisk,
            'average_score' => round($averageScore, 2),
            'highest_risk' => $highestRisk,
            'lowest_risk' => $lowestRisk,
            'most_recent' => $mostRecent,
        ];
    }

    /**
     * Get recent watchlist activity (Task 3.8)
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentActivity(int $userId, int $limit = 10): array
    {
        return DB::table('activity_logs')
            ->where('user_id', $userId)
            ->whereIn('action', ['watchlist_added', 'watchlist_removed', 'watchlist_refreshed'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
