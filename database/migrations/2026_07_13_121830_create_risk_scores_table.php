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
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->decimal('weather_score', 5, 2)->default(0);
            $table->decimal('inflation_score', 5, 2)->default(0);
            $table->decimal('currency_score', 5, 2)->default(0);
            $table->decimal('news_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamp('calculated_at');
            $table->timestamps();
            
            $table->index('country_code');
            $table->index('risk_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
    }
};
