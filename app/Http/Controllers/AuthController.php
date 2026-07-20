<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }
        return view('auth.login');
    }
    
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user', // Default role
            'is_active' => true,
            'email_verified_at' => now(), // Auto verify for now
        ]);

        // Log activity
        \DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'action' => 'user_registered',
            'description' => "New user registered: {$user->email}",
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Auto login after registration
        Auth::login($user);
        
        // Update last login
        $user->update(['last_login' => now()]);

        return redirect()->route('dashboard.index')
            ->with('success', 'Registration successful! Welcome to Supply Chain Risk Intelligence.');
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
