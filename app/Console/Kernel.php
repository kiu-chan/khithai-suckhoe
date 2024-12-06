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
        Commands\GenerateAirQualityData::class
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('data:generate-air-quality')->hourly();
        
        // Chạy cập nhật mỗi giờ
        $schedule->command('weather:sync')
            ->hourly()
            ->withoutOverlapping() // Tránh chạy chồng chéo
            ->appendOutputTo(storage_path('logs/weather-sync.log'));

        
        $schedule->command('weather:update-forecasts')
        ->everyThreeHours()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/weather-forecasts.log'));


        $schedule->command('plumes:generate')->hourly();
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