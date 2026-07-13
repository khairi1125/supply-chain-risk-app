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
        Schema::create('weather_cache', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->decimal('temperature', 5, 2);
            $table->decimal('rainfall', 5, 2)->default(0);
            $table->decimal('wind_speed', 5, 2)->default(0);
            $table->string('weather_condition');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->timestamp('fetched_at');
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
        Schema::dropIfExists('weather_cache');
    }
};
