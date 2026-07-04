<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CurrencyController; // Tambahkan baris ini
use App\Http\Controllers\Api\NewsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\CountryController;

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/currency/{country_code}', [CurrencyController::class, 'getExchangeRate']);
// Rute untuk berita
Route::get('/news/{country_code}', [NewsController::class, 'getNews']);
use App\Http\Controllers\Api\RiskController;

Route::get('/risk/{country_code}', [RiskController::class, 'calculateRisk']);