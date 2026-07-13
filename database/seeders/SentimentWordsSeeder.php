<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SentimentWordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Positive words
        $positiveWords = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'recovery', 
            'boost', 'expand', 'strong', 'success', 'gain', 'rise', 
            'positive', 'agreement', 'peace', 'export', 'investment', 'development',
            'prosperity', 'advancement', 'progress', 'efficient', 'optimize',
            'partnership', 'cooperation', 'innovation', 'opportunity', 'benefit'
        ];
        
        foreach ($positiveWords as $word) {
            \DB::table('positive_words')->insert([
                'word' => $word,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Negative words
        $negativeWords = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'conflict', 
            'shortage', 'decline', 'fall', 'recession', 'sanction', 'strike', 
            'protest', 'flood', 'storm', 'bankruptcy', 'disruption', 'blockage', 
            'collapse', 'threat', 'violence', 'uncertainty', 'risk', 'damage',
            'loss', 'failure', 'problem', 'emergency', 'tension', 'attack'
        ];
        
        foreach ($negativeWords as $word) {
            \DB::table('negative_words')->insert([
                'word' => $word,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
