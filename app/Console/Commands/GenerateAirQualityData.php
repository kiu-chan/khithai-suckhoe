<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AirQualityMeasurement;
use App\Models\Factory;
use Carbon\Carbon;

class GenerateAirQualityData extends Command
{
    protected $signature = 'data:generate-air-quality';
    protected $description = 'Generate hourly air quality measurement data';

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

    private $previousValues = [];

    public function handle()
    {
        $factories = Factory::all();
        $currentTime = Carbon::now()->startOfHour();

        foreach ($factories as $factory) {
            if (!isset($this->previousValues[$factory->code])) {
                $this->previousValues[$factory->code] = $this->generateInitialValues();
            }

            $data = $this->generateMeasurements($factory->code);
            
            // Generate AQI based on pollutant levels
            $aqi = $this->calculateAQI([
                'dust' => $data['dust_level'],
                'co' => $data['co_level'],
                'so2' => $data['so2_level'],
                'tsp' => $data['tsp_level']
            ]);

            AirQualityMeasurement::create([
                'location_code' => $factory->code,
                'factory_id' => $factory->id,
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

            $this->previousValues[$factory->code] = $data;
        }

        $this->info('Generated air quality data for ' . $currentTime);
    }

    private function calculateAQI($pollutants)
    {
        // Calculate sub-indices for each pollutant
        $indices = [
            ceil($pollutants['dust'] * 200), // Dust index
            ceil($pollutants['co'] * 20),    // CO index
            ceil($pollutants['so2'] * 800),  // SO2 index
            ceil($pollutants['tsp'] * 400)   // TSP index
        ];
        
        // Add some random variation (±20%)
        $variation = rand(-20, 20);
        
        // Take the maximum index and apply variation
        $aqi = max($indices);
        $aqi = max(0, min(500, $aqi * (1 + $variation/100)));
        
        return round($aqi);
    }

    private function generateInitialValues()
    {
        $values = [];
        foreach ($this->ranges as $parameter => $range) {
            $values[$parameter] = $this->generateRandomValue(
                $range['min'],
                $range['max'],
                $range['decimal']
            );
        }
        return $values;
    }

    private function generateMeasurements($locationCode)
    {
        $previous = $this->previousValues[$locationCode];
        $measurements = [];

        foreach ($this->ranges as $parameter => $range) {
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