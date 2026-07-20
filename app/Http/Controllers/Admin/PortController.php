<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of ports with search, filter, and pagination
     */
    public function index(Request $request)
    {
        $query = Port::query();
        
        // Search by port name or country
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('port_name', 'LIKE', "%{$search}%")
                  ->orWhere('country_name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by country
        if ($request->has('country') && $request->country != '') {
            $query->where('country_code', $request->country);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $isActive = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $isActive);
        }
        
        // Filter by region
        if ($request->has('region') && $request->region != '') {
            $query->where('region', $request->region);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $ports = $query->paginate(20)->withQueryString();
        
        // Get distinct countries and regions for filters
        $countries = DB::table('ports')
            ->select('country_code', 'country_name')
            ->distinct()
            ->orderBy('country_name')
            ->get();
            
        $regions = DB::table('ports')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');
        
        return view('admin.ports.index', compact('ports', 'countries', 'regions'));
    }

    /**
     * Show the form for creating a new port
     */
    public function create()
    {
        // Get list of countries from countries table
        $countries = DB::table('countries')
            ->select('code', 'name', 'cca2')
            ->orderBy('name')
            ->get();
        
        return view('admin.ports.create', compact('countries'));
    }

    /**
     * Store a newly created port in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'port_name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'country_code' => 'required|string|size:3',
            'country_name' => 'required|string|max:255',
            'region' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'port_type' => 'nullable|string|max:50',
        ]);

        $port = Port::create($validated);

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'port_created',
            'description' => "Created new port: {$port->port_name} ({$port->country_name})",
        ]);

        return redirect()->route('admin.ports.index')
            ->with('success', 'Port created successfully!');
    }

    /**
     * Display the specified port details
     */
    public function show(Port $port)
    {
        return view('admin.ports.show', compact('port'));
    }

    /**
     * Show the form for editing the specified port
     */
    public function edit(Port $port)
    {
        // Get list of countries
        $countries = DB::table('countries')
            ->select('code', 'name', 'cca2')
            ->orderBy('name')
            ->get();
            
        return view('admin.ports.edit', compact('port', 'countries'));
    }

    /**
     * Update the specified port in database
     */
    public function update(Request $request, Port $port)
    {
        $validated = $request->validate([
            'port_name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'country_code' => 'required|string|size:3',
            'country_name' => 'required|string|max:255',
            'region' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'port_type' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        $port->update($validated);

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'port_updated',
            'description' => "Updated port: {$port->port_name}",
        ]);

        return redirect()->route('admin.ports.index')
            ->with('success', 'Port updated successfully!');
    }

    /**
     * Remove the specified port from database
     */
    public function destroy(Port $port)
    {
        $portName = $port->port_name;
        $port->delete();

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'port_deleted',
            'description' => "Deleted port: {$portName}",
        ]);

        return back()->with('success', 'Port deleted successfully!');
    }

    /**
     * Toggle port active status (Activate/Deactivate)
     */
    public function toggleStatus(Port $port)
    {
        $port->is_active = !$port->is_active;
        $port->save();

        $status = $port->is_active ? 'activated' : 'deactivated';

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'port_status_changed',
            'description' => "Port {$port->port_name} has been {$status}",
        ]);

        return response()->json([
            'success' => true,
            'message' => "Port {$status} successfully!",
            'is_active' => $port->is_active
        ]);
    }
    
    /**
     * Display port map view with all ports
     */
    public function map()
    {
        return view('admin.ports.map');
    }
}
