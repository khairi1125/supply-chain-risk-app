<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Check and add columns if they don't exist
            if (!Schema::hasColumn('articles', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('articles', 'url')) {
                $table->string('url')->nullable()->after('title');
            }
            if (!Schema::hasColumn('articles', 'source')) {
                $table->string('source')->nullable()->after('title');
            }
            if (!Schema::hasColumn('articles', 'sentiment')) {
                $table->string('sentiment')->default('neutral')->after('category');
            }
            if (!Schema::hasColumn('articles', 'sentiment_score')) {
                $table->decimal('sentiment_score', 5, 3)->default(0)->after('category');
            }
            if (!Schema::hasColumn('articles', 'sentiment_confidence')) {
                $table->integer('sentiment_confidence')->default(0)->after('category');
            }
        });
        
        // Add indexes if they don't exist
        $indexes = DB::select("SHOW INDEX FROM articles");
        $indexNames = collect($indexes)->pluck('Key_name')->toArray();
        
        Schema::table('articles', function (Blueprint $table) use ($indexNames) {
            if (!in_array('articles_category_index', $indexNames)) {
                $table->index('category');
            }
            if (!in_array('articles_status_index', $indexNames)) {
                $table->index('status');
            }
            if (!in_array('articles_sentiment_index', $indexNames)) {
                $table->index('sentiment');
            }
            if (!in_array('articles_published_at_index', $indexNames)) {
                $table->index('published_at');
            }
        });
        
        // Add full-text index if it doesn't exist
        if (!in_array('articles_search_idx', $indexNames)) {
            // Check if description column exists before creating fulltext index
            if (Schema::hasColumn('articles', 'description')) {
                DB::statement('ALTER TABLE articles ADD FULLTEXT INDEX articles_search_idx (title, description)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Drop indexes
            $indexes = DB::select("SHOW INDEX FROM articles");
            $indexNames = collect($indexes)->pluck('Key_name')->toArray();
            
            if (in_array('articles_category_index', $indexNames)) {
                $table->dropIndex(['category']);
            }
            if (in_array('articles_status_index', $indexNames)) {
                $table->dropIndex(['status']);
            }
            if (in_array('articles_sentiment_index', $indexNames)) {
                $table->dropIndex(['sentiment']);
            }
            if (in_array('articles_published_at_index', $indexNames)) {
                $table->dropIndex(['published_at']);
            }
        });
        
        // Drop fulltext index
        $indexes = DB::select("SHOW INDEX FROM articles");
        $indexNames = collect($indexes)->pluck('Key_name')->toArray();
        if (in_array('articles_search_idx', $indexNames)) {
            DB::statement('ALTER TABLE articles DROP INDEX articles_search_idx');
        }
        
        // Drop columns
        Schema::table('articles', function (Blueprint $table) {
            $columns = Schema::getColumnListing('articles');
            
            if (in_array('description', $columns)) {
                $table->dropColumn('description');
            }
            if (in_array('url', $columns)) {
                $table->dropColumn('url');
            }
            if (in_array('source', $columns)) {
                $table->dropColumn('source');
            }
            if (in_array('sentiment', $columns)) {
                $table->dropColumn('sentiment');
            }
            if (in_array('sentiment_score', $columns)) {
                $table->dropColumn('sentiment_score');
            }
            if (in_array('sentiment_confidence', $columns)) {
                $table->dropColumn('sentiment_confidence');
            }
        });
    }
};
