<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function getExchangeRate($country_code)
    {
        // 1. Cari data negara di database kita berdasarkan kode (Contoh: IDN, DEU, JPN)
        $country = DB::table('countries')->where('code', strtoupper($country_code))->first();

        // Jika kode negara tidak ada di database, kembalikan pesan error
        if (!$country) {
            return response()->json([
                'success' => false, 
                'message' => 'Negara tidak ditemukan di database'
            ], 404);
        }

        $currencyCode = $country->currency_code;

        // 2. Tembak ExchangeRate API publik gratis (Patokan nilai dari USD)
        // Kita pakai withoutVerifying() lagi untuk menghindari error SSL XAMPP
        $response = Http::withoutVerifying()
            ->timeout(15)
            ->get('https://api.exchangerate-api.com/v4/latest/USD');

        // 3. Jika berhasil terhubung ke API eksternal
        if ($response->successful()) {
            $rates = $response->json()['rates'];

            // Cek apakah mata uang negara yang dicari ada di dalam daftar live API
            if (isset($rates[$currencyCode])) {
                return response()->json([
                    'success'         => true,
                    'country_name'    => $country->name,
                    'base_currency'   => 'USD',
                    'target_currency' => $currencyCode,
                    'exchange_rate'   => $rates[$currencyCode],
                    'message'         => "Saat ini 1 USD setara dengan " . $rates[$currencyCode] . " " . $currencyCode
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'Data kurs untuk mata uang ' . $currencyCode . ' tidak tersedia di API saat ini.'
                ], 404);
            }
        }

        // Jika API eksternal sedang down
        return response()->json([
            'success' => false, 
            'message' => 'Gagal mengambil data dari penyedia API Kurs eksternal'
        ], 500);
    }
}