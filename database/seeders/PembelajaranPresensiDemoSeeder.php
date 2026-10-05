<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PembelajaranPresensiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $dosen = DB::table('dosen')
            ->where('kode_dosen', 'DSN001')
            ->first();

        $mahasiswa = DB::table('mahasiswa')
            ->where('nim', '20260001')
            ->first();

        if (! $dosen || ! $mahasiswa) {
            throw new \RuntimeException(
                'Data dosen DSN001 atau mahasiswa 20260001 belum tersedia.'
            );
        }

        $kelas = DB::table('kelas_kuliah')
            ->whereIn('kode', [
                'PAI-1A-PAI101',
                'PAI-1A-PAI102',
                'PAI-1A-PAI103',
            ])
            ->orderBy('id')
            ->get();

        foreach ($kelas as $index => $item) {
            $pengajar = DB::table('pengajar_kelas')
                ->where('kelas_kuliah_id', $item->id)
                ->where('dosen_id', $dosen->id)
                ->where('aktif', true)
                ->first();

            if (! $pengajar) {
                continue;
            }

            $jadwal = DB::table('jadwal_kuliah')
                ->where('kelas_kuliah_id', $item->id)
                ->where('aktif', true)
                ->first();

            $nomor = $index + 1;

            $pertemuanData = [
                'kelas_kuliah_id' => $item->id,
                'jadwal_kuliah_id' => $jadwal?->id,
                'pengajar_kelas_id' => $pengajar->id,
                'nomor' => $nomor,
                'jenis' => 'kuliah',
                'topik' => $item->nama_mk_snapshot,
                'rencana' => 'Pembelajaran pertemuan ke-' . $nomor,
                'realisasi' => 'Pembelajaran pertemuan ke-' . $nomor,
                'mulai_rencana' => '2026-09-' . str_pad((string) (15 + $index), 2, '0', STR_PAD_LEFT) . ' 08:00:00',
                'selesai_rencana' => '2026-09-' . str_pad((string) (15 + $index), 2, '0', STR_PAD_LEFT) . ' 09:40:00',
                'mulai_aktual' => '2026-09-' . str_pad((string) (15 + $index), 2, '0', STR_PAD_LEFT) . ' 08:00:00',
                'selesai_aktual' => '2026-09-' . str_pad((string) (15 + $index), 2, '0', '0') . ' 09:40:00',
                'metode' => 'luring',
                'lokasi' => 'Ruang PAI 10' . ($index + 1),
                'tautan_pertemuan' => null,
                'jadwal_snapshot' => json_encode($jadwal),
                'pengajar_snapshot' => json_encode($pengajar),
                'status' => 'selesai',
                'revisi' => 1,
                'dibatalkan_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $pertemuanData = $this->hanyaKolom('pertemuan', $pertemuanData);

            $pertemuanId = DB::table('pertemuan')
                ->where('kelas_kuliah_id', $item->id)
                ->where('nomor', $nomor)
                ->value('id');

            if ($pertemuanId) {
                DB::table('pertemuan')
                    ->where('id', $pertemuanId)
                    ->update($pertemuanData);
            } else {
                $pertemuanId = DB::table('pertemuan')
                    ->insertGetId($pertemuanData);
            }

            $kegiatanData = [
                'kelas_kuliah_id' => $item->id,
                'pertemuan_id' => $pertemuanId,
                'pembuat_id' => $dosen->user_id,
                'form_token' => Str::random(32),
                'hash_permohonan' => hash('sha256', 'demo-kegiatan-' . $item->id),
                'jenis' => $index === 0 ? 'materi' : 'tugas',
                'metode' => $index === 0 ? 'informasi' : 'pengumpulan_berkas',
                'judul' => $index === 0
                    ? 'Materi ' . $item->nama_mk_snapshot
                    : 'Tugas ' . $item->nama_mk_snapshot,
                'instruksi' => 'Silakan pelajari materi dan kerjakan tugas sesuai petunjuk dosen.',
                'tautan_eksternal' => null,
                'buka_at' => $index === 0 ? null : '2026-09-20 00:00:00',
                'tenggat_at' => $index === 0 ? null : '2026-10-20 23:59:59',
                'maks_ukuran_byte' => 20 * 1024 * 1024,
                'maks_berkas' => 10,
                'ekstensi_diizinkan' => $index === 0
                    ? null
                    : json_encode(['pdf', 'jpg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip']),
                'status' => 'terbit',
                'terbit_at' => $now,
                'ditutup_at' => null,
                'diarsipkan_at' => null,
                'revisi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $kegiatanData = $this->hanyaKolom('kegiatan', $kegiatanData);

            DB::table('kegiatan')
                ->updateOrInsert(
                    [
                        'kelas_kuliah_id' => $item->id,
                        'pertemuan_id' => $pertemuanId,
                        'judul' => $kegiatanData['judul'],
                    ],
                    $kegiatanData
                );

            $presensiPertemuan = [
                'pertemuan_id' => $pertemuanId,
                'kelas_kuliah_id' => $item->id,
                'dibuat_oleh' => $dosen->user_id,
                'status' => 'terbuka',
                'jumlah_peserta' => 1,
                'dibuka_oleh' => $dosen->user_id,
                'dibuka_at' => $now,
                'ditutup_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $presensiPertemuan = $this->hanyaKolom(
                'presensi_pertemuan',
                $presensiPertemuan
            );

            if (Schema::hasTable('presensi_pertemuan')) {
                DB::table('presensi_pertemuan')
                    ->updateOrInsert(
                        ['pertemuan_id' => $pertemuanId],
                        $presensiPertemuan
                    );

                $presensiId = DB::table('presensi_pertemuan')
                    ->where('pertemuan_id', $pertemuanId)
                    ->value('id');

                if (Schema::hasTable('presensi') && $presensiId) {
                    $detailKrs = DB::table('detail_krs')
                        ->where('kelas_kuliah_id', $item->id)
                        ->where('status', 'aktif')
                        ->first();

                    if ($detailKrs) {
                        $presensi = $this->hanyaKolom('presensi', [
                            'presensi_pertemuan_id' => $presensiId,
                            'kelas_kuliah_id' => $item->id,
                            'detail_krs_id' => $detailKrs->id,
                            'mahasiswa_id' => $mahasiswa->id,
                            'peserta_snapshot' => json_encode($mahasiswa),
                            'status' => 'hadir',
                            'catatan' => null,
                            'dicatat_oleh' => $dosen->user_id,
                            'dicatat_at' => $now,
                            'revisi' => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        DB::table('presensi')->updateOrInsert(
                            [
                                'presensi_pertemuan_id' => $presensiId,
                                'mahasiswa_id' => $mahasiswa->id,
                            ],
                            $presensi
                        );
                    }
                }
            }
        }

        $this->command?->info(
            'Dummy Kelas Saya, Pembelajaran, dan Presensi berhasil dibuat.'
        );
    }

    private function hanyaKolom(string $table, array $data): array
    {
        $kolom = Schema::getColumnListing($table);

        return array_filter(
            $data,
            static fn ($nilai, $nama): bool =>
                in_array($nama, $kolom, true),
            ARRAY_FILTER_USE_BOTH
        );
    }
}