<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'USA', 'name' => 'United States', 'cca2' => 'US', 'region' => 'Americas', 'latitude' => 37.0902, 'longitude' => -95.7129],
            ['code' => 'CHN', 'name' => 'China', 'cca2' => 'CN', 'region' => 'Asia', 'latitude' => 35.8617, 'longitude' => 104.1954],
            ['code' => 'IDN', 'name' => 'Indonesia', 'cca2' => 'ID', 'region' => 'Asia', 'latitude' => -0.7893, 'longitude' => 113.9213],
            ['code' => 'JPN', 'name' => 'Japan', 'cca2' => 'JP', 'region' => 'Asia', 'latitude' => 36.2048, 'longitude' => 138.2529],
            ['code' => 'GBR', 'name' => 'United Kingdom', 'cca2' => 'GB', 'region' => 'Europe', 'latitude' => 55.3781, 'longitude' => -3.4360],
            ['code' => 'DEU', 'name' => 'Germany', 'cca2' => 'DE', 'region' => 'Europe', 'latitude' => 51.1657, 'longitude' => 10.4515],
            ['code' => 'SGP', 'name' => 'Singapore', 'cca2' => 'SG', 'region' => 'Asia', 'latitude' => 1.3521, 'longitude' => 103.8198],
            ['code' => 'IND', 'name' => 'India', 'cca2' => 'IN', 'region' => 'Asia', 'latitude' => 20.5937, 'longitude' => 78.9629],
            ['code' => 'AUS', 'name' => 'Australia', 'cca2' => 'AU', 'region' => 'Oceania', 'latitude' => -25.2744, 'longitude' => 133.7751],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(
                ['code' => $country['code']],
                [
                    'name' => $country['name'], 
                    'cca2' => $country['cca2'],
                    'region' => $country['region'],
                    'latitude' => $country['latitude'],
                    'longitude' => $country['longitude']
                ]
            );
        }

        $this->command->info('Data negara berhasil dimasukkan!');
    }
}