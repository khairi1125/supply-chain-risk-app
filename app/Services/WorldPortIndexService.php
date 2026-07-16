<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WorldPortIndexService
{
    private const GITHUB_PORTS_URL = 'https://raw.githubusercontent.com/tayljordan/ports/main/ports.json';

    /**
     * Fetch ports data from GitHub repository
     */
    public function fetchPortsData(): array
    {
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(60)->get(self::GITHUB_PORTS_URL);/*  */

            if ($response->successful()) {
                $body = $response->body();
                $data = json_decode($body, true);
                
                // Check if data has 'ports' key
                if (isset($data['ports']) && is_array($data['ports'])) {
                    $ports = $data['ports'];
                    Log::info('World Port Index data fetched successfully', [
                        'total_ports' => count($ports)
                    ]);
                    return $ports;
                }
                
                Log::error('Invalid port data structure', [
                    'keys' => array_keys($data ?? [])
                ]);
                return [];
            }

            Log::error('Failed to fetch World Port Index data', [
                'status' => $response->status()
            ]);
            return [];

        } catch (\Exception $e) {
            Log::error('Exception fetching World Port Index data', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Import ports from JSON data to database
     */
    public function importPorts(): array
    {
        $portsData = $this->fetchPortsData();
        
        if (empty($portsData)) {
            return [
                'success' => false,
                'message' => 'No port data to import',
                'imported' => 0
            ];
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($portsData as $portData) {
                try {
                    // Skip if no coordinates
                    if (!isset($portData['latitude']) || !isset($portData['longitude'])) {
                        $skipped++;
                        continue;
                    }
                    
                    // Skip if no port name
                    if (!isset($portData['wpi_port_name']) || empty($portData['wpi_port_name'])) {
                        $skipped++;
                        continue;
                    }
                    
                    // Map country name to ISO code (we need to do this because data uses full country names)
                    $countryCode = $this->mapCountryNameToCode($portData['country'] ?? '');
                    
                    if (!$countryCode) {
                        $skipped++;
                        continue;
                    }

                    // Skip if port already exists (by name and country)
                    $exists = DB::table('ports')
                        ->where('port_name', $portData['wpi_port_name'])
                        ->where('country_code', $countryCode)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    // Determine region based on country or continent
                    $region = $this->determineRegion($countryCode);
                    
                    // Get country name from countries table
                    $countryName = DB::table('countries')
                        ->where('code', $countryCode)
                        ->value('name');
                    
                    if (!$countryName) {
                        $countryName = $portData['country']; // Fallback to original country name
                    }

                    // Create port entry
                    DB::table('ports')->insert([
                        'port_name' => $portData['wpi_port_name'],
                        'code' => strtoupper(substr($portData['wpi_port_name'], 0, 5)),
                        'country_code' => $countryCode,
                        'country_name' => $countryName,
                        'region' => $region,
                        'latitude' => $portData['latitude'],
                        'longitude' => $portData['longitude'],
                        'port_type' => $portData['port_size'] ?? 'seaport',
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    Log::warning('Failed to import port', [
                        'port' => $portData['wpi_port_name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ]);
                    $errors++;
                }
            }

            DB::commit();

            Log::info('Port import completed', [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

            return [
                'success' => true,
                'message' => "Import completed successfully",
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'total' => count($portsData)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Port import transaction failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'imported' => 0
            ];
        }
    }

    /**
     * Map country name to ISO code
     */
    private function mapCountryNameToCode(string $countryName): ?string
    {
        $mapping = [
            'Indonesia' => 'ID',
            'Singapore' => 'SG',
            'Malaysia' => 'MY',
            'Thailand' => 'TH',
            'Vietnam' => 'VN',
            'Philippines' => 'PH',
            'China' => 'CN',
            'Japan' => 'JP',
            'South Korea' => 'KR',
            'India' => 'IN',
            'United States' => 'US',
            'Canada' => 'CA',
            'Mexico' => 'MX',
            'Brazil' => 'BR',
            'Argentina' => 'AR',
            'Chile' => 'CL',
            'United Kingdom' => 'GB',
            'France' => 'FR',
            'Germany' => 'DE',
            'Italy' => 'IT',
            'Spain' => 'ES',
            'Netherlands' => 'NL',
            'Belgium' => 'BE',
            'Australia' => 'AU',
            'New Zealand' => 'NZ',
            'Russia' => 'RU',
            'Norway' => 'NO',
            'Sweden' => 'SE',
            'Denmark' => 'DK',
            'Poland' => 'PL',
            'Turkey' => 'TR',
            'Egypt' => 'EG',
            'South Africa' => 'ZA',
            'Nigeria' => 'NG',
            'Kenya' => 'KE',
            'Saudi Arabia' => 'SA',
            'United Arab Emirates' => 'AE',
            'Qatar' => 'QA',
            'Kuwait' => 'KW',
            'Oman' => 'OM',
            'Bahrain' => 'BH',
            'Israel' => 'IL',
            'Greece' => 'GR',
            'Portugal' => 'PT',
            'Colombia' => 'CO',
            'Peru' => 'PE',
            'Venezuela' => 'VE',
            'Ecuador' => 'EC',
            'Uruguay' => 'UY',
            'Panama' => 'PA',
            'Costa Rica' => 'CR',
            'Jamaica' => 'JM',
            'Cuba' => 'CU',
            'Pakistan' => 'PK',
            'Bangladesh' => 'BD',
            'Sri Lanka' => 'LK',
            'Myanmar' => 'MM',
            'Cambodia' => 'KH',
            'Taiwan' => 'TW',
            'Hong Kong' => 'HK',
            'Finland' => 'FI',
            'Ireland' => 'IE',
            'Austria' => 'AT',
            'Switzerland' => 'CH',
            'Czech Republic' => 'CZ',
            'Romania' => 'RO',
            'Ukraine' => 'UA',
            'Morocco' => 'MA',
            'Tunisia' => 'TN',
            'Algeria' => 'DZ',
            'Libya' => 'LY',
            'Angola' => 'AO',
            'Mozambique' => 'MZ',
            'Tanzania' => 'TZ',
            'Ghana' => 'GH',
            'Ivory Coast' => 'CI',
            'Senegal' => 'SN',
            'Croatia' => 'HR',
            'Slovenia' => 'SI',
            'Bulgaria' => 'BG',
            'Estonia' => 'EE',
            'Latvia' => 'LV',
            'Lithuania' => 'LT',
            'Iceland' => 'IS',
            'Albania' => 'AL',
            'Serbia' => 'RS',
            'Montenegro' => 'ME',
            'Bosnia and Herzegovina' => 'BA',
            'North Macedonia' => 'MK',
            'Papua New Guinea' => 'PG',
            'Fiji' => 'FJ',
            'Guam' => 'GU',
            'New Caledonia' => 'NC',
            'French Polynesia' => 'PF',
            'Iran' => 'IR',
            'Iraq' => 'IQ',
            'Syria' => 'SY',
            'Lebanon' => 'LB',
            'Jordan' => 'JO',
            'Yemen' => 'YE',
        ];
        
        return $mapping[$countryName] ?? null;
    }

    /**
     * Determine region based on country code
     */
    private function determineRegion(string $countryCode): string
    {
        // Map country codes to regions
        $regionMap = [
            // Asia
            'CN' => 'Asia', 'JP' => 'Asia', 'KR' => 'Asia', 'SG' => 'Asia', 'MY' => 'Asia',
            'TH' => 'Asia', 'VN' => 'Asia', 'PH' => 'Asia', 'ID' => 'Asia', 'IN' => 'Asia',
            'BD' => 'Asia', 'PK' => 'Asia', 'LK' => 'Asia', 'MM' => 'Asia', 'KH' => 'Asia',
            'LA' => 'Asia', 'BN' => 'Asia', 'TL' => 'Asia', 'MV' => 'Asia', 'NP' => 'Asia',
            'BT' => 'Asia', 'MN' => 'Asia', 'KZ' => 'Asia', 'UZ' => 'Asia', 'TM' => 'Asia',
            'KG' => 'Asia', 'TJ' => 'Asia', 'AF' => 'Asia', 'IR' => 'Asia', 'IQ' => 'Asia',
            'SY' => 'Asia', 'JO' => 'Asia', 'LB' => 'Asia', 'IL' => 'Asia', 'PS' => 'Asia',
            'SA' => 'Asia', 'AE' => 'Asia', 'OM' => 'Asia', 'YE' => 'Asia', 'KW' => 'Asia',
            'BH' => 'Asia', 'QA' => 'Asia', 'TR' => 'Asia', 'AM' => 'Asia', 'AZ' => 'Asia',
            'GE' => 'Asia', 'CY' => 'Asia', 'HK' => 'Asia', 'MO' => 'Asia', 'TW' => 'Asia',

            // Europe
            'GB' => 'Europe', 'FR' => 'Europe', 'DE' => 'Europe', 'IT' => 'Europe', 'ES' => 'Europe',
            'PT' => 'Europe', 'NL' => 'Europe', 'BE' => 'Europe', 'GR' => 'Europe', 'PL' => 'Europe',
            'RO' => 'Europe', 'CZ' => 'Europe', 'SE' => 'Europe', 'HU' => 'Europe', 'AT' => 'Europe',
            'BG' => 'Europe', 'DK' => 'Europe', 'FI' => 'Europe', 'SK' => 'Europe', 'NO' => 'Europe',
            'IE' => 'Europe', 'HR' => 'Europe', 'SI' => 'Europe', 'LT' => 'Europe', 'LV' => 'Europe',
            'EE' => 'Europe', 'LU' => 'Europe', 'MT' => 'Europe', 'IS' => 'Europe', 'AL' => 'Europe',
            'RS' => 'Europe', 'BA' => 'Europe', 'MK' => 'Europe', 'ME' => 'Europe', 'MD' => 'Europe',
            'UA' => 'Europe', 'BY' => 'Europe', 'RU' => 'Europe', 'CH' => 'Europe', 'LI' => 'Europe',
            'MC' => 'Europe', 'SM' => 'Europe', 'VA' => 'Europe', 'AD' => 'Europe',

            // North America
            'US' => 'North America', 'CA' => 'North America', 'MX' => 'North America',
            'GT' => 'North America', 'BZ' => 'North America', 'SV' => 'North America',
            'HN' => 'North America', 'NI' => 'North America', 'CR' => 'North America',
            'PA' => 'North America', 'CU' => 'North America', 'JM' => 'North America',
            'HT' => 'North America', 'DO' => 'North America', 'BS' => 'North America',
            'BB' => 'North America', 'TT' => 'North America', 'LC' => 'North America',
            'VC' => 'North America', 'GD' => 'North America', 'AG' => 'North America',
            'DM' => 'North America', 'KN' => 'North America',

            // South America
            'BR' => 'South America', 'AR' => 'South America', 'CL' => 'South America',
            'CO' => 'South America', 'PE' => 'South America', 'VE' => 'South America',
            'EC' => 'South America', 'BO' => 'South America', 'PY' => 'South America',
            'UY' => 'South America', 'GY' => 'South America', 'SR' => 'South America',
            'GF' => 'South America',

            // Africa
            'ZA' => 'Africa', 'EG' => 'Africa', 'NG' => 'Africa', 'KE' => 'Africa',
            'ET' => 'Africa', 'GH' => 'Africa', 'TZ' => 'Africa', 'UG' => 'Africa',
            'DZ' => 'Africa', 'MA' => 'Africa', 'TN' => 'Africa', 'LY' => 'Africa',
            'SD' => 'Africa', 'CM' => 'Africa', 'CI' => 'Africa', 'AO' => 'Africa',
            'MZ' => 'Africa', 'MG' => 'Africa', 'MW' => 'Africa', 'ZM' => 'Africa',
            'ZW' => 'Africa', 'BW' => 'Africa', 'NA' => 'Africa', 'MU' => 'Africa',
            'SC' => 'Africa', 'SN' => 'Africa', 'ML' => 'Africa', 'BF' => 'Africa',
            'NE' => 'Africa', 'TD' => 'Africa', 'SO' => 'Africa', 'ER' => 'Africa',
            'DJ' => 'Africa', 'RW' => 'Africa', 'BI' => 'Africa', 'SS' => 'Africa',

            // Oceania
            'AU' => 'Oceania', 'NZ' => 'Oceania', 'PG' => 'Oceania', 'FJ' => 'Oceania',
            'SB' => 'Oceania', 'VU' => 'Oceania', 'NC' => 'Oceania', 'PF' => 'Oceania',
            'WS' => 'Oceania', 'GU' => 'Oceania', 'KI' => 'Oceania', 'FM' => 'Oceania',
            'TO' => 'Oceania', 'PW' => 'Oceania', 'MH' => 'Oceania', 'NR' => 'Oceania',
            'TV' => 'Oceania',
        ];

        return $regionMap[$countryCode] ?? 'Other';
    }

    /**
     * Get statistics about imported ports
     */
    public function getImportStatistics(): array
    {
        $total = DB::table('ports')->count();
        
        $byRegion = DB::table('ports')
            ->select('region', DB::raw('count(*) as count'))
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'region')
            ->toArray();

        $byCountry = DB::table('ports')
            ->select('country_code', DB::raw('count(*) as count'))
            ->groupBy('country_code')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'country_code')
            ->toArray();

        return [
            'total_ports' => $total,
            'by_region' => $byRegion,
            'top_countries' => $byCountry
        ];
    }
}
