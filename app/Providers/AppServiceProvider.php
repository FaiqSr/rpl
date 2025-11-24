<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schedule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Schedule untuk generate data sensor setiap jam
        Schedule::command('sensor:generate')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
        
        // Schedule untuk mengirim notifikasi Telegram setiap 5 menit
        Schedule::command('telegram:send-monitoring')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();
    }
}
