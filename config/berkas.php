<?php

return [
    'disk' => env('BERKAS_DISK', 'berkas_local'),
    'disk_diizinkan' => ['berkas_local', 'berkas_s3'],
    'maks_kib' => 20480,
    'kuota_byte' => (int) env('BERKAS_KUOTA_BYTE', 1073741824),
    'scanner' => env('BERKAS_SCANNER', 'none'),
    'clamscan' => env('BERKAS_CLAMSCAN', 'clamscan'),
    'scanner_timeout' => 60,
    'maks_piksel' => 20000000,
    'masa_tautan_menit' => 2,
    'batas_menunggu_menit' => 30,
];
