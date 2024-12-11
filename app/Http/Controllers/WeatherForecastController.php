<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeatherForecastController extends Controller
{
// WeatherForecastController.php
public function getForecast(Request $request)
{
    $forecastTime = $request->query('forecast_time');
    \Log::info('Fetching forecast for: ' . $forecastTime);
    
    $forecastData = DB::table('weather_forecasts')
        ->where('forecast_time', $forecastTime)
        ->select([
            'factory_id',
            'wind_speed',
            'wind_deg'
        ])
        ->get();
    
    \Log::info('Found forecast data: ', $forecastData->toArray());
    
    return response()->json($forecastData);
}
}