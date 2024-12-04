<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GaussianPlumeService
{
    protected $pythonScript;
    protected $outputDir;

    public function __construct()
    {
        $this->pythonScript = base_path('python/air_quality/gaussian_plume.py');
        $this->outputDir = storage_path('app/public/plumes');
        
        // Tạo thư mục output nếu chưa tồn tại
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    public function generatePlume($params = [])
    {
        try {
            // Merge với thông số mặc định
            $params = array_merge([
                'width' => 20000,
                'height' => 20000,
                'resolution' => 10,
                'source-x' => 106.7,
                'source-y' => 10.8,
                'stack-height' => 150,
                'stack-diameter' => 8,
                'stack-temp' => 180,
                'stack-velocity' => 25,
                'ambient-temp' => 30,
                'emission-rate' => 5000,
                'wind-speed' => 6,
                'wind-direction' => 225,
            ], $params);

            // Tạo tên file output độc nhất
            $outputFile = 'plume_' . time() . '.tif';
            $outputPath = $this->outputDir . '/' . $outputFile;

            // Xây dựng command
            $command = [
                'python',
                $this->pythonScript,
                '--width', $params['width'],
                '--height', $params['height'],
                '--resolution', $params['resolution'],
                '--source-x', $params['source-x'],
                '--source-y', $params['source-y'],
                '--stack-height', $params['stack-height'],
                '--stack-diameter', $params['stack-diameter'],
                '--stack-temp', $params['stack-temp'],
                '--stack-velocity', $params['stack-velocity'],
                '--ambient-temp', $params['ambient-temp'],
                '--emission-rate', $params['emission-rate'],
                '--wind-speed', $params['wind-speed'],
                '--wind-direction', $params['wind-direction'],
                '--output', $outputPath
            ];

            // Thực thi command
            $process = new Process($command);
            $process->setTimeout(300); // 5 phút
            $process->run();

            // Kiểm tra kết quả
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Kiểm tra file output có tồn tại
            if (!file_exists($outputPath)) {
                throw new \Exception("File output không được tạo");
            }

            return [
                'success' => true,
                'file_path' => $outputPath,
                'file_name' => $outputFile,
                'url' => asset('storage/plumes/' . $outputFile)
            ];

        } catch (\Exception $e) {
            Log::error('Lỗi tạo plume: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}