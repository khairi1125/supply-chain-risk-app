<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    public function index()
    {
        // Mengambil semua data negara dari database
        // Kita urutkan berdasarkan nama agar rapi
        $countries = DB::table('countries')->orderBy('name', 'asc')->get();

        // Mengembalikan data dalam bentuk format JSON
        return response()->json([
            'success' => true,
            'message' => 'Data negara berhasil diambil',
            'total'   => count($countries),
            'data'    => $countries
        ]);
    }
}