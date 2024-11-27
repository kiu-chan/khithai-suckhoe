<?php
use App\Http\Controllers\AirQualityController;

Route::get('/', [AirQualityController::class, 'index'])->name('air-quality.index');
Route::get('/search', [AirQualityController::class, 'search'])->name('air-quality.search');