<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AirQualityMeasurement;
use App\Models\Factory;
use App\Models\MonitoringStation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateAirQualityData extends Command
{
    protected $signature = 'data:generate-air-quality';
    protected $description = 'Generate hourly air quality measurement data for both factories and monitoring stations';

    private $ranges = [
        'temperature' => ['min' => 20, 'max' => 35, 'decimal' => 1],
        'humidity' => ['min' => 60, 'max' => 85, 'decimal' => 1],
        'wind_speed' => ['min' => 0.5, 'max' => 1.5, 'decimal' => 1],
        'noise_level' => ['min' => 65, 'max' => 85, 'decimal' => 1],
        'dust_level' => ['min' => 0.35, 'max' => 0.65, 'decimal' => 2],
        'co_level' => ['min' => 4.8, 'max' => 5.6, 'decimal' => 3],
        'so2_level' => ['min' => 0.12, 'max' => 0.16, 'decimal' => 3],
        'tsp_level' => ['min' => 0.20, 'max' => 0.32, 'decimal' => 3]
    ];

    // Điều chỉnh giới hạn cho điểm quan trắc (giả định điểm dân cư có mức ô nhiễm thấp hơn)
    private $monitoring_ranges = [
        'temperature' => ['min' => 20, 'max' => 35, 'decimal' => 1],
        'humidity' => ['min' => 60, 'max' => 85, 'decimal' => 1],
        'wind_speed' => ['min' => 0.5, 'max' => 1.5, 'decimal' => 1],
        'noise_level' => ['min' => 55, 'max' => 75, 'decimal' => 1],  // Thấp hơn khu công nghiệp
        'dust_level' => ['min' => 0.15, 'max' => 0.45, 'decimal' => 2], // Thấp hơn khu công nghiệp
        'co_level' => ['min' => 2.5, 'max' => 4.0, 'decimal' => 3],    // Thấp hơn khu công nghiệp
        'so2_level' => ['min' => 0.05, 'max' => 0.12, 'decimal' => 3], // Thấp hơn khu công nghiệp
        'tsp_level' => ['min' => 0.10, 'max' => 0.25, 'decimal' => 3]  // Thấp hơn khu công nghiệp
    ];

    private $previousValues = [];

    public function handle()
    {
        $currentTime = Carbon::now()->startOfHour();

        // Tạo dữ liệu cho nhà máy
        $factories = Factory::all();
        foreach ($factories as $factory) {
            $this->generateDataForLocation($factory, 'factory', $currentTime);
        }

        // Tạo dữ liệu cho điểm quan trắc
        $monitoringStations = DB::table('monitoring_stations')->get();
        foreach ($monitoringStations as $station) {
            $this->generateDataForLocation($station, 'monitoring', $currentTime);
        }

        $this->info('Generated air quality data for ' . $currentTime);
    }

    private function generateDataForLocation($location, $type, $currentTime)
    {
        $locationCode = $location->code;
        
        if (!isset($this->previousValues[$locationCode])) {
            $this->previousValues[$locationCode] = $this->generateInitialValues($type);
        }

        $data = $this->generateMeasurements($locationCode, $type);
        
        // Tính AQI dựa trên loại điểm đo
        $aqi = $this->calculateAQI([
            'dust' => $data['dust_level'],
            'co' => $data['co_level'],
            'so2' => $data['so2_level'],
            'tsp' => $data['tsp_level']
        ], $type);

        // Tạo bản ghi mới
        AirQualityMeasurement::create([
            'location_code' => $locationCode,
            'factory_id' => $type === 'factory' ? $location->id : null,
            'monitoring_station_id' => $type === 'monitoring' ? $location->id : null,
            'measurement_time' => $currentTime,
            'temperature' => $data['temperature'],
            'humidity' => $data['humidity'],
            'wind_speed' => $data['wind_speed'],
            'noise_level' => $data['noise_level'],
            'dust_level' => $data['dust_level'],
            'co_level' => $data['co_level'],
            'so2_level' => $data['so2_level'],
            'tsp_level' => $data['tsp_level'],
            'aqi' => $aqi
        ]);

        $this->previousValues[$locationCode] = $data;
    }

    private function calculateAQI($pollutants, $type)
    {
        // Điều chỉnh hệ số dựa trên loại điểm đo
        $multiplier = $type === 'monitoring' ? 0.8 : 1.0; // Điểm dân cư có xu hướng AQI thấp hơn
        
        // Tính các chỉ số con
        $indices = [
            ceil($pollutants['dust'] * 200 * $multiplier), // Dust index
            ceil($pollutants['co'] * 20 * $multiplier),    // CO index
            ceil($pollutants['so2'] * 800 * $multiplier),  // SO2 index
            ceil($pollutants['tsp'] * 400 * $multiplier)   // TSP index
        ];
        
        // Thêm biến động ngẫu nhiên (±20%)
        $variation = rand(-20, 20);
        
        // Lấy chỉ số cao nhất và áp dụng biến động
        $aqi = max($indices);
        $aqi = max(0, min(500, $aqi * (1 + $variation/100)));
        
        return round($aqi);
    }

    private function generateInitialValues($type)
    {
        $ranges = $type === 'monitoring' ? $this->monitoring_ranges : $this->ranges;
        $values = [];
        
        foreach ($ranges as $parameter => $range) {
            $values[$parameter] = $this->generateRandomValue(
                $range['min'],
                $range['max'],
                $range['decimal']
            );
        }
        
        return $values;
    }

    private function generateMeasurements($locationCode, $type)
    {
        $previous = $this->previousValues[$locationCode];
        $ranges = $type === 'monitoring' ? $this->monitoring_ranges : $this->ranges;
        $measurements = [];

        foreach ($ranges as $parameter => $range) {
            $maxHourlyChange = ($range['max'] - $range['min']) * 0.1;
            $minValue = max($range['min'], $previous[$parameter] - $maxHourlyChange);
            $maxValue = min($range['max'], $previous[$parameter] + $maxHourlyChange);
            
            $measurements[$parameter] = $this->generateRandomValue(
                $minValue,
                $maxValue,
                $range['decimal']
            );
        }

        return $measurements;
    }

    private function generateRandomValue($min, $max, $decimal)
    {
        $value = $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
        return round($value, $decimal);
    }
}