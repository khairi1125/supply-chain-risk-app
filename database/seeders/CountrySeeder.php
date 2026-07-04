<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        $positiveWords = [
            ['word' => 'growth'],
            ['word' => 'increase'],
            ['word' => 'profit'],
            ['word' => 'stable'],
            ['word' => 'improve']
        ];

        $negativeWords = [
            ['word' => 'war'],
            ['word' => 'crisis'],
            ['word' => 'inflation'],
            ['word' => 'delay'],
            ['word' => 'disaster']
        ];

        DB::table('positive_words')->insert($positiveWords);
        DB::table('negative_words')->insert($negativeWords);

        $this->command->info('Kamus kata positif dan negatif berhasil dimasukkan!');
    }
}