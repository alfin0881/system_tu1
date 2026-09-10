<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

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
        // Pastikan nama bulan (mis. di tanggal_surat pada surat yang
        // digenerate) selalu berbahasa Indonesia ("Agustus", bukan
        // "August"), tidak bergantung locale server/APP_LOCALE.
        Carbon::setLocale('id');
    }
}
