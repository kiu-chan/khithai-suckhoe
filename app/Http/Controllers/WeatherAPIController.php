<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherAPIController extends Controller
{
    private $apiKey = 'b5da2b01187e7f3c9d2d8aeffe7228d8';
    private $city = 'Thai Nguyen';
    private $countryCode = 'VN';

    public function getWindData()
    {
        try {
            $response = Http::get('http://api.openweathermap.org/data/2.5/weather', [
                'q' => $this->city . ',' . $this->countryCode,
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'wind' => [
                            'speed' => $data['wind']['speed'] ?? null,
                            'degree' => $data['wind']['deg'] ?? null,
                            'gust' => $data['wind']['gust'] ?? null
                        ],
                        'location' => [
                            'city' => $this->city,
                            'country' => $this->countryCode
                        ],
                        'timestamp' => now()->toIso8601String()
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch weather data'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getWindForecast()
    {
        try {
            $response = Http::get('http://api.openweathermap.org/data/2.5/forecast', [
                'q' => $this->city . ',' . $this->countryCode,
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $forecasts = [];

                foreach ($data['list'] as $forecast) {
                    $forecasts[] = [
                        'datetime' => $forecast['dt_txt'],
                        'wind' => [
                            'speed' => $forecast['wind']['speed'] ?? null,
                            'degree' => $forecast['wind']['deg'] ?? null,
                            'gust' => $forecast['wind']['gust'] ?? null
                        ]
                    ];
                }
                
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'location' => [
                            'city' => $this->city,
                            'country' => $this->countryCode
                        ],
                        'forecasts' => $forecasts,
                        'timestamp' => now()->toIso8601String()
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch forecast data'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}