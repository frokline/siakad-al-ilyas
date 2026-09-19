<?php

namespace Tests\Feature;

use App\Actions\KelolaKegiatan;
use App\Actions\KelolaPengumpulan;
use App\Http\Controllers\PengumpulanController;
use App\Models\Pengumpulan;
use App\Models\User;
use App\Services\AksesPengumpulan;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

class PengumpulanAksesTest extends PengumpulanDatabaseTestCase
{
    public function test_pembuatan_berulang_hanya_satu_draf_dan_satu_audit(): void
    {
        $k = $this->kegiatan();
        $token = (string) Str::uuid();
        $p = $this->draf($k, 0, $token);
        $this->assertSame($p->id, $this->draf($k, 0, $token)->id);
        $this->assertSame($p->id, $this->draf($k)->id);
        $this->assertSame(1, Pengumpulan::count());
        $this->assertSame(1, $this->auditJumlah());
    }
    public function test_token_pembuatan_lama_tidak_membuat_versi_baru_setelah_dikirim(): void
    {
        $k = $this->kegiatan();
        $token = (string) Str::uuid();
        $p = $this->kirim($this->isi($this->draf($k, 0, $token)));
        $this->assertSame($p->id, $this->draf($k, 0, $token)->id);
        $this->assertSame(1, Pengumpulan::count());
    }
    public function test_dasar_versi_lama_tidak_menciptakan_draf_tambahan(): void
    {
        $k = $this->kegiatan();
        $this->kirim($this->isi($this->draf($k)));
        $this->expectException(ValidationException::class);
        $this->draf($k, 0);
    }
    public function test_draf_baru_tidak_menggantikan_kiriman_lama(): void
    {
        $k = $this->kegiatan();
        $p = $this->kirim($this->isi($this->draf($k)));
        $baru = $this->draf($k, 1);
        $this->assertSame(2, $baru->versi);
        $this->assertNull($baru->jawaban_teks);
        $this->assertSame([$p->id], Pengumpulan::berlaku()->pluck('id')->all());
    }
    public function test_versi_final_terbaru_berlaku_dan_snapshot_lama_tetap(): void
    {
        $k = $this->kegiatan();
        $p1 = $this->kirim($this->isi($this->draf($k), 'Jawaban lama'));
        $p2 = $this->kirim($this->isi($this->draf($k, 1), 'Jawaban diperbaiki', ['11']));
        $this->assertSame([$p2->id], Pengumpulan::berlaku()->pluck('id')->all());
        $this->assertSame('Jawaban lama', $p1->fresh()->jawaban_teks);
        $this->assertSame($p1->hash_jawaban, $p1->fresh()->hitungHash());
        $this->assertSame(1, $p1->lampiran()->count());
        $this->assertSame(1, $p2->lampiran()->count());
    }
    public function test_pengulangan_kirim_setelah_tenggat_mengembalikan_bukti_yang_sama(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()));
        $data = ['versi_form' => $p->versiForm(), 'kunci_kirim' => $p->kunci_kirim];
        $a = app(KelolaPengumpulan::class);
        $final = $a->kirim(3, $p, $data);
        $jumlah = $this->auditJumlah();
        $this->jam('2030-01-10 05:00:00');
        $ulang = $a->kirim(3, $p, $data);
        $this->assertSame($final->id, $ulang->id);
        $this->assertSame($final->hash_jawaban, $ulang->hash_jawaban);
        $this->assertTrue($final->dikirim_at->equalTo($ulang->dikirim_at));
        $this->assertSame($jumlah, $this->auditJumlah());
    }
    public function test_jawaban_kosong_tidak_dapat_dikirim(): void
    {
        $p = $this->draf($this->kegiatan());
        try {
            $this->kirim($p);
            $this->fail('Jawaban kosong harus ditolak.');
        } catch (ValidationException) {
            $this->assertSame(Pengumpulan::DRAF, $p->fresh()->status);
            $this->assertSame(1, $this->auditJumlah());
        }
    }
    public function test_jawaban_teks_tanpa_berkas_dapat_dikirim(): void
    {
        $p = $this->kirim($this->isi($this->draf($this->kegiatan()), 'Jawaban teks', []));
        $this->assertSame(Pengumpulan::DIKIRIM, $p->status);
        $this->assertSame(0, $p->lampiran()->count());
    }
    public function test_jawaban_berkas_tanpa_teks_dapat_dikirim(): void
    {
        $p = $this->kirim($this->isi($this->draf($this->kegiatan()), null, ['11']));
        $this->assertSame(Pengumpulan::DIKIRIM, $p->status);
    }
    public function test_tepat_pada_tenggat_sudah_ditolak(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()));
        $this->jam('2030-01-10 04:00:00');
        $this->expectException(ValidationException::class);
        $this->kirim($p);
    }
    public function test_sebelum_mulai_tidak_bisa_membuat_draf(): void
    {
        $k = $this->kegiatan(['buka_lokal' => '2030-01-10T11:00']);
        $this->expectException(ValidationException::class);
        $this->draf($k);
    }
    public function test_penutupan_oleh_dosen_menghentikan_pengiriman(): void
    {
        $k = $this->kegiatan();
        $p = $this->isi($this->draf($k));
        app(KelolaKegiatan::class)->status('tutup', 2, $k, ['versi' => $k->versiForm(), 'alasan' => 'Menutup kegiatan untuk pengujian.']);
        $this->expectException(ValidationException::class);
        $this->kirim($p);
    }
    public function test_konteks_nonaktif_memblokir_pengiriman(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()));
        $audit = $this->auditJumlah();
        foreach (
            [
                ['detail_krs', 1, 'dibatalkan', 'aktif'],
                ['krs', 1, 'dibatalkan', 'disahkan'],
                ['registrasi_semester', 1, 'nonaktif', 'aktif'],
                ['riwayat_studi', 1, 'nonaktif', 'aktif'],
                ['kelas_kuliah', 100, 'selesai', 'aktif'],
                ['periode_akademik', 1, 'tutup', 'aktif'],
                ['pertemuan', 1, 'batal', 'terjadwal']
            ] as [$t, $id, $mati, $aktif]
        ) {
            DB::table($t)->where('id', $id)->update(['status' => $mati]);
            try {
                $this->kirim($p);
                $this->fail('Pengiriman harus ditolak untuk ' . $t);
            } catch (ValidationException) {
                $this->assertSame(Pengumpulan::DRAF, $p->fresh()->status);
                $this->assertSame($audit, $this->auditJumlah());
            }
            DB::table($t)->where('id', $id)->update(['status' => $aktif]);
        }
    }
    public function test_revisi_lama_tidak_menimpa_draf(): void
    {
        $p = $this->draf($this->kegiatan());
        $versi = $p->versiForm();
        $this->isi($p, 'Jawaban baru');
        $this->expectException(ValidationException::class);
        app(KelolaPengumpulan::class)->ubah(3, $p, ['versi_form' => $versi, 'jawaban_teks' => 'Jawaban usang', 'berkas_ids' => []]);
    }
    public function test_draf_hanya_dibaca_pemilik_bukan_admin_atau_dosen(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()));
        $a = app(AksesPengumpulan::class);
        $this->assertTrue($a->lihat(User::findOrFail(3), $p));
        foreach ([1, 2, 4, 5, 6] as $id) {
            $this->assertFalse($a->lihat(User::findOrFail($id), $p));
        }
    }
    public function test_kiriman_final_hanya_pemilik_admin_akademik_dan_dosen_kelas(): void
    {
        $p = $this->kirim($this->isi($this->draf($this->kegiatan())));
        $a = app(AksesPengumpulan::class);
        foreach ([1, 2, 3] as $id) {
            $this->assertTrue($a->lihat(User::findOrFail($id), $p));
        }
        foreach ([4, 5, 6] as $id) {
            $this->assertFalse($a->lihat(User::findOrFail($id), $p));
        }
        DB::table('pengajar_kelas')->where('dosen_id', 20)->update(['aktif' => false]);
        $this->assertFalse($a->lihat(User::findOrFail(2), $p));
    }
    public function test_pencabutan_krs_tidak_menghapus_akses_bukti_sendiri(): void
    {
        $k = $this->kegiatan();
        $p = $this->kirim($this->isi($this->draf($k)));
        $a = app(AksesPengumpulan::class);
        DB::table('krs')->where('id', 1)->update(['status' => 'dibatalkan']);
        $this->assertTrue($a->lihat(User::findOrFail(3), $p));
        $this->assertFalse($a->bolehTulis(User::findOrFail(3), $k));
        DB::table('users')->where('id', 3)->update(['status' => 'nonaktif']);
        $this->assertFalse($a->lihat(User::findOrFail(3), $p));
    }
    public function test_berkas_orang_lain_ditolak_tanpa_perubahan_parsial(): void
    {
        $p = $this->draf($this->kegiatan());
        try {
            $this->isi($p, 'Teks', ['11', '12']);
            $this->fail('Berkas orang lain harus ditolak.');
        } catch (ValidationException) {
            $this->assertSame(0, $p->semuaLampiran()->count());
            $this->assertNull($p->fresh()->jawaban_teks);
            $this->assertSame(1, $this->auditJumlah());
        }
    }
    public function test_batas_jumlah_ukuran_dan_format_diperiksa(): void
    {
        $p = $this->draf($this->kegiatan(['maks_berkas' => 1]));
        try {
            $this->isi($p, 'Teks', ['11', '13']);
            $this->fail('Jumlah harus ditolak.');
        } catch (ValidationException) {
            $this->assertSame(0, $p->lampiran()->count());
        }
        DB::table('berkas')->where('id', 11)->update(['ukuran_byte' => 20971520]);
        try {
            $this->isi($p);
            $this->fail('Ukuran harus ditolak.');
        } catch (ValidationException) {
            $this->assertSame(0, $p->lampiran()->count());
        }
        DB::table('berkas')->where('id', 11)->update(['ukuran_byte' => 12, 'ekstensi' => 'png', 'mime_type' => 'image/png']);
        $this->expectException(ValidationException::class);
        $this->isi($p);
    }
    public function test_status_file_dicek_lagi_ketika_kirim(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()));
        DB::table('berkas')->where('id', 11)->update(['status' => 'dihapus']);
        $this->expectException(ValidationException::class);
        $this->kirim($p);
    }
    public function test_perubahan_hash_file_ditolak(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()));
        DB::table('berkas')->where('id', 11)->update(['sha256' => str_repeat('f', 64)]);
        $this->expectException(ValidationException::class);
        $this->kirim($p);
    }
    public function test_jawaban_dan_lampiran_final_tidak_dapat_diubah(): void
    {
        $p = $this->kirim($this->isi($this->draf($this->kegiatan())));
        try {
            DB::transaction(function () use ($p): void {
                $p->jawaban_teks = 'Diubah';
                $p->revisi++;
                $p->save();
            });
            $this->fail('Jawaban final harus terkunci.');
        } catch (LogicException) {
            $this->assertSame('Jawaban mahasiswa A', $p->fresh()->jawaban_teks);
        }
        $this->expectException(LogicException::class);
        DB::transaction(function () use ($p): void {
            $l = $p->lampiran()->firstOrFail();
            $l->aktif = false;
            $l->dilepas_at = now('UTC');
            $l->save();
        });
    }
    public function test_unique_database_mencegah_dua_draf_per_peserta(): void
    {
        $p = $this->draf($this->kegiatan());
        $row = $p->getAttributes();
        unset($row['id']);
        $row['versi'] = 2;
        $row['token_draf'] = (string) Str::uuid();
        $row['kunci_kirim'] = bin2hex(random_bytes(32));
        $this->expectException(QueryException::class);
        DB::table('pengumpulan')->insert($row);
    }
    public function test_dosen_tidak_bisa_meminta_tautan_lampiran_draf(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()));
        $this->actingAs(User::findOrFail(2), 'web');
        $this->expectException(AuthorizationException::class);
        app(PengumpulanController::class)->tautan(Request::create('/pengumpulan/' . $p->id), $p, $p->lampiran()->firstOrFail());
    }
    public function test_input_pemilik_palsu_ditolak_oleh_request(): void
    {
        $p = $this->draf($this->kegiatan());
        $this->actingAs(User::findOrFail(3), 'web');
        $this->patchJson(route('pengumpulan.update', $p), [
            'versi_form' => $p->versiForm(),
            'jawaban_teks' => 'Teks',
            'lampiran_ids' => '',
            'pemilik_id' => 6
        ])->assertUnprocessable()->assertJsonValidationErrors('pemilik_id');
    }
    public function test_html_jawaban_ditampilkan_sebagai_teks(): void
    {
        $p = $this->isi($this->draf($this->kegiatan()), '<script>alert(1)</script>', []);
        $this->actingAs(User::findOrFail(3), 'web');
        $r = Request::create('/pengumpulan/' . $p->id);
        $r->setUserResolver(fn() => User::findOrFail(3));
        $html = app(PengumpulanController::class)->show($r, $p, app(AksesPengumpulan::class))->render();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }
    public function test_rekap_tidak_membaca_draf_sebagai_kiriman(): void
    {
        $k = $this->kegiatan();
        $this->isi($this->draf($k));
        $this->actingAs(User::findOrFail(2), 'web');
        $r = Request::create('/pengumpulan/kegiatan/' . $k->id . '/rekap');
        $r->setUserResolver(fn() => User::findOrFail(2));
        $data = app(PengumpulanController::class)->rekap($r, $k, app(AksesPengumpulan::class))->getData();
        $this->assertSame(2, $data['total']);
        $this->assertSame(0, $data['sudah']);
        $this->assertSame(0, $data['historis']->total());
        $this->kirim(Pengumpulan::firstOrFail());
        $data = app(PengumpulanController::class)->rekap($r, $k, app(AksesPengumpulan::class))->getData();
        $this->assertSame(1, $data['sudah']);
        $this->assertSame(1, $data['historis']->total());
    }
}
