<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RiskAlertService
{
    /**
     * Get all risk alerts aggregated from multiple sources
     */
    public function getAlerts()
    {
        // Cache alerts for 5 minutes
        return Cache::remember('risk_alerts', 300, function () {
            // Get all unresolved alerts from database, sorted by severity and created_at
            $alerts = DB::table('alert_logs')
                ->where('is_resolved', false)
                ->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low')")
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function($alert) {
                    return [
                        'type' => $alert->type,
                        'severity' => $alert->severity,
                        'title' => $alert->title,
                        'description' => $alert->description,
                        'country_code' => $alert->country_code,
                        'country_name' => $alert->country_name,
                        'flag_url' => $alert->flag_url,
                        'link' => $alert->link,
                        'icon' => $alert->icon,
                        'color' => $alert->color,
                        'timestamp' => \Carbon\Carbon::parse($alert->created_at)->diffForHumans(),
                    ];
                })
                ->toArray();

            return $alerts;
        });
    }

    /**
     * Get count of alerts by severity
     */
    public function getAlertCount($severity = null)
    {
        if ($severity) {
            return DB::table('alert_logs')
                ->where('is_resolved', false)
                ->where('severity', $severity)
                ->count();
        }

        return DB::table('alert_logs')
            ->where('is_resolved', false)
            ->count();
    }
    /**
     * Clear alerts cache
     */
    public function clearCache()
    {
        Cache::forget('risk_alerts');
    }
    
    /**
     * Create or update alert log in database
     */
    private function createOrUpdateAlertLog($data)
    {
        // Check if similar alert exists in last 24 hours
        $existing = DB::table('alert_logs')
            ->where('type', $data['type'])
            ->where('country_code', $data['country_code'])
            ->where('is_resolved', false)
            ->where('created_at', '>=', now()->subHours(24))
            ->first();
            
        if ($existing) {
            // Update existing alert
            DB::table('alert_logs')
                ->where('id', $existing->id)
                ->update([
                    'severity' => $data['severity'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'updated_at' => now(),
                ]);
                
            return $existing;
        }
        
        // Create new alert
        $id = DB::table('alert_logs')->insertGetId([
            'type' => $data['type'],
            'severity' => $data['severity'],
            'title' => $data['title'],
            'description' => $data['description'],
            'country_code' => $data['country_code'],
            'country_name' => $data['country_name'],
            'flag_url' => $data['flag_url'] ?? null,
            'link' => $data['link'] ?? null,
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'is_resolved' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return DB::table('alert_logs')->where('id', $id)->first();
    }
}
