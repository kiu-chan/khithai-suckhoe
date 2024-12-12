<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FactoryPlumeService
{
    protected $pythonScript;
    protected $outputDir;
    protected $factories;

    public function __construct()
    {
        $this->pythonScript = base_path('python/air_quality/gaussian_plume.py');
        $this->outputDir = storage_path('app/public/plumes/factories');
        
        // Tạo thư mục output nếu chưa tồn tại
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }

        // Cấu hình các thông số kỹ thuật của các nhà máy
        $this->factories = [
            'luu_xa' => [
                'code' => 'KLV.05',
                'stack_height' => 150,      // Chiều cao ống khói (m)
                'stack_diameter' => 8,      // Đường kính ống khói (m)
                'stack_temp' => 180,        // Nhiệt độ khí thải (°C)
                'stack_velocity' => 25,     // Vận tốc khí thải (m/s)
            ],
            'quan_trieu' => [
                'code' => 'KLV.04',
                'stack_height' => 140,
                'stack_diameter' => 7,
                'stack_temp' => 170,
                'stack_velocity' => 22,
            ],
            'la_hien' => [
                'code' => 'KLV.02',
                'stack_height' => 135,
                'stack_diameter' => 6.5,
                'stack_temp' => 165,
                'stack_velocity' => 20,
            ],
            'quang_son' => [
                'code' => 'KLV.03',
                'stack_height' => 142,
                'stack_diameter' => 7.2,
                'stack_temp' => 172,
                'stack_velocity' => 23,
            ],
            'cao_ngan' => [
                'code' => 'KLV.01',
                'stack_height' => 145,
                'stack_diameter' => 7.5,
                'stack_temp' => 175,
                'stack_velocity' => 23,
            ],
        ];
    }

    /**
     * Tạo plume cho tất cả các nhà máy
     */
    public function generateAllPlumes()
    {
        $results = [];
        
        foreach ($this->factories as $slug => $config) {
            try {
                $this->info("Đang tạo plume cho nhà máy: {$slug}");
                $result = $this->generatePlume($slug);
                if ($result['success']) {
                    $results[$slug] = $result['file_path'];
                    $this->info("✓ Đã tạo xong plume cho {$slug}");
                } else {
                    $this->error("✗ Lỗi khi tạo plume cho {$slug}: " . $result['error']);
                }
            } catch (\Exception $e) {
                Log::error("Lỗi tạo plume cho nhà máy {$slug}: " . $e->getMessage());
                $this->error("✗ Lỗi không mong muốn khi tạo plume cho {$slug}");
            }
        }

        return $results;
    }

    /**
     * Tạo plume cho một nhà máy cụ thể
     */
    protected function generatePlume($factorySlug)
    {
        try {
            // Kiểm tra xem nhà máy có tồn tại trong cấu hình
            if (!isset($this->factories[$factorySlug])) {
                throw new \Exception("Không tìm thấy cấu hình cho nhà máy: {$factorySlug}");
            }

            $factory = $this->factories[$factorySlug];
            
            // Lấy tọa độ nhà máy
            $factoryData = DB::table('factories')
                ->select([
                    DB::raw("ST_X(ST_AsText(geom)) as longitude"),
                    DB::raw("ST_Y(ST_AsText(geom)) as latitude")
                ])
                ->where('code', $factory['code'])
                ->first();

            if (!$factoryData) {
                throw new \Exception("Không tìm thấy tọa độ nhà máy");
            }

            // Lấy dữ liệu thời tiết mới nhất
            $weatherData = DB::table('factory_weather_data')
                ->where('factory_code', $factory['code'])
                ->orderBy('measurement_time', 'desc')
                ->first();

            if (!$weatherData) {
                throw new \Exception("Không tìm thấy dữ liệu thời tiết");
            }

            // Lấy dữ liệu đo khí thải mới nhất
            $emissionData = DB::table('air_quality_measurements')
                ->where('location_code', $factory['code'])
                ->orderBy('measurement_time', 'desc')
                ->first();

            if (!$emissionData) {
                throw new \Exception("Không tìm thấy dữ liệu khí thải");
            }

            // Tính toán tốc độ phát thải
            $emissionRate = $this->calculateEmissionRate($emissionData);

            // Tạo tên file
            $outputFile = "{$factorySlug}_p.tif";
            $outputPath = $this->outputDir . '/' . $outputFile;

            // Xóa file cũ nếu tồn tại
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }

            // Tạo lệnh để chạy script Python
            $command = [
                'python',
                $this->pythonScript,
                '--width', 20000,
                '--height', 20000,
                '--resolution', 10,
                '--source-x', $factoryData->longitude,
                '--source-y', $factoryData->latitude,
                '--stack-height', $factory['stack_height'],
                '--stack-diameter', $factory['stack_diameter'],
                '--stack-temp', $factory['stack_temp'],
                '--stack-velocity', $factory['stack_velocity'],
                '--ambient-temp', $weatherData->temperature,
                '--emission-rate', $emissionRate,
                '--wind-speed', $weatherData->wind_speed,
                '--wind-direction', $weatherData->wind_direction ?? 0,
                '--output', $outputPath
            ];

            // Thực thi lệnh
            $process = new Process($command);
            $process->setTimeout(300); // 5 phút
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Kiểm tra file được tạo thành công
            if (!file_exists($outputPath)) {
                throw new \Exception("File output không được tạo");
            }

            return [
                'success' => true,
                'file_path' => $outputPath,
                'file_name' => $outputFile
            ];

        } catch (\Exception $e) {
            Log::error("Lỗi khi tạo plume cho {$factorySlug}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tính toán tốc độ phát thải dựa trên các phép đo
     */
    protected function calculateEmissionRate($measurements)
    {
        // Tốc độ phát thải cơ bản (g/s)
        $baseRate = 5000;
        
        // Điều chỉnh dựa trên nồng độ bụi
        $dustFactor = 1.0;
        if ($measurements->dust_level > 0.6) {
            $dustFactor = 1.3;
        } elseif ($measurements->dust_level > 0.5) {
            $dustFactor = 1.2;
        }
        
        // Điều chỉnh dựa trên nồng độ SO2
        $so2Factor = 1.0;
        if ($measurements->so2_level > 0.15) {
            $so2Factor = 1.3;
        } elseif ($measurements->so2_level > 0.12) {
            $so2Factor = 1.2;
        }
        
        // Điều chỉnh dựa trên CO
        $coFactor = 1.0;
        if ($measurements->co_level > 5.0) {
            $coFactor = 1.2;
        }
        
        return $baseRate * $dustFactor * $so2Factor * $coFactor;
    }

    /**
     * Log thông tin
     */
    protected function info($message)
    {
        Log::info($message);
    }

    /**
     * Log lỗi
     */
    protected function error($message)
    {
        Log::error($message);
    }
}