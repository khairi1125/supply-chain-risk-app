<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenMeteoService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    protected $openMeteoService;

    public function __construct(OpenMeteoService $openMeteoService)
    {
        $this->middleware('auth');
        $this->openMeteoService = $openMeteoService;
    }

    public function index(Request $request)
    {
        try {
            $latitude = $request->input('lat');
            $longitude = $request->input('lon');

            // Logic untuk mengambil data cuaca
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getByCountry($countryCode)
    {
        try {
            // Logic untuk mengambil cuaca berdasarkan negara
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
