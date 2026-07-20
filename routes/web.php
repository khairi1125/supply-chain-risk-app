<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WatchlistController;
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
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
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
    Route::get('/currency', function () {
        return view('dashboard.currency');
    })->name('currency');
    Route::get('/news', function () {
        $countries = \Illuminate\Support\Facades\DB::table('countries')->orderBy('name')->get();
        return view('dashboard.news', compact('countries'));
    })->name('news');
    Route::get('/compare', function () {
        return view('dashboard.compare');
    })->name('compare');
    Route::get('/my-watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
});

// Admin Routes (require authentication + admin role)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    
    // User Management
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/change-password', [AdminUserController::class, 'changePassword'])->name('users.change-password');
    
    // Port Management
    Route::resource('ports', AdminPortController::class);
    Route::get('ports-map', [AdminPortController::class, 'map'])->name('ports.map');
    Route::post('ports/{port}/toggle-status', [AdminPortController::class, 'toggleStatus'])->name('ports.toggle-status');
    
    // Article Management
    Route::resource('articles', AdminArticleController::class);
    Route::get('articles-import', [AdminArticleController::class, 'import'])->name('articles.import');
    Route::post('articles-fetch-news', [AdminArticleController::class, 'fetchNews'])->name('articles.fetch-news');
    Route::post('articles-import-news', [AdminArticleController::class, 'importNews'])->name('articles.import-news');
    Route::post('articles/{article}/toggle-status', [AdminArticleController::class, 'toggleStatus'])->name('articles.toggle-status');
});
