<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortDataService
{
    /**
     * Get major world ports (static dataset)
     * Based on World Port Index
     */
    public function getMajorPorts()
    {
        return [
            // Asia Pacific
            ['name' => 'Port of Shanghai', 'country_code' => 'CHN', 'latitude' => 31.2304, 'longitude' => 121.4737, 'type' => 'major'],
            ['name' => 'Port of Singapore', 'country_code' => 'SGP', 'latitude' => 1.2644, 'longitude' => 103.8221, 'type' => 'major'],
            ['name' => 'Port of Shenzhen', 'country_code' => 'CHN', 'latitude' => 22.5431, 'longitude' => 114.0579, 'type' => 'major'],
            ['name' => 'Port of Ningbo-Zhoushan', 'country_code' => 'CHN', 'latitude' => 29.8683, 'longitude' => 121.544, 'type' => 'major'],
            ['name' => 'Port of Hong Kong', 'country_code' => 'HKG', 'latitude' => 22.2793, 'longitude' => 114.1628, 'type' => 'major'],
            ['name' => 'Port of Busan', 'country_code' => 'KOR', 'latitude' => 35.0951, 'longitude' => 129.0401, 'type' => 'major'],
            ['name' => 'Port of Guangzhou', 'country_code' => 'CHN', 'latitude' => 23.1291, 'longitude' => 113.2644, 'type' => 'major'],
            ['name' => 'Port of Qingdao', 'country_code' => 'CHN', 'latitude' => 36.0671, 'longitude' => 120.3826, 'type' => 'major'],
            ['name' => 'Port of Tianjin', 'country_code' => 'CHN', 'latitude' => 38.9517, 'longitude' => 117.7658, 'type' => 'major'],
            ['name' => 'Port of Tokyo', 'country_code' => 'JPN', 'latitude' => 35.6532, 'longitude' => 139.7573, 'type' => 'major'],
            ['name' => 'Port of Yokohama', 'country_code' => 'JPN', 'latitude' => 35.4437, 'longitude' => 139.6380, 'type' => 'major'],
            ['name' => 'Port of Jakarta', 'country_code' => 'IDN', 'latitude' => -6.1051, 'longitude' => 106.8817, 'type' => 'major'],
            ['name' => 'Port of Manila', 'country_code' => 'PHL', 'latitude' => 14.5995, 'longitude' => 120.9842, 'type' => 'major'],
            ['name' => 'Port of Bangkok', 'country_code' => 'THA', 'latitude' => 13.7563, 'longitude' => 100.5018, 'type' => 'major'],
            ['name' => 'Port of Ho Chi Minh', 'country_code' => 'VNM', 'latitude' => 10.8231, 'longitude' => 106.6297, 'type' => 'major'],
            ['name' => 'Port of Colombo', 'country_code' => 'LKA', 'latitude' => 6.9271, 'longitude' => 79.8612, 'type' => 'major'],
            ['name' => 'Port of Mumbai', 'country_code' => 'IND', 'latitude' => 18.9388, 'longitude' => 72.8354, 'type' => 'major'],
            ['name' => 'Port of Sydney', 'country_code' => 'AUS', 'latitude' => -33.8568, 'longitude' => 151.2153, 'type' => 'major'],
            ['name' => 'Port of Melbourne', 'country_code' => 'AUS', 'latitude' => -37.8136, 'longitude' => 144.9631, 'type' => 'major'],
            
            // Europe
            ['name' => 'Port of Rotterdam', 'country_code' => 'NLD', 'latitude' => 51.9244, 'longitude' => 4.4777, 'type' => 'major'],
            ['name' => 'Port of Antwerp', 'country_code' => 'BEL', 'latitude' => 51.2194, 'longitude' => 4.4025, 'type' => 'major'],
            ['name' => 'Port of Hamburg', 'country_code' => 'DEU', 'latitude' => 53.5511, 'longitude' => 9.9937, 'type' => 'major'],
            ['name' => 'Port of Valencia', 'country_code' => 'ESP', 'latitude' => 39.4699, 'longitude' => -0.3763, 'type' => 'major'],
            ['name' => 'Port of Barcelona', 'country_code' => 'ESP', 'latitude' => 41.3851, 'longitude' => 2.1734, 'type' => 'major'],
            ['name' => 'Port of Piraeus', 'country_code' => 'GRC', 'latitude' => 37.9386, 'longitude' => 23.6473, 'type' => 'major'],
            ['name' => 'Port of Felixstowe', 'country_code' => 'GBR', 'latitude' => 51.9613, 'longitude' => 1.3511, 'type' => 'major'],
            ['name' => 'Port of Le Havre', 'country_code' => 'FRA', 'latitude' => 49.4944, 'longitude' => 0.1079, 'type' => 'major'],
            ['name' => 'Port of Genoa', 'country_code' => 'ITA', 'latitude' => 44.4056, 'longitude' => 8.9463, 'type' => 'major'],
            ['name' => 'Port of Marseille', 'country_code' => 'FRA', 'latitude' => 43.2965, 'longitude' => 5.3698, 'type' => 'major'],
            
            // Americas
            ['name' => 'Port of Los Angeles', 'country_code' => 'USA', 'latitude' => 33.7406, 'longitude' => -118.2726, 'type' => 'major'],
            ['name' => 'Port of Long Beach', 'country_code' => 'USA', 'latitude' => 33.7701, 'longitude' => -118.1937, 'type' => 'major'],
            ['name' => 'Port of New York/New Jersey', 'country_code' => 'USA', 'latitude' => 40.6692, 'longitude' => -74.0445, 'type' => 'major'],
            ['name' => 'Port of Savannah', 'country_code' => 'USA', 'latitude' => 32.0809, 'longitude' => -81.0912, 'type' => 'major'],
            ['name' => 'Port of Houston', 'country_code' => 'USA', 'latitude' => 29.7604, 'longitude' => -95.3698, 'type' => 'major'],
            ['name' => 'Port of Vancouver', 'country_code' => 'CAN', 'latitude' => 49.2827, 'longitude' => -123.1207, 'type' => 'major'],
            ['name' => 'Port of Santos', 'country_code' => 'BRA', 'latitude' => -23.9608, 'longitude' => -46.3334, 'type' => 'major'],
            ['name' => 'Port of Manzanillo', 'country_code' => 'MEX', 'latitude' => 19.0543, 'longitude' => -104.3188, 'type' => 'major'],
            ['name' => 'Port of Cartagena', 'country_code' => 'COL', 'latitude' => 10.3910, 'longitude' => -75.4794, 'type' => 'major'],
            ['name' => 'Port of Buenos Aires', 'country_code' => 'ARG', 'latitude' => -34.6037, 'longitude' => -58.3816, 'type' => 'major'],
            
            // Middle East & Africa
            ['name' => 'Port of Dubai', 'country_code' => 'ARE', 'latitude' => 25.2048, 'longitude' => 55.2708, 'type' => 'major'],
            ['name' => 'Port of Jeddah', 'country_code' => 'SAU', 'latitude' => 21.4858, 'longitude' => 39.1925, 'type' => 'major'],
            ['name' => 'Port of Suez Canal', 'country_code' => 'EGY', 'latitude' => 30.0444, 'longitude' => 32.3605, 'type' => 'major'],
            ['name' => 'Port of Durban', 'country_code' => 'ZAF', 'latitude' => -29.8587, 'longitude' => 31.0218, 'type' => 'major'],
            ['name' => 'Port of Cape Town', 'country_code' => 'ZAF', 'latitude' => -33.9249, 'longitude' => 18.4241, 'type' => 'major'],
            ['name' => 'Port of Lagos', 'country_code' => 'NGA', 'latitude' => 6.4541, 'longitude' => 3.3947, 'type' => 'major'],
            ['name' => 'Port of Mombasa', 'country_code' => 'KEN', 'latitude' => -4.0435, 'longitude' => 39.6682, 'type' => 'major'],
        ];
    }
    
    /**
     * Import ports to database
     */
    public function importPorts()
    {
        $ports = $this->getMajorPorts();
        $imported = 0;
        $updated = 0;
        
        foreach ($ports as $port) {
            // Get country name
            $country = DB::table('countries')
                ->where('code', $port['country_code'])
                ->first();
            
            $exists = DB::table('ports')
                ->where('port_name', $port['name'])
                ->where('country_code', $port['country_code'])
                ->exists();
            
            if ($exists) {
                DB::table('ports')
                    ->where('port_name', $port['name'])
                    ->where('country_code', $port['country_code'])
                    ->update([
                        'latitude' => $port['latitude'],
                        'longitude' => $port['longitude'],
                        'port_type' => $port['type'],
                        'updated_at' => now()
                    ]);
                $updated++;
            } else {
                DB::table('ports')->insert([
                    'port_name' => $port['name'],
                    'country_code' => $port['country_code'],
                    'country_name' => $country ? $country->name : 'Unknown',
                    'latitude' => $port['latitude'],
                    'longitude' => $port['longitude'],
                    'port_type' => $port['type'],
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $imported++;
            }
        }
        
        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => count($ports)
        ];
    }
    
    /**
     * Get all ports with country info
     */
    public function getAllPortsWithCountry()
    {
        return DB::table('ports')
            ->leftJoin('countries', 'ports.country_code', '=', 'countries.code')
            ->select(
                'ports.id',
                'ports.port_name',
                'ports.code',
                'ports.country_code',
                'ports.country_name',
                'ports.region',
                'ports.latitude',
                'ports.longitude',
                'ports.port_type',
                'ports.is_active',
                'countries.name as country_name_full',
                'countries.flag_url'
            )
            ->where('ports.is_active', 1)
            ->orderBy('ports.port_name')
            ->get();
    }
    
    /**
     * Get ports by country
     */
    public function getPortsByCountry($countryCode)
    {
        return DB::table('ports')
            ->where('country_code', $countryCode)
            ->where('is_active', 1)
            ->orderBy('port_name')
            ->get();
    }
    
    /**
     * Search ports by name or country
     */
    public function searchPorts($query)
    {
        return DB::table('ports')
            ->leftJoin('countries', 'ports.country_code', '=', 'countries.code')
            ->where(function($q) use ($query) {
                $q->where('ports.port_name', 'LIKE', "%{$query}%")
                  ->orWhere('ports.country_name', 'LIKE', "%{$query}%")
                  ->orWhere('countries.name', 'LIKE', "%{$query}%");
            })
            ->where('ports.is_active', 1)
            ->select(
                'ports.id',
                'ports.port_name',
                'ports.code',
                'ports.country_code',
                'ports.country_name',
                'ports.region',
                'ports.latitude',
                'ports.longitude',
                'ports.port_type',
                'countries.name as country_name_full',
                'countries.flag_url'
            )
            ->limit(50)
            ->get();
    }
}
