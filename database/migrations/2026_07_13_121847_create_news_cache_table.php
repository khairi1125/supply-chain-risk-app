<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('source');
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->integer('positive_score')->default(0);
            $table->integer('negative_score')->default(0);
            $table->timestamp('published_at');
            $table->timestamps();
            
            $table->index('country_code');
            $table->index('sentiment');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_cache');
    }
};
