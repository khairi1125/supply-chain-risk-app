<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\RiskController;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Api\WeatherController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Country API endpoints
Route::get('/countries', [CountryController::class, 'index']); // List with filters
Route::get('/countries/{code}', [CountryController::class, 'show']); // Detailed info
Route::get('/risk/{code}', [CountryController::class, 'getRisk']); // Risk score only
Route::get('/worldbank/{code}', [CountryController::class, 'getWorldBankData']); // GDP & Inflation trends

// Currency endpoints
Route::get('/currency/{country_code}', [CurrencyController::class, 'getExchangeRate']);
Route::get('/currency', [CurrencyController::class, 'getAllRates']);
Route::get('/currency/history/{from}/{to}', [CurrencyController::class, 'getHistory']);

// News endpoints
Route::get('/news/search', [NewsController::class, 'searchNews']);
Route::get('/news/{country_code}', [NewsController::class, 'getNews']);
Route::post('/news/clear-cache', [NewsController::class, 'clearCache']);

// Weather endpoints
Route::get('/weather/global', [WeatherController::class, 'getGlobalWeather']);
Route::get('/weather/{lat}/{lon}', [WeatherController::class, 'getWeatherByCoordinates']);

// Port API endpoints
Route::get('/ports', [PortController::class, 'index']);
Route::get('/ports/search', [PortController::class, 'search']);
Route::get('/ports/country/{code}', [PortController::class, 'byCountry']);
Route::get('/ports/{id}', [PortController::class, 'show']);

// Watchlist API endpoints
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/watchlist', [\App\Http\Controllers\Api\WatchlistController::class, 'store']);
    Route::delete('/watchlist/{id}', [\App\Http\Controllers\Api\WatchlistController::class, 'destroy']);
    Route::post('/watchlist/{id}/refresh', [\App\Http\Controllers\Api\WatchlistController::class, 'refresh']);
});
