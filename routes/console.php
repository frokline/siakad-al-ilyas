<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
// Tambahkan sekali ke routes/console.php setelah migration dan tes lulus.
\Illuminate\Support\Facades\Schedule::command('notifikasi:sinkronkan')
    ->everyFiveMinutes()->withoutOverlapping(60);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
