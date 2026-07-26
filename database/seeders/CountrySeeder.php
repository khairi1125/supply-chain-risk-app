<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['code' => 'USA', 'name' => 'United States', 'cca2' => 'US'],
            ['code' => 'CHN', 'name' => 'China', 'cca2' => 'CN'],
            ['code' => 'IDN', 'name' => 'Indonesia', 'cca2' => 'ID'],
            ['code' => 'JPN', 'name' => 'Japan', 'cca2' => 'JP'],
            ['code' => 'GBR', 'name' => 'United Kingdom', 'cca2' => 'GB'],
            ['code' => 'DEU', 'name' => 'Germany', 'cca2' => 'DE'],
            ['code' => 'SGP', 'name' => 'Singapore', 'cca2' => 'SG'],
            ['code' => 'IND', 'name' => 'India', 'cca2' => 'IN'],
            ['code' => 'AUS', 'name' => 'Australia', 'cca2' => 'AU'],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(
                ['code' => $country['code']],
                ['name' => $country['name'], 'cca2' => $country['cca2']]
            );
        }

        $this->command->info('Data negara berhasil dimasukkan!');
    }
}