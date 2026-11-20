<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rekonsiliasi transaksi DOKU pending setiap 10 menit
Schedule::command('doku:sync-pending')->everyTenMinutes();
