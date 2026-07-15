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
        // Clear existing data
        \DB::table('positive_words')->truncate();
        \DB::table('negative_words')->truncate();
        
        // Positive words (Supply Chain & Economic context)
        $positiveWords = [
            // Economic Growth
            'growth', 'increase', 'profit', 'stable', 'improve', 'recovery', 
            'boost', 'expand', 'strong', 'success', 'gain', 'rise', 
            'prosperity', 'advancement', 'progress', 'development',
            
            // Trade & Business
            'export', 'investment', 'partnership', 'cooperation', 'agreement',
            'deal', 'trade', 'surplus', 'efficient', 'optimize', 'productive',
            
            // Positive Indicators
            'positive', 'peace', 'innovation', 'opportunity', 'benefit',
            'strengthen', 'healthy', 'robust', 'resilient', 'thriving',
            'accelerate', 'momentum', 'favorable', 'advantage', 'competitive',
            
            // Supply Chain Positive
            'delivery', 'supply', 'availability', 'accessible', 'smooth',
            'streamline', 'timely', 'reliable', 'quality', 'excellence',
            'solution', 'breakthrough', 'upgrade', 'enhance', 'modernize',
            
            // Market Positive
            'demand', 'consumer', 'spending', 'confidence', 'optimism',
            'rebound', 'rally', 'bullish', 'peak', 'boom', 'thrive'
        ];
        
        foreach ($positiveWords as $word) {
            \DB::table('positive_words')->insert([
                'word' => $word,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Negative words (Supply Chain & Economic context)
        $negativeWords = [
            // Conflict & Crisis
            'war', 'crisis', 'conflict', 'attack', 'violence', 'protest',
            'strike', 'sanction', 'tension', 'threat', 'terrorism', 'instability',
            
            // Economic Negative
            'inflation', 'recession', 'decline', 'fall', 'collapse', 'bankruptcy',
            'debt', 'deficit', 'unemployment', 'downturn', 'bearish', 'crash',
            
            // Supply Chain Disruption
            'delay', 'shortage', 'disruption', 'blockage', 'bottleneck', 'congestion',
            'backlog', 'stuck', 'halt', 'suspended', 'cancelled', 'shutdown',
            
            // Natural Disasters
            'disaster', 'flood', 'storm', 'hurricane', 'typhoon', 'earthquake',
            'drought', 'wildfire', 'tsunami', 'cyclone', 'tornado',
            
            // Business Negative
            'loss', 'failure', 'problem', 'emergency', 'damage', 'destruction',
            'risk', 'uncertainty', 'volatile', 'weak', 'poor', 'slow',
            'decrease', 'drop', 'plunge', 'slump', 'worsen', 'deteriorate',
            
            // Supply Chain Issues
            'scarce', 'unavailable', 'missing', 'broken', 'faulty', 'defective',
            'contaminated', 'expired', 'recalled', 'banned', 'restricted'
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
