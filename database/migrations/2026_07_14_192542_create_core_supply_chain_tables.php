<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Note: countries table is created by 2026_07_13_121629_create_countries_table.php
        
        // 2. Tabel Pelabuhan (Geospatial & Marine Data)
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->string('name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->onDelete('cascade');
        });

        // 3. Tabel Watchlists (Negara favorit pantauan User)
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('country_code', 3);
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->onDelete('cascade');
        });

        // 4. Kamus Sentimen (Lexicon-Based AI)
        Schema::create('positive_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
        });

        Schema::create('negative_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
        });

        // 5. Tabel Artikel Berita & Sentimen Cache
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->enum('sentiment', ['Positive', 'Neutral', 'Negative'])->default('Neutral');
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->onDelete('cascade');
        });

        // 6. Tabel Risk Scores (Pusat Analitik & Algoritma Scoring)
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->float('weather_score')->default(0);    // Skor dari cuaca
            $table->float('inflation_score')->default(0);  // Skor dari ekonomi/inflasi
            $table->float('news_score')->default(0);       // Skor dari sentimen berita
            $table->float('currency_score')->default(0);   // Skor fluktuasi kurs
            $table->float('total_score')->default(0);      // Total Risk Score
            $table->string('risk_level');                  // Low, Medium, High
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->foreign('country_code')->references('code')->on('countries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_scores');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('negative_words');
        Schema::dropIfExists('positive_words');
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('ports');
        // Note: countries table is dropped by 2026_07_13_121629_create_countries_table.php
    }
};