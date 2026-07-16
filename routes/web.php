<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PortController as AdminPortController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin' 
            ? redirect()->route('admin.index') 
            : redirect()->route('dashboard.index');
    }
    return redirect()->route('login');
});

// Authentication Routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout Route (authenticated only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User Dashboard Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/country-monitor', [DashboardController::class, 'countryMonitor'])->name('country.monitor');
    Route::get('/port-map', function () {
        return view('dashboard.port-map');
    })->name('port.map');
    Route::get('/weather-map', function () {
        return view('dashboard.weather-map');
    })->name('weather.map');
});

// Admin Routes (require authentication + admin role)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    
    // User Management
    Route::resource('users', AdminUserController::class);
    
    // Port Management
    Route::resource('ports', AdminPortController::class);
    
    // Article Management
    Route::resource('articles', AdminArticleController::class);
});
