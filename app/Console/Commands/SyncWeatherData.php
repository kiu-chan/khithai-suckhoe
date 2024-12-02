<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncWeatherData extends Command
{
    protected $signature = 'weather:sync';
    protected $description = 'Sync weather data from OpenWeatherMap for both factories and weather stations';

    protected $apiKey = 'b5da2b01187e7f3c9d2d8aeffe7228d8';

    public function handle()
    {
        $this->info('Starting weather sync process at: ' . Carbon::now());
        
        // Sync dữ liệu từ nhà máy
        $this->syncFromFactories();
        
        // Sync dữ liệu từ trạm thời tiết
        $this->syncFromWeatherStations();
        
        // Cập nhật dữ liệu không khí
        $this->updateAirQualityData();
        
        $this->info('Weather sync process completed at: ' . Carbon::now());
    }

    protected function syncFromFactories()
    {
        $factories = DB::table('factories')
            ->select(['code as factory_code', 'name', 'geom'])
            ->whereNotNull('geom')
            ->get();

        $this->info("\nStarting sync for {$factories->count()} factories...");

        foreach ($factories as $factory) {
            $this->syncFactoryData($factory);
        }
    }

    protected function syncFromWeatherStations()
    {
        $stations = DB::table('weather_stations')
            ->select(['station_code', 'station_name as name', 'geom'])
            ->whereNotNull('geom')
            ->get();

        $this->info("\nStarting sync for {$stations->count()} weather stations...");

        foreach ($stations as $station) {
            $this->syncStationData($station);
        }
    }

    protected function syncFactoryData($factory)
    {
        try {
            $this->info("\nProcessing factory: {$factory->name}");
            
            $point = DB::selectOne(
                "SELECT ST_X(geom) as longitude, ST_Y(geom) as latitude 
                FROM factories 
                WHERE code = ?", 
                [$factory->factory_code]
            );
            
            if (!$point) {
                $this->warn("No coordinates found for factory {$factory->name}");
                return;
            }

            $this->info("Factory coordinates: {$point->latitude}, {$point->longitude}");
            
            $weatherData = $this->getWeatherData($point->latitude, $point->longitude);
            $this->info("Received weather data for factory:");
            $this->info("Temperature: " . $weatherData['main']['temp'] . "°C");
            $this->info("Humidity: " . $weatherData['main']['humidity'] . "%");
            
            // Kiểm tra dữ liệu trong vòng 1 giờ
            $existingRecord = DB::table('factory_weather_data')
                ->where('factory_code', $factory->factory_code)
                ->where('measurement_time', '>', Carbon::now()->subHour())
                ->exists();

            if ($existingRecord) {
                $this->info("Skipping insert - Record exists within the last hour for factory {$factory->name}");
                return;
            }

            // Chuẩn bị dữ liệu để insert
            $insertData = [
                'factory_code' => $factory->factory_code,
                'measurement_time' => Carbon::now(),
                'temperature' => $weatherData['main']['temp'],
                'humidity' => $weatherData['main']['humidity'],
                'wind_speed' => $weatherData['wind']['speed'],
                'wind_direction' => $weatherData['wind']['deg'] ?? null,
                'air_pressure' => $weatherData['main']['pressure'],
                'rainfall' => $weatherData['rain']['1h'] ?? 0,
                'created_at' => Carbon::now()
            ];
            
            $this->info("Inserting factory weather data:");
            $this->table(['Field', 'Value'], collect($insertData)->map(function($value, $key) {
                return [$key, is_null($value) ? 'NULL' : $value];
            })->toArray());

            // Insert vào factory_weather_data
            DB::table('factory_weather_data')->insert($insertData);
            
            // Cập nhật bảng air_quality_measurements
            $this->updateAirQualityMeasurement($factory->factory_code, $weatherData);
            
            $this->info("✓ Successfully inserted weather data for factory: {$factory->name}");
            
        } catch (\Exception $e) {
            $this->error("Error syncing weather data for factory {$factory->name}: " . $e->getMessage());
            \Log::error("Weather sync error for factory {$factory->factory_code}", [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function syncStationData($station)
    {
        try {
            $this->info("\nProcessing station: {$station->name}");
            
            $point = DB::selectOne(
                "SELECT ST_X(geom) as longitude, ST_Y(geom) as latitude 
                FROM weather_stations 
                WHERE station_code = ?", 
                [$station->station_code]
            );
            
            if (!$point) {
                $this->warn("No coordinates found for station {$station->name}");
                return;
            }

            $this->info("Station coordinates: {$point->latitude}, {$point->longitude}");
            
            $weatherData = $this->getWeatherData($point->latitude, $point->longitude);
            $this->info("Received weather data for station:");
            $this->info("Temperature: " . $weatherData['main']['temp'] . "°C");
            $this->info("Humidity: " . $weatherData['main']['humidity'] . "%");
            
            // Kiểm tra dữ liệu trong vòng 1 giờ
            $existingRecord = DB::table('weather_measurements')
                ->where('station_code', $station->station_code)
                ->where('measurement_time', '>', Carbon::now()->subHour())
                ->exists();

            if ($existingRecord) {
                $this->info("Skipping insert - Record exists within the last hour for station {$station->name}");
                return;
            }

            // Chuẩn bị dữ liệu để insert
            $insertData = [
                'station_code' => $station->station_code,
                'measurement_time' => Carbon::now(),
                'temperature' => $weatherData['main']['temp'],
                'humidity' => $weatherData['main']['humidity'],
                'wind_speed' => $weatherData['wind']['speed'],
                'wind_direction' => $weatherData['wind']['deg'] ?? null,
                'air_pressure' => $weatherData['main']['pressure'],
                'rainfall' => $weatherData['rain']['1h'] ?? 0,
                'created_at' => Carbon::now()
            ];
            
            $this->info("Inserting station weather data:");
            $this->table(['Field', 'Value'], collect($insertData)->map(function($value, $key) {
                return [$key, is_null($value) ? 'NULL' : $value];
            })->toArray());

            // Insert vào weather_measurements
            DB::table('weather_measurements')->insert($insertData);

            // Cập nhật bảng air_quality_measurements
            $this->updateAirQualityMeasurement($station->station_code, $weatherData);
            
            $this->info("✓ Successfully inserted weather data for station: {$station->name}");
            
        } catch (\Exception $e) {
            $this->error("Error syncing weather data for station {$station->name}: " . $e->getMessage());
            \Log::error("Weather sync error for station {$station->station_code}", [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function updateAirQualityMeasurement($locationCode, $weatherData)
    {
        try {
            // Tìm bản ghi mới nhất trong air_quality_measurements
            $latestMeasurement = DB::table('air_quality_measurements')
                ->where('location_code', $locationCode)
                ->orderBy('measurement_time', 'desc')
                ->first();

            if ($latestMeasurement) {
                // Cập nhật thông tin thời tiết
                DB::table('air_quality_measurements')
                    ->where('id', $latestMeasurement->id)
                    ->update([
                        'temperature' => $weatherData['main']['temp'],
                        'humidity' => $weatherData['main']['humidity'],
                        'wind_speed' => $weatherData['wind']['speed']
                    ]);
                
                $this->info("Updated air quality measurement data for location: {$locationCode}");
            }
        } catch (\Exception $e) {
            $this->error("Error updating air quality measurement for location {$locationCode}: " . $e->getMessage());
            \Log::error("Air quality measurement update error", [
                'location_code' => $locationCode,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function updateAirQualityData()
    {
        $this->info("\nUpdating air quality data based on weather conditions...");

        try {
            // Lấy tất cả các bản ghi air_quality mới nhất
            $latestMeasurements = DB::table('air_quality_measurements')
                ->whereIn('id', function($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('air_quality_measurements')
                        ->groupBy('location_code');
                })
                ->get();

            foreach ($latestMeasurements as $measurement) {
                // Điều chỉnh AQI dựa trên điều kiện thời tiết
                $weatherFactor = $this->calculateWeatherFactor($measurement);
                $newAqi = round($measurement->aqi * $weatherFactor);
                
                // Cập nhật AQI mới
                DB::table('air_quality_measurements')
                    ->where('id', $measurement->id)
                    ->update(['aqi' => $newAqi]);

                $this->info("Updated AQI for location {$measurement->location_code}: {$measurement->aqi} -> {$newAqi}");
            }
        } catch (\Exception $e) {
            $this->error("Error updating air quality data: " . $e->getMessage());
            \Log::error("Air quality update error", [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function calculateWeatherFactor($measurement)
    {
        $factor = 1.0;

        // Điều chỉnh theo độ ẩm
        if ($measurement->humidity > 80) {
            $factor *= 1.2; // Không khí ẩm làm tăng ô nhiễm
        } elseif ($measurement->humidity < 40) {
            $factor *= 0.9; // Không khí khô làm giảm ô nhiễm
        }

        // Điều chỉnh theo gió
        if ($measurement->wind_speed > 3) {
            $factor *= 0.8; // Gió mạnh làm giảm ô nhiễm
        } elseif ($measurement->wind_speed < 0.5) {
            $factor *= 1.2; // Gió yếu làm tăng ô nhiễm
        }

        // Giới hạn factor trong khoảng 0.5 - 1.5
        return max(0.5, min(1.5, $factor));
    }

    protected function getWeatherData($lat, $lon)
    {
        $this->info("Calling OpenWeatherMap API...");
        
        $url = "https://api.openweathermap.org/data/2.5/weather";
        $params = [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'units' => 'metric'
        ];
        
        $this->info("API URL: {$url}");
        $this->info("Parameters (excluding API key): " . json_encode(array_diff_key($params, ['appid' => ''])));

        $response = Http::timeout(30)->get($url, $params);

        if ($response->successful()) {
            $this->info("API call successful");
            return $response->json();
        }

        $this->error("API call failed with status: " . $response->status());
        $this->error("Response body: " . $response->body());
        
        throw new \Exception('Failed to fetch weather data: ' . $response->body());
    }
}