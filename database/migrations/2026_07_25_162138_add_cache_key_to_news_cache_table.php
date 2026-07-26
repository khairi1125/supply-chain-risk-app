<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_cache', function (Blueprint $table) {
            // Add cache_key for per-query caching (e.g. 'global', 'country_indonesia')
            $table->string('cache_key', 100)->nullable()->default('global')->after('id');
            $table->index('cache_key');
        });

        // Backfill existing rows
        \Illuminate\Support\Facades\DB::table('news_cache')->update(['cache_key' => 'global']);
    }

    public function down(): void
    {
        Schema::table('news_cache', function (Blueprint $table) {
            $table->dropIndex(['cache_key']);
            $table->dropColumn('cache_key');
        });
    }
};
