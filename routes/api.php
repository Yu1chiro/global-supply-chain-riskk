<?php

use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\RiskController;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\CurrencyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])->group(function () {

Route::get('/countries', [CountryController::class, 'index']);

Route::get('/countries/search', [CountryController::class, 'search']);
Route::post('/countries/add/{code}', [CountryController::class, 'addByCode']);
Route::get('/countries/{id}', [CountryController::class, 'show']);
Route::get('/countries/data/{code}', [CountryController::class, 'getCountryData']);
Route::get('/countries/{id}/economic-history', [CountryController::class, 'getEconomicHistory']);
Route::post('/countries/sync', [CountryController::class, 'syncAllCountries']);
Route::post('/countries/compare', [CountryController::class, 'compare']);

Route::get('/risk', [RiskController::class, 'index']);
Route::get('/risk/latest/{countryId}', [RiskController::class, 'getLatestRisk']);
Route::post('/risk/calculate', [RiskController::class, 'calculate']);
Route::post('/risk/calculate-all', [RiskController::class, 'calculateAll']);
Route::post('/risk/predict', [RiskController::class, 'predict']);
Route::get('/risk/summary', [RiskController::class, 'getRiskSummary']);
Route::get('/risk/trend/{countryId}', [RiskController::class, 'getRiskTrend']);

Route::get('/ports', [PortController::class, 'index']);

Route::get('/ports/geojson', [PortController::class, 'getAllPortsGeoJson']);
Route::get('/ports/{id}', [PortController::class, 'show']);
Route::post('/ports', [PortController::class, 'store']);
Route::put('/ports/{id}', [PortController::class, 'update']);
Route::delete('/ports/{id}', [PortController::class, 'destroy']);

Route::get('/news', [NewsController::class, 'index']);
Route::post('/news/fetch', [NewsController::class, 'fetch']);        
Route::get('/news/sentiment/{id}', [NewsController::class, 'analyzeSentiment']);
Route::get('/news/sentiment-summary', [NewsController::class, 'getSentimentSummary']);
Route::get('/news/logistics', [NewsController::class, 'getLogisticsNews']);
Route::get('/news/economy', [NewsController::class, 'getEconomyNews']);
Route::get('/news/trade', [NewsController::class, 'getTradeNews']);

Route::get('/currency', [CurrencyController::class, 'getRates']);
Route::get('/currency/rates', [CurrencyController::class, 'getRates']);
Route::get('/currency/rates/{countryCode}', [CurrencyController::class, 'getCountryRates']);
Route::get('/currency/risk', [CurrencyController::class, 'getCurrencyRisk']);
Route::get('/currency/list', [CurrencyController::class, 'getAllCurrencies']);

}); 
