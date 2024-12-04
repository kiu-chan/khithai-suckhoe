<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonitoringStation;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index()
    {
        // Lấy dữ liệu monitoring từ database
        $monitoringData = $this->getMonitoringData();
        
        return view('monitoring.index', [
            'monitoringData' => $monitoringData,
            'currentDate' => Carbon::now()->format('jS Y')
        ]);
    }

    private function getMonitoringData()
    {
        // Đây là dữ liệu mẫu, bạn cần thay thế bằng dữ liệu thực từ database
        return [
            [
                'id' => 1,
                'code' => 'K1',
                'aqi' => 174,
                'temperature' => 90,
                'humidity' => 82,
                'noise' => 200,
                'tsp' => 0.60,
                'pb_dust' => 0.100,
                'no2' => 0.8,
                'so2' => 20,
                'co' => 30,
                'pm10' => 120,
                'pm25' => 60
            ],
            [
                'id' => 2,
                'code' => 'K2',
                'aqi' => 95,
                'temperature' => 88,
                'humidity' => 78,
                'noise' => 180,
                'tsp' => 0.55,
                'pb_dust' => 0.090,
                'no2' => 0.6,
                'so2' => 18,
                'co' => 25,
                'pm10' => 100,
                'pm25' => 50
            ],
            [
                'id' => 3,
                'code' => 'K3',
                'aqi' => 45,
                'temperature' => 85,
                'humidity' => 75,
                'noise' => 160,
                'tsp' => 0.48,
                'pb_dust' => 0.080,
                'no2' => 0.5,
                'so2' => 15,
                'co' => 20,
                'pm10' => 80,
                'pm25' => 40
            ]
        ];
    }
}