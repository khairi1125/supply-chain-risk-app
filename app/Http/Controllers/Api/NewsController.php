<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    public function getNews($country_code)
    {
        // 1. Cari nama negara
        $country = DB::table('countries')->where('code', strtoupper($country_code))->first();

        if (!$country) {
            return response()->json(['success' => false, 'message' => 'Negara tidak ditemukan'], 404);
        }

        // 2. Ambil kamus kata dari database dan ubah jadi Array
        $positiveWords = DB::table('positive_words')->pluck('word')->toArray();
        $negativeWords = DB::table('negative_words')->pluck('word')->toArray();

        // 3. Tembak GNews API (Silakan daftar di gnews.io nanti untuk dapat API key asli)
        $apiKey = 'GANTI_DENGAN_API_KEY_GNEWS_NANTI'; 
        $query = urlencode($country->name . ' economy logistics');
        
        $response = Http::withoutVerifying()
            ->timeout(15)
            ->get("https://gnews.io/api/v4/search?q={$query}&lang=en&max=3&apikey={$apiKey}");

        $articles = [];

        // 4. Sistem Fallback: Jika GNews error (karena API Key salah), gunakan berita Dummy!
        if ($response->successful() && isset($response->json()['articles'])) {
            $articles = $response->json()['articles'];
        } else {
            // Berita dummy sengaja dirancang mengandung kata dari database kita
            $articles = [
                [
                    'title' => 'Economic growth and profit increase in ' . $country->name,
                    'description' => 'The economy shows stable growth, bringing profit to investors.'
                ],
                [
                    'title' => 'Unexpected disaster causes logistics delay',
                    'description' => 'A crisis and high inflation caused massive delay in shipping.'
                ],
                [
                    'title' => 'Normal day in ' . $country->name,
                    'description' => 'Just a regular news without much happening.'
                ]
            ];
        }

        // 5. Proses Artikel dengan AI Lexicon Based Sentiment Analysis buatan kita
        $analyzedArticles = [];

        // Ganti bagian looping di dalam fungsi getNews kamu dengan kode ini:

foreach ($articles as $article) {
    // 1. Gabungkan dan bersihkan teks
    $text = strtolower($article['title'] . ' ' . $article['description']);
    // Mengganti semua karakter non-huruf/angka dengan spasi
    $text = preg_replace('/[^a-z0-9]/', ' ', $text); 
    $words = explode(' ', $text);

    $positiveScore = 0;
    $negativeScore = 0;

    foreach ($words as $word) {
        $word = trim($word);
        if (empty($word)) continue;

        // Cek ke Database (ini memastikan kita tidak salah input array)
        if (DB::table('positive_words')->where('word', $word)->exists()) {
            $positiveScore++;
        }
        if (DB::table('negative_words')->where('word', $word)->exists()) {
            $negativeScore++;
        }
    }

    // Penentuan Sentimen
    $sentiment = "Neutral";
    if ($positiveScore > $negativeScore) $sentiment = "Positive";
    elseif ($negativeScore > $positiveScore) $sentiment = "Negative";

    $analyzedArticles[] = [
        'title'          => $article['title'],
        'description'    => $article['description'],
        'positive_score' => $positiveScore,
        'negative_score' => $negativeScore,
        'sentiment'      => $sentiment
    ];
}

        // 6. Kembalikan Output
        return response()->json([
            'success'      => true,
            'country'      => $country->name,
            'total_news'   => count($analyzedArticles),
            'news_data'    => $analyzedArticles
        ]);
    }
}