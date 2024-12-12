<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FactoryController extends Controller
{
    public function detail($slug)
    {
        $factories = [
            'luu-xa' => [
                'name' => 'Luu Xa Cement Company',
                'code' => 'KLV.05',
                'address' => 'Luu Xa Commune, Dai Tu Town, Thai Nguyen',
                'description' => 'Luu Xa Cement Factory was established in 1974. The factory has a production capacity of 1.5 million tons/year.',
                'image' => 'images/Luuxa.png',
                'stats' => [
                    'capacity' => '1.5 million tons/year',
                    'employees' => '500 people',
                    'founded' => '1974',
                    'area' => '50 hectares'
                ]
            ],
            'quan-trieu' => [
                'name' => 'Quan Trieu Cement Joint Stock Company',
                'code' => 'KLV.04',
                'address' => 'Quang Trung Ward, Thai Nguyen City, Thai Nguyen',
                'description' => 'Quan Trieu Cement Joint Stock Company - TISCO was established in 1982. The factory has a designed capacity of 1.2 million tons/year.',
                'image' => 'images/quantrieu.png',
                'stats' => [
                    'capacity' => '1.2 million tons/year',
                    'employees' => '450 people',
                    'founded' => '1982',
                    'area' => '45 hectares'
                ]
            ],
            'cao-ngan' => [
                'name' => 'Cao Ngan Cement Joint Stock Company',
                'code' => 'KLV.01',
                'address' => 'Cao Ngan Commune, Thai Nguyen City, Thai Nguyen',
                'description' => 'Cao Ngan Cement Joint Stock Company was established in 1995. With modern production lines, the capacity reaches 2 million tons/year.',
                'image' => 'images/Caongan.png',
                'stats' => [
                    'capacity' => '2.0 million tons/year',
                    'employees' => '600 people',
                    'founded' => '1995',
                    'area' => '65 hectares'
                ]
            ],
            'quang-son' => [
                'name' => 'Quang Son Cement One Member Co., Ltd',
                'code' => 'KLV.03',
                'address' => 'Quang Son Commune, Dong Hy District, Thai Nguyen',
                'description' => 'Quang Son Cement One Member Limited Company was established in 2001, and is one of the modern cement factories in Thai Nguyen.',
                'image' => 'images/QuangSon.png',
                'stats' => [
                    'capacity' => '1.8 million tons/year',
                    'employees' => '520 people',
                    'founded' => '2001',
                    'area' => '55 hectares'
                ]
            ],
            'la-hien' => [
                'name' => 'La Hien Joint Stock Company',
                'code' => 'KLV.02',
                'address' => 'La Hien Commune, Vo Nhai District, Thai Nguyen',
                'description' => 'La Hien Cement Joint Stock Company was established in 1996, with advanced and environmentally friendly production technology.',
                'image' => 'images/Lahien.png',
                'stats' => [
                    'capacity' => '1.6 million tons/year',
                    'employees' => '480 people',
                    'founded' => '1996',
                    'area' => '48 hectares'
                ]
            ]
        ];

        if (!isset($factories[$slug])) {
            abort(404);
        }

        // Get latest measurements for this factory
        $latestMeasurements = DB::table('air_quality_measurements')
            ->where('location_code', $factories[$slug]['code'])
            ->orderBy('measurement_time', 'desc')
            ->select([
                'measurement_time',
                'dust_level',
                'so2_level',
                'co_level',
                'tsp_level',
                'temperature',
                'humidity',
                'wind_speed',
                'noise_level',
                'aqi'
            ])
            ->first();

        // Format measurements if they exist
        if ($latestMeasurements) {
            $factories[$slug]['environmental_metrics'] = [
                'dust' => number_format($latestMeasurements->dust_level, 3) . ' mg/Nm3',
                'so2' => number_format($latestMeasurements->so2_level, 3) . ' mg/Nm3',
                'co' => number_format($latestMeasurements->co_level, 3) . ' mg/Nm3',
                'tsp' => number_format($latestMeasurements->tsp_level, 3) . ' mg/Nm3'
            ];
            
            $factories[$slug]['weather_metrics'] = [
                'temperature' => number_format($latestMeasurements->temperature, 1) . ' °C',
                'humidity' => number_format($latestMeasurements->humidity, 1) . ' %',
                'wind_speed' => number_format($latestMeasurements->wind_speed, 1) . ' m/s',
                'noise' => number_format($latestMeasurements->noise_level, 1) . ' dB'
            ];
            
            $factories[$slug]['aqi'] = [
                'value' => round($latestMeasurements->aqi),
                'status' => $this->getAQIStatus($latestMeasurements->aqi),
                'color' => $this->getAQIColor($latestMeasurements->aqi)
            ];
            
            $factories[$slug]['last_updated'] = Carbon::parse($latestMeasurements->measurement_time)
                ->format('Y-m-d H:i:s');
        }

        return view('factory.detail', [
            'factory' => $factories[$slug]
        ]);
    }

    private function getAQIStatus($aqi)
    {
        if (!$aqi) return null;
        if ($aqi <= 50) return 'Good';
        if ($aqi <= 100) return 'Moderate';
        if ($aqi <= 150) return 'Poor';
        if ($aqi <= 200) return 'Bad';
        if ($aqi <= 300) return 'Very bad';
        return 'Hazardous';
    }

    private function getAQIColor($aqi)
    {
        if (!$aqi) return '#808080';
        if ($aqi <= 50) return '#00E400';
        if ($aqi <= 100) return '#FFFF00';
        if ($aqi <= 150) return '#FF7E00';
        if ($aqi <= 200) return '#FF0000';
        if ($aqi <= 300) return '#8F3F97';
        return '#7E0023';
    }
}