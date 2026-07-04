<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RiskController extends Controller
{
    public function calculateRisk($country_code)
    {
        // 1. Ambil data negara dulu
        $country = DB::table('countries')->where('code', strtoupper($country_code))->first();
        if (!$country) {
            return response()->json(['success' => false, 'message' => 'Negara tidak ditemukan'], 404);
        }

        // 2. Analisis Berita (Tanpa Http::get, langsung logika)
        // Kita ambil data dari tabel articles yang sudah kita buat sebelumnya
        $negativeCount = DB::table('articles')
            ->where('country_code', strtoupper($country_code))
            ->where('sentiment', 'Negative')
            ->count();

        // Skor Berita: Makin banyak berita negatif, makin tinggi risikonya
        $newsScore = ($negativeCount > 0) ? ($negativeCount * 20) : 10;
        if ($newsScore > 100) $newsScore = 100;

        // 3. Analisis Kurs (Menggunakan API pihak ketiga saja, karena ini data eksternal)
        $currScore = 50; 
        $currRes = Http::withoutVerifying()->timeout(5)->get('https://api.exchangerate-api.com/v4/latest/USD');
        
        if ($currRes->successful()) {
            $rates = $currRes->json()['rates'];
            $rate = $rates[$country->currency_code] ?? 1;
            // Jika kurs > 15000, risiko dianggap tinggi
            $currScore = ($rate > 15000) ? 80 : 20;
        }

        // 4. Weighted Risk Model (Rumus Utama)
        $totalRisk = ($newsScore * 0.6) + ($currScore * 0.4);
        
        $level = 'Low';
        if ($totalRisk > 70) $level = 'Critical';
        elseif ($totalRisk > 40) $level = 'Medium';

        return response()->json([
            'success' => true,
            'country' => $country->name,
            'weighted_risk_score' => round($totalRisk, 2),
            'risk_level' => $level,
            'details' => [
                'news_risk_score' => $newsScore,
                'currency_risk_score' => $currScore
            ]
        ]);
    }
}