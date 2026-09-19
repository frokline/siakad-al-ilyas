<?php

return [
    // Aktifkan hanya modul yang kode, migration, dan route-nya sudah terpasang.
    // Perubahan daftar ini memerlukan php artisan config:clear.
    'sumber_aktif' => ['pengumuman', 'materi', 'kegiatan', 'tagihan', 'pembayaran', 'surat', 'krs', 'kalender'],
    'hari_sinkronisasi' => 30,
];
