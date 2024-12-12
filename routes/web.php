<?php
use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PlumeController;
use App\Http\Controllers\WeatherForecastController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\WeatherAPIController;

// Route::get('/', [AirQualityController::class, 'index'])->name('air-quality.index');
Route::get('/search', [AirQualityController::class, 'search'])->name('air-quality.search');

Route::get('/map', [MapController::class, 'index'])->name('map.index');

Route::get('/update-aqi', [MapController::class, 'updateAQILayer']);

Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

// routes/web.php
Route::get('/', [HealthController::class, 'index'])->name('health.index');
Route::get('/factory/{slug}', [FactoryController::class, 'detail'])->name('factory.detail');

Route::get('/plume', [PlumeController::class, 'index'])->name('plume.index');
Route::post('/plume/generate', [PlumeController::class, 'generate'])->name('plume.generate');

Route::get('/api/weather-forecast', [WeatherForecastController::class, 'getForecast']);

Route::get('/medical-records', [MedicalRecordController::class, 'index'])->name('medical-records.index');

Route::get('/weather/wind', [WeatherAPIController::class, 'getWindData']);
Route::get('/weather/wind/forecast', [WeatherAPIController::class, 'getWindForecast']);