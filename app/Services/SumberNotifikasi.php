<?php

namespace App\Services;

use App\Models\{User, Pengumuman, Materi, Kegiatan, Tagihan, Pembayaran, PermohonanSurat, Krs, KalenderAkademik};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use LogicException;

final class SumberNotifikasi
{
    public const DAFTAR = [
        'pengumuman' => ['tabel' => 'pengumuman', 'model' => Pengumuman::class, 'route' => 'pengumuman.show', 'parameter' => 'pengumuman', 'judul' => 'Pengumuman tersedia'],
        'materi' => ['tabel' => 'materi', 'model' => Materi::class, 'route' => 'materi.show', 'parameter' => 'materi', 'judul' => 'Pembaruan materi perkuliahan'],
        'kegiatan' => ['tabel' => 'kegiatan', 'model' => Kegiatan::class, 'route' => 'kegiatan.show', 'parameter' => 'kegiatan', 'judul' => 'Pembaruan kegiatan perkuliahan'],
        'tagihan' => ['tabel' => 'tagihan', 'model' => Tagihan::class, 'route' => 'tagihan.show', 'parameter' => 'tagihan', 'judul' => 'Pembaruan tagihan akademik'],
        'pembayaran' => ['tabel' => 'pembayaran', 'model' => Pembayaran::class, 'route' => 'pembayaran.show', 'parameter' => 'pembayaran', 'judul' => 'Pembaruan pengajuan pembayaran'],
        'surat' => ['tabel' => 'permohonan_surat', 'model' => PermohonanSurat::class, 'route' => 'surat.show', 'parameter' => 'permohonanSurat', 'judul' => 'Pembaruan permohonan surat'],
        'krs' => ['tabel' => 'krs', 'model' => Krs::class, 'route' => 'admin.krs.show', 'parameter' => 'krs', 'judul' => 'Pembaruan KRS untuk petugas akademik'],
        'kalender' => ['tabel' => 'kalender_akademik', 'model' => KalenderAkademik::class, 'route' => 'kalender.show', 'parameter' => 'kalenderAkademik', 'judul' => 'Pembaruan kalender akademik'],
    ];
    public function aktif(): array
    {
        $jenis = config('notifikasi.sumber_aktif');
        if (
            ! is_array($jenis) || count(array_filter($jenis, 'is_string')) !== count($jenis)
            || array_diff($jenis, array_keys(self::DAFTAR))
        ) {
            throw new LogicException('Konfigurasi sumber notifikasi tidak valid.');
        }
        return array_values(array_unique($jenis));
    }
    public function definisi(string $jenis): array
    {
        if (! in_array($jenis, $this->aktif(), true)) {
            throw new LogicException('Sumber notifikasi tidak diaktifkan.');
        }
        return self::DAFTAR[$jenis];
    }
    public function masuk(User $u): bool
    {
        return app(AksesBerkas::class)->masuk($u);
    }
    public function query(string $jenis, User $u): Builder
    {
        $def = $this->definisi($jenis);
        $q = $def['model']::query();
        if (! $this->masuk($u)) {
            return $q->whereRaw('1 = 0');
        }
        // Aturan baca sumber dipakai ulang. Admin tidak menerima notifikasi draf publik.
        return match ($jenis) {
            'pengumuman' => app(AksesPengumuman::class)->bacaan($q, $u),
            'materi' => app(AksesMateri::class)->batasi($q, $u)->where('materi.status', 'terbit')
                ->whereNotNull('materi.terbit_at')->where('materi.terbit_at', '<=', now('UTC')),
            'kegiatan' => app(AksesKegiatan::class)->batasi($q, $u)->whereIn('kegiatan.status', ['terbit', 'ditutup'])
                ->whereNotNull('kegiatan.terbit_at')->where('kegiatan.terbit_at', '<=', now('UTC')),
            'tagihan' => app(AksesTagihan::class)->batasi($q, $u)->whereIn('tagihan.status', ['terbit', 'dibatalkan'])
                ->whereNotNull('tagihan.diterbitkan_at'),
            'pembayaran' => app(AksesPembayaran::class)->batasi($q, $u),
            'surat' => app(AksesSurat::class)->batasi($q, $u),
            // Route KRS lama adalah portal admin, bukan portal mahasiswa.
            'krs' => $q->when(! app(AksesMateri::class)->admin($u) || ! Gate::forUser($u)->allows('kelola-krs'), fn($b) => $b->whereRaw('1 = 0'))
                ->whereIn('krs.status', ['diajukan', 'disahkan', 'dibatalkan']),
            'kalender' => app(AksesKalender::class)->batasi($q, $u)->whereIn('kalender_akademik.status', ['terbit', 'batal'])
                ->whereNotNull('kalender_akademik.diterbitkan_at')->where('kalender_akademik.diterbitkan_at', '<=', now('UTC')),
        };
    }
    public function tujuan(string $jenis, int $id): string
    {
        $d = $this->definisi($jenis);
        // Route relatif dari daftar kode tetap. Tidak pernah menerima URL/route/class client.
        return route($d['route'], [$d['parameter'] => $id], false);
    }
}
