<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact administrator.',
                ])->onlyInput('email');
            }
            
            $request->session()->regenerate();
            
            // Update last login timestamp
            $user->update(['last_login' => now()]);
            
            // Log activity
            \DB::table('activity_logs')->insert([
                'user_id' => $user->id,
                'action' => 'login',
                'description' => 'User logged in to the system',
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.index'));
            }
            
            return redirect()->intended(route('dashboard.index'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log activity before logout
        if ($user) {
            \DB::table('activity_logs')->insert([
                'user_id' => $user->id,
                'action' => 'logout',
                'description' => 'User logged out from the system',
                'ip_address' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
