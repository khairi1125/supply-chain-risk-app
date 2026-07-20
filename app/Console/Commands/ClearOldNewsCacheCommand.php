<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('news:clear-old-cache')]
#[Description('Clear news cache older than 6 hours to force fresh fetch from GNews API')]
class ClearOldNewsCacheCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Clearing old news cache...');
        
        // Delete cache older than 6 hours
        $deleted = DB::table('news_cache')
            ->where('created_at', '<', now()->subHours(6))
            ->delete();
        
        $this->info("✅ Cleared {$deleted} old cached news articles");
        $this->info('📰 Next request will fetch fresh news from GNews API');
        
        // Show current cache status
        $remaining = DB::table('news_cache')->count();
        $this->info("📊 Remaining cached articles: {$remaining}");
        
        return 0;
    }
}
