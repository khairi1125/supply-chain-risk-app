<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_cache', function (Blueprint $table) {
            $table->string('image_url', 1000)->nullable()->after('source');
            $table->string('category', 50)->nullable()->default('logistics')->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('news_cache', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'category']);
        });
    }
};

