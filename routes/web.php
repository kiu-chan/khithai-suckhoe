<?php
use App\Http\Controllers\AirQualityController;
use App\Http\Controllers\MapController;

Route::get('/', [AirQualityController::class, 'index'])->name('air-quality.index');
Route::get('/search', [AirQualityController::class, 'search'])->name('air-quality.search');

Route::get('/map', [MapController::class, 'index'])->name('map.index');

Route::get('/update-aqi', [MapController::class, 'updateAQILayer']);