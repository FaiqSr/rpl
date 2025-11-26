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
        // Menggunakan cron expression untuk lebih fleksibel: setiap 5 menit (0,5,10,15,20,25,30,35,40,45,50,55)
        Schedule::command('telegram:send-monitoring')
            ->cron('*/5 * * * *') // Setiap 5 menit: 0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55
            ->withoutOverlapping(4) // Max 4 menit overlap protection (kurang dari 5 menit interval)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/telegram-scheduler.log')); // Log output untuk debugging
    }
}
