<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateWeatherForecasts extends Command
{
    protected $signature = 'weather:update-forecasts';
    protected $description = 'Update weather forecasts for all factories';

    protected $apiKey = 'b5da2b01187e7f3c9d2d8aeffe7228d8';

    public function handle()
    {
        $this->info('Starting weather forecast update at: ' . Carbon::now());

        // Lấy danh sách nhà máy và tọa độ
        $factories = DB::table('factories')
            ->select(['code', 'name', DB::raw("ST_X(geom) as longitude"), DB::raw("ST_Y(geom) as latitude")])
            ->get();

        foreach ($factories as $factory) {
            $this->updateForecastForFactory($factory);
        }

        $this->info('Weather forecast update completed at: ' . Carbon::now());
    }

    protected function updateForecastForFactory($factory)
    {
        try {
            $this->info("\nFetching forecasts for factory: {$factory->name}");

            // Gọi API OpenWeatherMap
            $response = Http::get('https://api.openweathermap.org/data/2.5/forecast', [
                'lat' => $factory->latitude,
                'lon' => $factory->longitude,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang' => 'vi'
            ]);

            if (!$response->successful()) {
                $this->error("API call failed for factory {$factory->name}: " . $response->body());
                return;
            }

            $data = $response->json();
            
            foreach ($data['list'] as $forecast) {
                $forecastTime = Carbon::createFromTimestamp($forecast['dt']);
                
                // Chuẩn bị dữ liệu cập nhật
                $updateData = [
                    'temperature' => $forecast['main']['temp'],
                    'feels_like' => $forecast['main']['feels_like'],
                    'temp_min' => $forecast['main']['temp_min'],
                    'temp_max' => $forecast['main']['temp_max'],
                    'pressure' => $forecast['main']['pressure'],
                    'humidity' => $forecast['main']['humidity'],
                    'weather_main' => $forecast['weather'][0]['main'],
                    'weather_description' => $forecast['weather'][0]['description'],
                    'clouds_percentage' => $forecast['clouds']['all'],
                    'wind_speed' => $forecast['wind']['speed'],
                    'wind_deg' => $forecast['wind']['deg'],
                    'wind_gust' => $forecast['wind']['gust'] ?? null,
                    'visibility' => $forecast['visibility'],
                    'pop' => $forecast['pop'],
                    'rain_3h' => isset($forecast['rain']['3h']) ? $forecast['rain']['3h'] : null,
                ];

                // Kiểm tra xem dự báo đã tồn tại chưa
                $exists = DB::table('weather_forecasts')
                    ->where('factory_id', $factory->code)
                    ->where('forecast_time', $forecastTime)
                    ->exists();

                if ($exists) {
                    // Cập nhật nếu đã tồn tại
                    DB::table('weather_forecasts')
                        ->where('factory_id', $factory->code)
                        ->where('forecast_time', $forecastTime)
                        ->update(array_merge($updateData, [
                            'updated_at' => Carbon::now()
                        ]));
                } else {
                    // Thêm mới nếu chưa tồn tại
                    DB::table('weather_forecasts')->insert(array_merge($updateData, [
                        'factory_id' => $factory->code,
                        'forecast_time' => $forecastTime,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]));
                }
            }

            // Xóa các dự báo cũ (ngoài khoảng thời gian 5 ngày)
            DB::table('weather_forecasts')
                ->where('factory_id', $factory->code)
                ->where('forecast_time', '<', Carbon::now())
                ->delete();

            $this->info("Successfully updated forecasts for factory: {$factory->name}");

        } catch (\Exception $e) {
            $this->error("Error updating forecasts for factory {$factory->name}: " . $e->getMessage());
            \Log::error("Weather forecast update error", [
                'factory' => $factory->code,
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
        }
    }
}