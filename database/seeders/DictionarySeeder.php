<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DictionarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data positif
        $positiveWords = [
            ['word' => 'growth'],
            ['word' => 'increase'],
            ['word' => 'profit'],
            ['word' => 'stable'],
            ['word' => 'improve']
        ];

        // Data negatif
        $negativeWords = [
            ['word' => 'war'],
            ['word' => 'crisis'],
            ['word' => 'inflation'],
            ['word' => 'delay'],
            ['word' => 'disaster']
        ];

        // Masukkan ke tabel
        DB::table('positive_words')->insert($positiveWords);
        DB::table('negative_words')->insert($negativeWords);
    }
}