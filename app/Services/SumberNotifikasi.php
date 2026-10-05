<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use App\Models\Kegiatan;
use App\Models\Krs;
use App\Models\Materi;
use App\Models\Pembayaran;
use App\Models\Pengumuman;
use App\Models\PermohonanSurat;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use LogicException;

final class SumberNotifikasi
{
    public const DAFTAR = [
        'pengumuman' => [
            'tabel' => 'pengumuman',
            'model' => Pengumuman::class,
            'route' => 'pengumuman.show',
            'parameter' => 'pengumuman',
            'judul' => 'Pengumuman tersedia',
        ],

        'materi' => [
            'tabel' => 'materi',
            'model' => Materi::class,
            'route' => 'materi.show',
            'parameter' => 'materi',
            'judul' => 'Pembaruan materi perkuliahan',
        ],

        'kegiatan' => [
            'tabel' => 'kegiatan',
            'model' => Kegiatan::class,
            'route' => 'kegiatan.show',
            'parameter' => 'kegiatan',
            'judul' => 'Pembelajaran baru tersedia',
        ],

        'tagihan' => [
            'tabel' => 'tagihan',
            'model' => Tagihan::class,
            'route' => 'tagihan.show',
            'parameter' => 'tagihan',
            'judul' => 'Pembaruan tagihan akademik',
        ],

        'pembayaran' => [
            'tabel' => 'pembayaran',
            'model' => Pembayaran::class,
            'route' => 'pembayaran.show',
            'parameter' => 'pembayaran',
            'judul' => 'Pembaruan pengajuan pembayaran',
        ],

        'surat' => [
            'tabel' => 'permohonan_surat',
            'model' => PermohonanSurat::class,
            'route' => 'surat.show',
            'parameter' => 'permohonanSurat',
            'judul' => 'Pembaruan permohonan surat',
        ],

        'krs' => [
            'tabel' => 'krs',
            'model' => Krs::class,
            'route' => 'admin.krs.show',
            'parameter' => 'krs',
            'judul' => 'Pembaruan KRS untuk petugas akademik',
        ],

        'kalender' => [
            'tabel' => 'kalender_akademik',
            'model' => KalenderAkademik::class,
            'route' => 'kalender.show',
            'parameter' => 'kalenderAkademik',
            'judul' => 'Pembaruan kalender akademik',
        ],
    ];

    public function aktif(): array
    {
        $jenis = config(
            'notifikasi.sumber_aktif'
        );

        if (
            ! is_array($jenis)
            || count(
                array_filter($jenis, 'is_string')
            ) !== count($jenis)
            || array_diff(
                $jenis,
                array_keys(self::DAFTAR)
            ) !== []
        ) {
            throw new LogicException(
                'Konfigurasi sumber notifikasi tidak valid.'
            );
        }

        return array_values(
            array_unique($jenis)
        );
    }

    public function definisi(
        string $jenis
    ): array {
        if (
            ! in_array(
                $jenis,
                $this->aktif(),
                true
            )
        ) {
            throw new LogicException(
                'Sumber notifikasi tidak diaktifkan.'
            );
        }

        return self::DAFTAR[$jenis];
    }

    public function masuk(User $user): bool
    {
        return app(AksesBerkas::class)
            ->masuk($user);
    }

    public function query(
        string $jenis,
        User $user
    ): Builder {
        $definisi = $this->definisi($jenis);

        $query = $definisi['model']::query();

        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return match ($jenis) {
            'pengumuman' =>
                app(AksesPengumuman::class)
                    ->bacaan($query, $user),

            'materi' =>
                app(AksesMateri::class)
                    ->batasi($query, $user)
                    ->where(
                        'materi.status',
                        'terbit'
                    )
                    ->whereNotNull(
                        'materi.terbit_at'
                    )
                    ->where(
                        'materi.terbit_at',
                        '<=',
                        now('UTC')
                    ),

            'kegiatan' =>
                app(AksesKegiatan::class)
                    ->batasi($query, $user)
                    ->whereIn(
                        'kegiatan.status',
                        [
                            Kegiatan::TERBIT,
                            Kegiatan::DITUTUP,
                        ]
                    )
                    ->whereNotNull(
                        'kegiatan.terbit_at'
                    )
                    ->where(
                        'kegiatan.terbit_at',
                        '<=',
                        now('UTC')
                    ),

            'tagihan' =>
                app(AksesTagihan::class)
                    ->batasi($query, $user)
                    ->whereIn(
                        'tagihan.status',
                        [
                            'terbit',
                            'dibatalkan',
                        ]
                    )
                    ->whereNotNull(
                        'tagihan.diterbitkan_at'
                    ),

            'pembayaran' =>
                app(AksesPembayaran::class)
                    ->batasi($query, $user),

            'surat' =>
                app(AksesSurat::class)
                    ->batasi($query, $user),

            'krs' =>
                $query
                    ->when(
                        ! app(AksesMateri::class)
                            ->admin($user)
                        || ! Gate::forUser($user)
                            ->allows('kelola-krs'),
                        static fn (Builder $krs): Builder =>
                            $krs->whereRaw('1 = 0')
                    )
                    ->whereIn(
                        'krs.status',
                        [
                            'diajukan',
                            'disahkan',
                            'dibatalkan',
                        ]
                    ),

            'kalender' =>
                app(AksesKalender::class)
                    ->batasi($query, $user)
                    ->whereIn(
                        'kalender_akademik.status',
                        [
                            'terbit',
                            'batal',
                        ]
                    )
                    ->whereNotNull(
                        'kalender_akademik.diterbitkan_at'
                    )
                    ->where(
                        'kalender_akademik.diterbitkan_at',
                        '<=',
                        now('UTC')
                    ),
        };
    }

    public function tujuan(
        string $jenis,
        int $id
    ): string {
        $definisi = $this->definisi(
            $jenis
        );

        /*
         * Semua jenis pembelajaran pertama-tama membuka
         * halaman detail. Dari detail tersebut, tugas atau ujian
         * menyediakan tombol menuju ruang pengumpulan.
         */
        if ($jenis === 'kegiatan') {
            return route(
                'kegiatan.show',
                [
                    'kegiatan' => $id,
                ],
                false
            );
        }

        return route(
            $definisi['route'],
            [
                $definisi['parameter'] => $id,
            ],
            false
        );
    }
}