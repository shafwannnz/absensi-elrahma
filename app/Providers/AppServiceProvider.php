<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Nama hari & bulan Bahasa Indonesia pada translatedFormat() (mis. "Sabtu, 03 Oktober 2026").
        Carbon::setLocale(config('app.locale'));
    }
}
