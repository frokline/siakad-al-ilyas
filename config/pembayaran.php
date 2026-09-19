<?php

return [
    // Isi di .env menggunakan rekening resmi yang telah dikonfirmasi pihak institusi.
    // Format bebas satu baris, maksimal 150 karakter: bank, nomor rekening, atas nama.
    'tujuan_transfer' => env('SPP_TUJUAN_TRANSFER', ''),
];
