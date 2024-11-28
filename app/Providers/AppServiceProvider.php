<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache; // Add this import

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer('components.footer', function($view) {
            $view->with([
                'onlineUsers' => Cache::get('online_users', 0),
                'todayVisits' => Cache::get('today_visits', 0),
                'monthlyVisits' => Cache::get('monthly_visits', 0),
                'totalVisits' => Cache::get('total_visits', 0)
            ]);
        });
    }
}