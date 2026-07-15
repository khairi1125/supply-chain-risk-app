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

// News endpoints
Route::get('/news/{country_code}', [NewsController::class, 'getNews']);

// Port API endpoints
Route::get('/ports', [PortController::class, 'index']);
Route::get('/ports/search', [PortController::class, 'search']);
Route::get('/ports/country/{code}', [PortController::class, 'byCountry']);
Route::get('/ports/{id}', [PortController::class, 'show']);
