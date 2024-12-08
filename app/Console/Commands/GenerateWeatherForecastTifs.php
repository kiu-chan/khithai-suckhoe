<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateWeatherForecastTifs extends Command
{
    protected $signature = 'weather:generate-tifs';
    protected $description = 'Generate TIF files for weather forecasts';

    private $factoryParams = [
        'KLV.01' => [  // Lưu Xá
            'name' => 'luu_xa',
            'stack_height' => 120,
            'stack_diameter' => 3.0,
            'stack_velocity' => 18,
            'emission_rate' => 55
        ],
        'KLV.02' => [  // Quán Triều
            'name' => 'quan_trieu',
            'stack_height' => 110,
            'stack_diameter' => 2.8,
            'stack_velocity' => 17,
            'emission_rate' => 52
        ],
        'KLV.03' => [  // Cao Ngạn
            'name' => 'cao_ngan',
            'stack_height' => 130,
            'stack_diameter' => 3.2,
            'stack_velocity' => 19,
            'emission_rate' => 58
        ],
        'KLV.04' => [  // Quang Sơn
            'name' => 'quang_son',
            'stack_height' => 115,
            'stack_diameter' => 2.9,
            'stack_velocity' => 16,
            'emission_rate' => 53
        ],
        'KLV.05' => [  // La Hiên
            'name' => 'la_hien',
            'stack_height' => 125,
            'stack_diameter' => 3.1,
            'stack_velocity' => 18,
            'emission_rate' => 54
        ]
    ];

    private $modelParams = [
        'width' => 20000,      // Chiều rộng vùng tính (m)
        'height' => 20000,     // Chiều cao vùng tính (m)
        'resolution' => 10,   // Độ phân giải (m)
    ];

    public function handle()
    {
        $this->info('Starting weather forecast TIF generation at: ' . now());

        // Lấy tất cả các dự báo cần xử lý
        $forecasts = DB::table('weather_forecasts')
            ->select([
                'weather_forecasts.*',
                'factories.name as factory_name',
                'factories.code as factory_code',
                DB::raw("ST_X(factories.geom) as longitude"),
                DB::raw("ST_Y(factories.geom) as latitude")
            ])
            ->join('factories', 'weather_forecasts.factory_id', '=', 'factories.code')
            ->where('forecast_time', '>=', now())
            ->where('forecast_time', '<=', now()->addDays(5))
            ->whereRaw('EXTRACT(HOUR FROM forecast_time) IN (0,3,6,9,12,15,18,21)')
            ->orderBy('factory_id')
            ->orderBy('forecast_time')
            ->get();

        $this->info("Found {$forecasts->count()} forecasts to process");

        // Tạo thư mục output nếu chưa tồn tại
        $outputDir = storage_path('app/public/plumes/weather_forecasts');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        foreach ($forecasts as $forecast) {
            $this->generateTifForForecast($forecast);
        }

        $this->info('Weather forecast TIF generation completed at: ' . now());
    }

    private function generateTifForForecast($forecast)
    {
        // Kiểm tra xem có thông số cho nhà máy này không
        if (!isset($this->factoryParams[$forecast->factory_code])) {
            $this->warn("No parameters found for factory {$forecast->factory_code}, skipping...");
            return;
        }

        // Lấy thông số nhà máy
        $factoryParam = $this->factoryParams[$forecast->factory_code];
        
        // Tạo tên file với tên nhà máy cố định
        $dayNumber = $this->getDayNumber($forecast->forecast_time);
        $hour = sprintf("%02d", Carbon::parse($forecast->forecast_time)->hour);
        
        // Tạo đường dẫn file
        $outputDir = storage_path('app/public/plumes/weather_forecasts');
        $outputFile = "{$outputDir}/{$factoryParam['name']}_{$dayNumber}_{$hour}.tif";

        // Tạo lệnh chạy gaussian_plume.py
        $cmd = [
            'python',
            base_path('python/air_quality/gaussian_plume.py'),
            '--width', $this->modelParams['width'],
            '--height', $this->modelParams['height'],
            '--resolution', $this->modelParams['resolution'],
            '--source-x', $forecast->longitude,
            '--source-y', $forecast->latitude,
            '--stack-height', $factoryParam['stack_height'],
            '--stack-diameter', $factoryParam['stack_diameter'],
            '--stack-temp', $forecast->temperature,
            '--stack-velocity', $factoryParam['stack_velocity'],
            '--ambient-temp', $forecast->feels_like,
            '--emission-rate', $factoryParam['emission_rate'],
            '--wind-speed', $forecast->wind_speed,
            '--wind-direction', $forecast->wind_deg,
            '--output', $outputFile
        ];

        // Chuyển array thành string command và thực thi
        $cmd = array_map('escapeshellarg', $cmd);
        $cmdString = implode(' ', $cmd);

        try {
            $this->info("\nProcessing forecast:");
            $this->info("Factory: {$factoryParam['name']}");
            $this->info("Time: {$forecast->forecast_time}");
            $this->info("Output: {$outputFile}");
            
            // Thực thi lệnh
            $output = [];
            $returnVar = 0;
            exec($cmdString . " 2>&1", $output, $returnVar);

            if ($returnVar === 0) {
                $this->info("Successfully generated TIF file");
            } else {
                $this->error("Error generating TIF: " . implode("\n", $output));
                \Log::error("Error generating weather forecast TIF", [
                    'factory' => $factoryParam['name'],
                    'time' => $forecast->forecast_time,
                    'error' => implode("\n", $output)
                ]);
            }
        } catch (\Exception $e) {
            $this->error("Exception occurred: " . $e->getMessage());
            \Log::error("Exception in weather forecast TIF generation", [
                'factory' => $factoryParam['name'],
                'time' => $forecast->forecast_time,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function getDayNumber($forecastTime)
    {
        $forecastDate = Carbon::parse($forecastTime)->startOfDay();
        $today = now()->startOfDay();
        
        // Nếu là ngày trong quá khứ hoặc hôm nay, trả về 0
        if ($forecastDate->lte($today)) {
            return 0;
        }
        
        // Các ngày tiếp theo
        for ($i = 1; $i <= 5; $i++) {
            if ($forecastDate->eq($today->copy()->addDays($i))) {
                return $i;
            }
        }
        
        return 5; // Mặc định cho các ngày xa hơn
    }
}