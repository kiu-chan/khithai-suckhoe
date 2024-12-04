<?php
use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\HealthController;

Route::get('/', [AirQualityController::class, 'index'])->name('air-quality.index');
Route::get('/search', [AirQualityController::class, 'search'])->name('air-quality.search');

Route::get('/map', [MapController::class, 'index'])->name('map.index');

Route::get('/update-aqi', [MapController::class, 'updateAQILayer']);

Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

// routes/web.php
Route::get('/health', [HealthController::class, 'index'])->name('health.index');
Route::get('/factory/{slug}', [FactoryController::class, 'detail'])->name('factory.detail');