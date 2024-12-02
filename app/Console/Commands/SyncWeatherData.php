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
            
            $existingRecord = DB::table('factory_weather_data')
                ->where('factory_code', $factory->factory_code)
                ->where('measurement_time', '>', Carbon::now()->subHour())
                ->exists();

            if ($existingRecord) {
                $this->info("Skipping insert - Record exists within the last hour for factory {$factory->name}");
                return;
            }

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

            DB::table('factory_weather_data')->insert($insertData);
            
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
            
            $existingRecord = DB::table('weather_measurements')
                ->where('station_code', $station->station_code)
                ->where('measurement_time', '>', Carbon::now()->subHour())
                ->exists();

            if ($existingRecord) {
                $this->info("Skipping insert - Record exists within the last hour for station {$station->name}");
                return;
            }

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

            DB::table('weather_measurements')->insert($insertData);
            
            $this->info("✓ Successfully inserted weather data for station: {$station->name}");
            
        } catch (\Exception $e) {
            $this->error("Error syncing weather data for station {$station->name}: " . $e->getMessage());
            \Log::error("Weather sync error for station {$station->station_code}", [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
        }
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