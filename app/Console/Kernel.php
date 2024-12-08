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
        Commands\UpdateWeatherForecasts::class,
        Commands\GenerateWeatherForecastTifs::class,
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
            ->appendOutputTo(storage_path('logs/weather-sync.log'));

        // Cập nhật dự báo thời tiết mỗi 3 giờ
        $schedule->command('weather:update-forecasts')
            ->everyThreeHours()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weather-forecasts.log'));

        // Tạo plume mỗi giờ
        $schedule->command('plumes:generate')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/plumes.log'));

        // Xóa log files cũ sau 7 ngày
        $schedule->command('log:clear --days=7')
            ->daily();

        
        $schedule->command('weather:generate-tifs')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weather-forecast-tifs.log'));
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