<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GenerateAirQualityData::class,
        Commands\GenerateFactoryPlumes::class,
        Commands\SyncWeatherData::class,
        Commands\UpdateWeatherForecasts::class
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cập nhật dữ liệu chất lượng không khí mỗi giờ
        $schedule->command('data:generate-air-quality')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/air-quality.log'));
        
        // Đồng bộ dữ liệu thời tiết mỗi giờ
        $schedule->command('weather:sync')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weather-sync.log'))
            ->before(function () {
                \Log::info('Starting weather sync task');
            })
            ->after(function () {
                \Log::info('Weather sync task completed');
            });

        // Cập nhật dự báo thời tiết mỗi 3 giờ
        $schedule->command('weather:update-forecasts')
            ->everyThreeHours()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weather-forecasts.log'))
            ->before(function () {
                \Log::info('Starting weather forecast update');
            })
            ->after(function () {
                \Log::info('Weather forecast update completed');
            });

        // Tạo plume mỗi giờ
        $schedule->command('plumes:generate')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/plumes.log'))
            ->before(function () {
                \Log::info('Starting plume generation');
            })
            ->after(function () {
                \Log::info('Plume generation completed');
            });

        // Xóa log files cũ sau 7 ngày
        $schedule->command('log:clear --days=7')
            ->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}