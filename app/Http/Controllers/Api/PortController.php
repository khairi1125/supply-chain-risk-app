<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PortDataService;
use App\Services\OpenMeteoService;
use Illuminate\Http\Request;

class PortController extends Controller
{
    protected $portService;
    protected $weatherService;

    public function __construct(PortDataService $portService, OpenMeteoService $weatherService)
    {
        $this->portService = $portService;
        $this->weatherService = $weatherService;
    }

    /**
     * Get all ports
     * GET /api/ports
     */
    public function index(Request $request)
    {
        try {
            $ports = $this->portService->getAllPortsWithCountry();
            
            return response()->json([
                'success' => true,
                'total' => $ports->count(),
                'data' => $ports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get port detail with weather
     * GET /api/ports/{id}
     */
    public function show($id)
    {
        try {
            $port = \DB::table('ports')
                ->join('countries', 'ports.country_code', '=', 'countries.code')
                ->where('ports.id', $id)
                ->select(
                    'ports.*',
                    'countries.name as country_name_full',
                    'countries.flag_url',
                    'countries.region'
                )
                ->first();

            if (!$port) {
                return response()->json([
                    'success' => false,
                    'message' => 'Port not found'
                ], 404);
            }

            // Get weather for port location
            $weather = $this->weatherService->getWeather($port->latitude, $port->longitude);

            return response()->json([
                'success' => true,
                'data' => [
                    'port' => $port,
                    'weather' => $weather
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search ports by name or country
     * GET /api/ports/search?q=singapore
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');

            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $ports = $this->portService->searchPorts($query);

            return response()->json([
                'success' => true,
                'total' => $ports->count(),
                'data' => $ports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ports by country
     * GET /api/ports/country/{code}
     */
    public function byCountry($countryCode)
    {
        try {
            $ports = $this->portService->getPortsByCountry($countryCode);

            return response()->json([
                'success' => true,
                'total' => $ports->count(),
                'data' => $ports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
