<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- 1. WAJIB TAMBAHKAN BARIS INI

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
        // Deteksi Ngrok, Proxy (Hostinger/Cloudflare), ATAU jika sedang online (Production)
        if (config('app.env') === 'production' || str_contains(request()->getHost(), 'ngrok') || request()->header('x-forwarded-proto') == 'https') {
            URL::forceScheme('https');
        }
    }
}