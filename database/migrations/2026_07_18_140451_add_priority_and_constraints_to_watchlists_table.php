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
        Schema::table('watchlists', function (Blueprint $table) {
            // Add priority column after country_name
            $table->enum('priority', ['low', 'medium', 'high'])
                  ->default('medium')
                  ->after('country_name');
            
            // Add unique constraint on (user_id, country_code)
            $table->unique(['user_id', 'country_code'], 'unique_user_country');
            
            // Add index on created_at for performance
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watchlists', function (Blueprint $table) {
            // Drop index
            $table->dropIndex(['created_at']);
            
            // Drop unique constraint
            $table->dropUnique('unique_user_country');
            
            // Drop priority column
            $table->dropColumn('priority');
        });
    }
};
