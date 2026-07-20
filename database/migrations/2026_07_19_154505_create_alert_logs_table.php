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
        Schema::create('alert_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['economic', 'news', 'weather', 'port'])->index();
            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->index();
            $table->string('title');
            $table->text('description');
            $table->string('country_code', 3)->nullable()->index();
            $table->string('country_name')->nullable();
            $table->string('flag_url')->nullable();
            $table->string('link')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index('created_at');
            $table->index('is_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_logs');
    }
};
