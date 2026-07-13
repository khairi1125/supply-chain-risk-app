<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PortsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ports = [
            ['port_name' => 'Port of Shanghai', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 31.2304, 'longitude' => 121.4737, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Singapore', 'country_code' => 'SGP', 'country_name' => 'Singapore', 'latitude' => 1.2966, 'longitude' => 103.7764, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Rotterdam', 'country_code' => 'NLD', 'country_name' => 'Netherlands', 'latitude' => 51.9225, 'longitude' => 4.4792, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Los Angeles', 'country_code' => 'USA', 'country_name' => 'United States', 'latitude' => 33.7361, 'longitude' => -118.2922, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Hamburg', 'country_code' => 'DEU', 'country_name' => 'Germany', 'latitude' => 53.5753, 'longitude' => 9.8689, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Antwerp', 'country_code' => 'BEL', 'country_name' => 'Belgium', 'latitude' => 51.2896, 'longitude' => 4.4724, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Hong Kong', 'country_code' => 'HKG', 'country_name' => 'Hong Kong', 'latitude' => 22.3000, 'longitude' => 114.1700, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Busan', 'country_code' => 'KOR', 'country_name' => 'South Korea', 'latitude' => 35.1040, 'longitude' => 129.0403, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Dubai', 'country_code' => 'ARE', 'country_name' => 'United Arab Emirates', 'latitude' => 25.2760, 'longitude' => 55.3274, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Tokyo', 'country_code' => 'JPN', 'country_name' => 'Japan', 'latitude' => 35.6532, 'longitude' => 139.7595, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Long Beach', 'country_code' => 'USA', 'country_name' => 'United States', 'latitude' => 33.7547, 'longitude' => -118.2224, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Guangzhou', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 23.1167, 'longitude' => 113.2500, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Qingdao', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 36.0671, 'longitude' => 120.3826, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Tianjin', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 39.0842, 'longitude' => 117.7414, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Ningbo', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 29.8683, 'longitude' => 121.5440, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Shenzhen', 'country_code' => 'CHN', 'country_name' => 'China', 'latitude' => 22.5431, 'longitude' => 114.0579, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Kaohsiung', 'country_code' => 'TWN', 'country_name' => 'Taiwan', 'latitude' => 22.6203, 'longitude' => 120.2861, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Tanjung Pelepas', 'country_code' => 'MYS', 'country_name' => 'Malaysia', 'latitude' => 1.3667, 'longitude' => 103.5500, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Tanjung Priok', 'country_code' => 'IDN', 'country_name' => 'Indonesia', 'latitude' => -6.1049, 'longitude' => 106.8826, 'port_type' => 'Seaport'],
            ['port_name' => 'Port of Jebel Ali', 'country_code' => 'ARE', 'country_name' => 'United Arab Emirates', 'latitude' => 25.0131, 'longitude' => 55.0836, 'port_type' => 'Seaport'],
        ];
        
        foreach ($ports as $port) {
            \DB::table('ports')->insert([
                'port_name' => $port['port_name'],
                'country_code' => $port['country_code'],
                'country_name' => $port['country_name'],
                'latitude' => $port['latitude'],
                'longitude' => $port['longitude'],
                'port_type' => $port['port_type'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
