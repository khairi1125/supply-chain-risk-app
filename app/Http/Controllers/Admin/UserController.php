<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of users with search, filter, and pagination
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search by name or email
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $isActive = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $isActive);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $users = $query->paginate(15)->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,admin',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'user_created',
            'description' => "Created new user: {$user->email} (Role: {$user->role})",
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user details
     */
    public function show(User $user)
    {
        // Get user's watchlist count
        $watchlistCount = $user->watchlists()->count();
        
        // Get user's recent activity
        $recentActivity = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('admin.users.show', compact('user', 'watchlistCount', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in database
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => 'required|in:user,admin',
            'is_active' => 'required|boolean',
        ]);

        // Prevent admin from changing their own role
        if ($user->id === Auth::id() && $validated['role'] !== $user->role) {
            return back()->with('error', 'You cannot change your own role!');
        }

        $user->update($validated);

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'user_updated',
            'description' => "Updated user: {$user->email}",
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from database
     */
    public function destroy(User $user)
    {
        // Prevent self-delete
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself!');
        }

        $email = $user->email;
        
        // Delete user (cascade will handle watchlists)
        $user->delete();

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'user_deleted',
            'description' => "Deleted user: {$email}",
        ]);

        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user active status (Activate/Deactivate)
     */
    public function toggleStatus(User $user)
    {
        // Prevent deactivating self
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate yourself!'
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'user_status_changed',
            'description' => "User {$user->email} has been {$status}",
        ]);

        return response()->json([
            'success' => true,
            'message' => "User {$status} successfully!",
            'is_active' => $user->is_active
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Log activity
        $this->activityLogService->log([
            'user_id' => Auth::id(),
            'action' => 'user_password_changed',
            'description' => "Changed password for user: {$user->email}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!'
        ]);
    }
}
