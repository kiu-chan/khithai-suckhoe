<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AirQualityMeasurement;
use App\Models\Factory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateAirQualityData extends Command 
{
    protected $signature = 'data:generate-air-quality';
    protected $description = 'Generate hourly air quality measurement data';

    private $ranges = [
        'temperature' => ['min' => 20, 'max' => 35, 'decimal' => 1],
        'humidity' => ['min' => 60, 'max' => 85, 'decimal' => 1],
        'wind_speed' => ['min' => 0.5, 'max' => 1.5, 'decimal' => 1],
        'noise_level' => ['min' => 65, 'max' => 85, 'decimal' => 1],
        'dust_level' => ['min' => 0.15, 'max' => 0.35, 'decimal' => 2],
        'co_level' => ['min' => 2.0, 'max' => 3.5, 'decimal' => 3],
        'so2_level' => ['min' => 0.05, 'max' => 0.12, 'decimal' => 3],
        'tsp_level' => ['min' => 0.10, 'max' => 0.25, 'decimal' => 3]
    ];

    private $monitoring_ranges = [
        'temperature' => ['min' => 20, 'max' => 35, 'decimal' => 1],
        'humidity' => ['min' => 60, 'max' => 85, 'decimal' => 1],
        'wind_speed' => ['min' => 0.5, 'max' => 1.5, 'decimal' => 1],
        'noise_level' => ['min' => 55, 'max' => 75, 'decimal' => 1],
        'dust_level' => ['min' => 0.10, 'max' => 0.25, 'decimal' => 2],
        'co_level' => ['min' => 1.5, 'max' => 2.5, 'decimal' => 3],
        'so2_level' => ['min' => 0.03, 'max' => 0.08, 'decimal' => 3],
        'tsp_level' => ['min' => 0.08, 'max' => 0.15, 'decimal' => 3]
    ];

    private $aqi_levels = [
        'dust_level' => [
            [0, 50, 0, 0.054],
            [51, 100, 0.055, 0.154],
            [101, 150, 0.155, 0.254],
            [151, 200, 0.255, 0.354],
            [201, 300, 0.355, 0.424],
            [301, 400, 0.425, 0.504],
            [401, 500, 0.505, 0.604]
        ],
        'co_level' => [
            [0, 50, 0, 2.0],
            [51, 100, 2.0, 4.0], // Điều chỉnh boundary từ 2.0 thay vì 2.1
            [101, 150, 4.0, 6.0], // Điều chỉnh boundary từ 4.0 thay vì 4.1
            [151, 200, 6.0, 8.0],
            [201, 300, 8.0, 10.0],
            [301, 400, 10.0, 12.0],
            [401, 500, 12.0, 15.0]
        ],
        'so2_level' => [
            [0, 50, 0, 0.035],
            [51, 100, 0.036, 0.075],
            [101, 150, 0.076, 0.185],
            [151, 200, 0.186, 0.304],
            [201, 300, 0.305, 0.604],
            [301, 400, 0.605, 0.804],
            [401, 500, 0.805, 1.004]
        ],
        'tsp_level' => [
            [0, 50, 0, 0.08],
            [51, 100, 0.081, 0.15],
            [101, 150, 0.151, 0.25],
            [151, 200, 0.251, 0.35],
            [201, 300, 0.351, 0.42],
            [301, 400, 0.421, 0.50],
            [401, 500, 0.501, 0.60]
        ]
    ];

    private $previousValues = [];

    public function handle()
    {
        $currentTime = Carbon::now()->startOfHour();
        $this->info('Bắt đầu tạo dữ liệu chất lượng không khí cho: ' . $currentTime);

        $factories = Factory::all();
        foreach ($factories as $factory) {
            $this->generateDataForLocation($factory, 'factory', $currentTime);
        }

        $monitoringStations = DB::table('monitoring_stations')->get();
        foreach ($monitoringStations as $station) {
            $this->generateDataForLocation($station, 'monitoring', $currentTime);
        }

        $this->info('Hoàn thành tạo dữ liệu');
    }

    private function generateDataForLocation($location, $type, $currentTime)
    {
        $locationCode = $location->code;
        
        if (!isset($this->previousValues[$locationCode])) {
            $this->previousValues[$locationCode] = $this->generateInitialValues($type);
        }

        $data = $this->generateMeasurements($locationCode, $type);
        
        $pollutantAQIs = [
            'dust' => $this->calculatePollutantAQI('dust_level', $data['dust_level']),
            'co' => $this->calculatePollutantAQI('co_level', $data['co_level']),
            'so2' => $this->calculatePollutantAQI('so2_level', $data['so2_level']),
            'tsp' => $this->calculatePollutantAQI('tsp_level', $data['tsp_level'])
        ];

        $aqi = max($pollutantAQIs);

        $this->info("\nLocation: $locationCode");
        $this->info("Dust AQI: {$pollutantAQIs['dust']} (Concentration: {$data['dust_level']})");
        $this->info("CO AQI: {$pollutantAQIs['co']} (Concentration: {$data['co_level']})");
        $this->info("SO2 AQI: {$pollutantAQIs['so2']} (Concentration: {$data['so2_level']})");
        $this->info("TSP AQI: {$pollutantAQIs['tsp']} (Concentration: {$data['tsp_level']})");
        $this->info("Final AQI: $aqi\n");

        DB::table('air_quality_measurements')->insert([
            'location_code' => $locationCode,
            'measurement_time' => $currentTime,
            'temperature' => $data['temperature'],
            'humidity' => $data['humidity'],
            'wind_speed' => $data['wind_speed'],
            'noise_level' => $data['noise_level'],
            'dust_level' => $data['dust_level'],
            'co_level' => $data['co_level'],
            'so2_level' => $data['so2_level'],
            'tsp_level' => $data['tsp_level'],
            'aqi' => $aqi,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->previousValues[$locationCode] = $data;
    }

    private function calculatePollutantAQI($pollutant, $concentration)
    {
        foreach ($this->aqi_levels[$pollutant] as $level) {
            list($aqiLow, $aqiHigh, $concLow, $concHigh) = $level;
            
            if ($concentration >= $concLow && $concentration <= $concHigh) {
                $aqi = (($aqiHigh - $aqiLow) / ($concHigh - $concLow)) 
                      * ($concentration - $concLow) 
                      + $aqiLow;
                      
                return round($aqi);
            }
        }
        
        // Nếu nồng độ thấp hơn ngưỡng thấp nhất
        $firstLevel = reset($this->aqi_levels[$pollutant]);
        if ($concentration < $firstLevel[2]) {
            return $firstLevel[0];
        }
        
        // Nếu nồng độ cao hơn ngưỡng cao nhất
        $lastLevel = end($this->aqi_levels[$pollutant]);
        if ($concentration > $lastLevel[3]) {
            return 500;
        }
        
        return 500;
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

    private function getAQIDescription($aqi)
    {
        if ($aqi <= 50) return 'Tốt';
        if ($aqi <= 100) return 'Trung bình';
        if ($aqi <= 150) return 'Kém';
        if ($aqi <= 200) return 'Xấu';
        if ($aqi <= 300) return 'Rất xấu';
        if ($aqi <= 400) return 'Nguy hại';
        return 'Nguy hại nghiêm trọng';
    }
}