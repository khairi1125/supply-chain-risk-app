<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WatchlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WatchlistController extends Controller
{
    protected $watchlistService;

    public function __construct(WatchlistService $watchlistService)
    {
        // Middleware sudah di-handle di routes/api.php
        $this->watchlistService = $watchlistService;
    }

    /**
     * Add country to watchlist (Task 5.2)
     * POST /api/watchlist
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string|size:3',
            'priority' => 'required|in:low,medium,high',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->watchlistService->addToWatchlist(
                Auth::id(),
                $request->country_code,
                $request->priority,
                $request->notes
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove country from watchlist (Task 5.3)
     * DELETE /api/watchlist/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $result = $this->watchlistService->removeFromWatchlist(
                Auth::id(),
                $id
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Refresh watchlist entry data (Task 5.4)
     * POST /api/watchlist/{id}/refresh
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh($id)
    {
        try {
            $result = $this->watchlistService->refreshWatchlistData(
                Auth::id(),
                $id
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
