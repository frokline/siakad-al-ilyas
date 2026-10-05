<?php

namespace Tests\Feature;

use App\Actions\KelolaKegiatan;
use App\Actions\KelolaPengumpulan;
use App\Models\Pengumpulan;
use App\Models\User;
use App\Services\AksesPengumpulan;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class PengumpulanAksesTest extends PengumpulanDatabaseTestCase
{
    public function test_jawaban_pertama_langsung_terkirim(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan()
        );

        $this->assertSame(
            Pengumpulan::TERKIRIM,
            $pengumpulan->status
        );

        $this->assertSame(1, $pengumpulan->revisi);
        $this->assertNull($pengumpulan->diubah_at);
        $this->assertNull($pengumpulan->dibatalkan_at);
        $this->assertSame(1, Pengumpulan::count());
        $this->assertSame(1, $this->auditJumlah());
        $this->assertSame(
            'kirim_jawaban',
            DB::table('audit_log')
                ->where('entitas', 'pengumpulan')
                ->value('aksi')
        );
    }

    public function test_pesan_tanpa_berkas_dapat_dikirim(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan(),
            'Jawaban hanya berupa pesan.',
            []
        );

        $this->assertSame(
            'Jawaban hanya berupa pesan.',
            $pengumpulan->jawaban_teks
        );

        $this->assertSame(
            0,
            $pengumpulan->lampiran()->count()
        );
    }

    public function test_berkas_tanpa_pesan_dapat_dikirim(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan(),
            null,
            ['11']
        );

        $this->assertNull(
            $pengumpulan->jawaban_teks
        );

        $this->assertSame(
            1,
            $pengumpulan->lampiran()->count()
        );
    }

    public function test_jawaban_kosong_ditolak(): void
    {
        $this->expectException(
            ValidationException::class
        );

        $this->jawab(
            $this->kegiatan(),
            null,
            []
        );
    }

    public function test_edit_memperbarui_baris_yang_sama(): void
    {
        $kegiatan = $this->kegiatan();

        $awal = $this->jawab(
            $kegiatan,
            'Jawaban awal',
            ['11']
        );

        $id = $awal->id;

        $hasil = $this->jawab(
            $kegiatan,
            'Jawaban diperbaiki',
            ['13'],
            $awal
        );

        $this->assertSame($id, $hasil->id);
        $this->assertSame(1, Pengumpulan::count());
        $this->assertSame(2, $hasil->revisi);
        $this->assertNotNull($hasil->diubah_at);
        $this->assertSame(
            'Jawaban diperbaiki',
            $hasil->jawaban_teks
        );

        $this->assertSame(
            [13],
            $hasil->lampiran()
                ->pluck('berkas_id')
                ->all()
        );

        $this->assertSame(2, $this->auditJumlah());
    }

    public function test_versi_lama_tidak_boleh_menimpa_jawaban(): void
    {
        $kegiatan = $this->kegiatan();
        $pengumpulan = $this->jawab($kegiatan);
        $versiLama = $pengumpulan->versiForm();

        $this->jawab(
            $kegiatan,
            'Perubahan pertama',
            ['11'],
            $pengumpulan
        );

        $this->expectException(
            ValidationException::class
        );

        app(KelolaPengumpulan::class)->simpan(
            3,
            $kegiatan,
            [
                'versi_form' => $versiLama,
                'jawaban_teks' => 'Perubahan usang',
                'berkas_ids' => [11],
            ]
        );
    }

    public function test_tepat_pada_tenggat_sudah_ditolak(): void
    {
        $kegiatan = $this->kegiatan();

        $this->jam('2030-01-10 04:00:00');

        $this->expectException(
            ValidationException::class
        );

        $this->jawab($kegiatan);
    }

    public function test_sebelum_waktu_mulai_ditolak(): void
    {
        $kegiatan = $this->kegiatan([
            'buka_lokal' => '2030-01-10T11:00',
        ]);

        $this->expectException(
            ValidationException::class
        );

        $this->jawab($kegiatan);
    }

    public function test_kegiatan_ditutup_memblokir_jawaban(): void
    {
        $kegiatan = $this->kegiatan();

        app(KelolaKegiatan::class)->status(
            'tutup',
            2,
            $kegiatan,
            [
                'versi' => $kegiatan->versiForm(),
                'alasan' =>
                    'Menutup kegiatan untuk pengujian.',
            ]
        );

        $this->expectException(
            ValidationException::class
        );

        $this->jawab($kegiatan);
    }

    public function test_status_akademik_nonaktif_memblokir_jawaban(): void
    {
        $kegiatan = $this->kegiatan();

        $kondisi = [
            ['detail_krs', 1, 'dibatalkan'],
            ['krs', 1, 'dibatalkan'],
            ['registrasi_semester', 1, 'nonaktif'],
            ['riwayat_studi', 1, 'nonaktif'],
            ['kelas_kuliah', 100, 'selesai'],
            ['periode_akademik', 1, 'tutup'],
            ['pertemuan', 1, 'batal'],
        ];

        foreach ($kondisi as [$tabel, $id, $status]) {
            $statusAwal = DB::table($tabel)
                ->where('id', $id)
                ->value('status');

            DB::table($tabel)
                ->where('id', $id)
                ->update(['status' => $status]);

            try {
                $this->jawab($kegiatan);

                $this->fail(
                    'Jawaban seharusnya ditolak ketika '
                    . $tabel
                    . ' tidak aktif.'
                );
            } catch (
                ValidationException
                | \Symfony\Component\HttpKernel\Exception\HttpException
            ) {
                $this->assertSame(
                    0,
                    Pengumpulan::count()
                );
            } finally {
                DB::table($tabel)
                    ->where('id', $id)
                    ->update(['status' => $statusAwal]);
            }
        }
    }

    public function test_berkas_orang_lain_ditolak(): void
    {
        $this->expectException(
            ValidationException::class
        );

        $this->jawab(
            $this->kegiatan(),
            'Jawaban',
            ['12']
        );
    }

    public function test_batas_jumlah_format_dan_ukuran_diperiksa(): void
    {
        $kegiatan = $this->kegiatan([
            'maks_berkas' => 1,
        ]);

        try {
            $this->jawab(
                $kegiatan,
                'Jawaban',
                ['11', '13']
            );

            $this->fail(
                'Jumlah berkas berlebih harus ditolak.'
            );
        } catch (ValidationException) {
            $this->assertSame(0, Pengumpulan::count());
        }

        DB::table('berkas')
            ->where('id', 11)
            ->update([
                'ukuran_byte' => 20 * 1024 * 1024,
            ]);

        try {
            $this->jawab(
                $kegiatan,
                'Jawaban',
                ['11']
            );

            $this->fail(
                'Ukuran berkas berlebih harus ditolak.'
            );
        } catch (ValidationException) {
            $this->assertSame(0, Pengumpulan::count());
        }

        DB::table('berkas')
            ->where('id', 11)
            ->update([
                'ukuran_byte' => 12,
                'ekstensi' => 'png',
                'mime_type' => 'image/png',
            ]);

        $this->expectException(
            ValidationException::class
        );

        $this->jawab(
            $kegiatan,
            'Jawaban',
            ['11']
        );
    }

    public function test_mahasiswa_hanya_melihat_jawaban_sendiri(): void
    {
        $kegiatan = $this->kegiatan();

        $milikA = $this->jawab(
            $kegiatan,
            'Jawaban A',
            ['11'],
            null,
            3
        );

        $milikB = $this->jawab(
            $kegiatan,
            'Jawaban B',
            ['12'],
            null,
            6
        );

        $akses = app(AksesPengumpulan::class);

        $this->assertTrue(
            $akses->lihat(
                User::findOrFail(3),
                $milikA
            )
        );

        $this->assertFalse(
            $akses->lihat(
                User::findOrFail(3),
                $milikB
            )
        );

        $this->assertTrue(
            $akses->lihat(
                User::findOrFail(6),
                $milikB
            )
        );

        $this->assertFalse(
            $akses->lihat(
                User::findOrFail(6),
                $milikA
            )
        );
    }

    public function test_dosen_kelas_dapat_melihat_jawaban(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan()
        );

        $akses = app(AksesPengumpulan::class);

        $this->assertTrue(
            $akses->lihat(
                User::findOrFail(2),
                $pengumpulan
            )
        );

        DB::table('pengajar_kelas')
            ->where('dosen_id', 20)
            ->update(['aktif' => false]);

        $this->assertFalse(
            $akses->lihat(
                User::findOrFail(2),
                $pengumpulan
            )
        );
    }

    public function test_hapus_menggunakan_pembatalan_logis(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan()
        );

        $id = $pengumpulan->id;

        $hasil = $this->batalkan($pengumpulan);

        $this->assertSame($id, $hasil->id);
        $this->assertSame(
            Pengumpulan::DIBATALKAN,
            $hasil->status
        );

        $this->assertNotNull(
            $hasil->dibatalkan_at
        );

        $this->assertSame(2, $hasil->revisi);
        $this->assertSame(1, Pengumpulan::count());

        $this->assertSame(
            0,
            Pengumpulan::query()
                ->berlaku()
                ->count()
        );
    }

    public function test_jawaban_yang_dihapus_tidak_dapat_diedit(): void
    {
        $kegiatan = $this->kegiatan();
        $pengumpulan = $this->jawab($kegiatan);
        $pengumpulan = $this->batalkan($pengumpulan);

        $this->expectException(
            ValidationException::class
        );

        $this->jawab(
            $kegiatan,
            'Mencoba menghidupkan kembali',
            ['11'],
            $pengumpulan
        );
    }

    public function test_hapus_setelah_tenggat_ditolak(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan()
        );

        $this->jam('2030-01-10 04:00:00');

        $this->expectException(
            ValidationException::class
        );

        $this->batalkan($pengumpulan);
    }

    public function test_unique_database_mencegah_duplikasi_peserta(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan()
        );

        $data = $pengumpulan->getAttributes();
        unset($data['id']);

        $this->expectException(
            QueryException::class
        );

        DB::table('pengumpulan')->insert($data);
    }

    public function test_pengumpulan_tidak_dihapus_secara_fisik(): void
    {
        $pengumpulan = $this->jawab(
            $this->kegiatan()
        );

        $this->expectException(
            LogicException::class
        );

        $pengumpulan->delete();
    }
}