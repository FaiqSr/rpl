<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\URL;

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

        if(config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        // Schedule untuk generate data sensor setiap jam
        Schedule::command('sensor:generate')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Schedule untuk mengirim notifikasi Telegram
        // TESTING MODE: Setiap 10 detik untuk testing (normal: everyMinute)
        // - Kondisi BAIK: 30 detik sekali (TESTING MODE - normal: 1 jam)
        // - Kondisi PERHATIAN/BURUK: 10 detik sekali (TESTING MODE - normal: 5 menit)
        // NOTE: Untuk testing, gunakan command manual dengan loop atau ubah ke everyMinute() untuk production
        Schedule::command('telegram:send-monitoring')
            ->everyMinute() // Scheduler check setiap menit, logic di command handle interval 10 detik
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/telegram-scheduler.log')); // Log output untuk debugging
    }
}
