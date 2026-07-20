<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ActivityLogService
{
    /**
     * Log an activity to the activity_logs table
     * 
     * @param array $data - Activity data including user_id, action, description
     * @return void
     */
    public function log(array $data): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $data['user_id'] ?? null,
            'action' => $data['action'],
            'description' => $data['description'] ?? null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
