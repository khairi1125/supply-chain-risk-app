<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            SentimentWordsSeeder::class,
            DictionarySeeder::class,
            PortsSeeder::class,
            DemoUserSeeder::class,
        ]);
    }
}