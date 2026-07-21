<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('data:sync-all')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Sinkronkan berita (logistics/economy/trade) ke news_cache setiap 3 jam.
// Dipisah dari data:sync-all (yang tiap 30 menit) karena GNews API kuota
// hariannya terbatas (free tier, 100 request/hari) dan dipakai bareng fitur
// lain (mis. hitung risk score semua negara). 3 kategori x 8x/hari = 24
// panggilan/hari, aman dari kuota, sambil tetap bikin halaman News selalu
// ada berita segar tanpa bergantung ke kuota real-time saat user buka halaman.
Schedule::command('news:sync')
    ->everyThreeHours()
    ->withoutOverlapping()
    ->runInBackground();
