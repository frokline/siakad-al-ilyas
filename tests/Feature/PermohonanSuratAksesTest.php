<?php

namespace Tests\Feature;

use App\Actions\KelolaPermohonanSurat;
use App\Models\Berkas;
use App\Models\PermohonanSurat;
use App\Models\User;
use App\Services\BerkasSurat;
use App\Services\PrivasiBuktiPembayaran;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PermohonanSuratAksesTest extends SuratDatabaseTestCase
{
    public function test_pengajuan_mencatat_snapshot_riwayat_dan_audit(): void
    {
        $p = $this->ajukanSurat(['lampiran_berkas_id' => 11]);
        $this->assertSame('diajukan', $p->status);
        $this->assertSame('M001', $p->akademik_snapshot['nim']);
        $this->assertSame(1, $p->riwayat()->count());
        $this->assertSame(1, $p->audits()->count());
        $this->assertSame(11, $p->lampiran_snapshot['id']);
        $this->assertNotNull($p->slot_aktif);
    }
    public function test_token_pengajuan_berulang_tidak_menduplikasi(): void
    {
        $a = app(KelolaPermohonanSurat::class);
        $v = $this->dataSurat();
        $p = $a->ajukan(4, $v);
        $this->assertSame($p->id, $a->ajukan(4, $v)->id);
        $this->assertSame(1, PermohonanSurat::count());
        $this->assertSame(1, $p->riwayat()->count());
    }
    public function test_token_sama_isi_berbeda_ditolak(): void
    {
        $v = $this->dataSurat();
        $a = app(KelolaPermohonanSurat::class);
        $a->ajukan(4, $v);
        $v['keperluan'] = 'Keperluan baru yang berbeda.';
        $this->expectException(ValidationException::class);
        $a->ajukan(4, $v);
    }
    public function test_permohonan_aktif_ganda_ditolak(): void
    {
        $this->ajukanSurat();
        $this->expectException(ValidationException::class);
        $this->ajukanSurat();
    }
    public function test_registrasi_mahasiswa_lain_ditolak(): void
    {
        try {
            $this->ajukanSurat(['registrasi_semester_id' => 2]);
            $this->fail('Harus ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame(0, PermohonanSurat::count());
        }
    }
    public function test_registrasi_cuti_ditolak(): void
    {
        DB::table('registrasi_semester')->where('id', 1)->update(['status' => 'cuti']);
        $this->expectException(ValidationException::class);
        $this->ajukanSurat();
    }
    public function test_periode_tidak_aktif_ditolak(): void
    {
        DB::table('periode_akademik')->where('id', 1)->update(['status' => 'arsip']);
        $this->expectException(ValidationException::class);
        $this->ajukanSurat();
    }
    public function test_jenis_nonaktif_ditolak(): void
    {
        DB::table('jenis_surat')->where('id', 1)->update(['aktif' => false]);
        $this->expectException(ValidationException::class);
        $this->ajukanSurat();
    }
    public function test_form_jenis_lama_ditolak(): void
    {
        $v = $this->dataSurat();
        DB::table('jenis_surat')->where('id', 1)->increment('revisi');
        $this->expectException(ValidationException::class);
        app(KelolaPermohonanSurat::class)->ajukan(4, $v);
    }
    public function test_lampiran_milik_orang_lain_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        $this->ajukanSurat(['lampiran_berkas_id' => 12]);
    }
    public function test_lampiran_pembelajaran_ditolak(): void
    {
        DB::table('materi_berkas')->insert(['berkas_id' => 11]);
        $this->expectException(ValidationException::class);
        $this->ajukanSurat(['lampiran_berkas_id' => 11]);
    }
    public function test_bukti_pembayaran_ditolak_sebagai_lampiran(): void
    {
        DB::table('pembayaran')->insert(['bukti_berkas_id' => 11]);
        $this->expectException(ValidationException::class);
        $this->ajukanSurat(['lampiran_berkas_id' => 11]);
    }
    public function test_dokumen_surat_ditolak_sebagai_bukti_pembayaran(): void
    {
        $this->ajukanSurat(['lampiran_berkas_id' => 11]);
        $this->expectException(ValidationException::class);
        DB::transaction(fn() => PrivasiBuktiPembayaran::khususKeuangan(Berkas::whereKey(11)->lockForUpdate()->firstOrFail()));
    }
    public function test_berkas_rusak_ditolak_sebelum_pengajuan(): void
    {
        $b = Berkas::findOrFail(11);
        Storage::disk('berkas_local')->put($b->object_key, str_repeat('X', $b->ukuran_byte));
        $this->expectException(ValidationException::class);
        $this->ajukanSurat(['lampiran_berkas_id' => 11]);
    }
    public function test_mahasiswa_hanya_melihat_permohonan_sendiri(): void
    {
        $p = $this->ajukanSurat();
        $lain = $this->ajukanSurat(['registrasi_semester_id' => 2], 3);
        $this->actingAs(User::findOrFail(4), 'web')->get(route('surat.index'))->assertOk()->assertSee($p->nomor_pengajuan)->assertDontSee($lain->nomor_pengajuan);
        $this->get(route('surat.show', $lain))->assertForbidden();
    }
    public function test_keuangan_saja_tidak_masuk_portal_surat(): void
    {
        $this->actingAs(User::findOrFail(2), 'web')->get(route('surat.index'))->assertForbidden();
    }
    public function test_mahasiswa_tidak_dapat_memproses(): void
    {
        $p = $this->ajukanSurat();
        $this->actingAs(User::findOrFail(4), 'web')->postJson(route('surat.tindakan', $p), $this->dataTindakan($p, 'diproses'))->assertForbidden();
        $this->assertSame('diajukan', $p->fresh()->status);
    }
    public function test_peran_petugas_dicabut_diperiksa_ulang(): void
    {
        $p = $this->ajukanSurat();
        DB::table('user_roles')->where('user_id', 1)->delete();
        try {
            $this->tindakanSurat($p, 'diproses');
            $this->fail('Harus ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('diajukan', $p->fresh()->status);
        }
    }
    public function test_pemohon_boleh_batal_sebelum_diproses(): void
    {
        $p = $this->tindakanSurat($this->ajukanSurat(), 'dibatalkan', [], 4);
        $this->assertNull($p->slot_aktif);
        $this->assertSame(2, $p->riwayat()->count());
        $this->assertNotSame($p->id, $this->ajukanSurat()->id);
    }
    public function test_pemohon_tidak_boleh_batal_setelah_diproses(): void
    {
        $p = $this->tindakanSurat($this->ajukanSurat(), 'diproses');
        $this->actingAs(User::findOrFail(4), 'web')->postJson(route('surat.tindakan', $p), $this->dataTindakan($p, 'dibatalkan'))->assertForbidden();
    }
    public function test_penolakan_menyimpan_alasan_dan_membuka_pengajuan_baru(): void
    {
        $p = $this->tindakanSurat($this->ajukanSurat(), 'ditolak', ['catatan' => 'Tujuan permohonan belum jelas.']);
        $this->assertSame('ditolak', $p->status);
        $this->assertSame('Tujuan permohonan belum jelas.', $p->riwayat()->orderByDesc('id')->first()->catatan);
        $this->assertNotSame($p->id, $this->ajukanSurat()->id);
    }
    public function test_penerbitan_mencatat_nomor_pdf_riwayat_dan_audit(): void
    {
        $p = $this->terbitkanSurat();
        $this->assertSame('terbit', $p->status);
        $this->assertSame(13, $p->hasil_berkas_id);
        $this->assertSame('001/ILYAS/IX/2026', $p->nomor_surat);
        $this->assertNull($p->slot_aktif);
        $this->assertSame(3, $p->riwayat()->count());
        $this->assertSame(3, $p->audits()->count());
    }
    public function test_penerbitan_memerlukan_registrasi_masih_aktif(): void
    {
        $p = $this->tindakanSurat($this->ajukanSurat(), 'diproses');
        DB::table('registrasi_semester')->where('id', 1)->update(['status' => 'cuti']);
        $this->expectException(ValidationException::class);
        $this->tindakanSurat($p, 'terbit');
    }
    public function test_perubahan_nama_mahasiswa_menghalangi_penerbitan_snapshot_lama(): void
    {
        $p = $this->tindakanSurat($this->ajukanSurat(), 'diproses');
        DB::table('users')->where('id', 4)->update(['nama' => 'Nama diperbaiki']);
        $this->expectException(ValidationException::class);
        $this->tindakanSurat($p, 'terbit');
    }
    public function test_nonaktif_katalog_tidak_menghapus_permohonan_lama(): void
    {
        $p = $this->tindakanSurat($this->ajukanSurat(), 'diproses');
        DB::table('jenis_surat')->where('id', 1)->update(['aktif' => false, 'syarat' => 'Syarat baru']);
        $p = $this->tindakanSurat($p, 'terbit');
        $this->assertSame('terbit', $p->status);
        $this->assertSame('Tujuan penggunaan surat.', $p->jenis_snapshot['syarat']);
    }
    public function test_pdf_final_harus_milik_petugas_penerbit(): void
    {
        $p = $this->tindakanSurat($this->ajukanSurat(), 'diproses');
        $this->expectException(ValidationException::class);
        $this->tindakanSurat($p, 'terbit', ['hasil_berkas_id' => 14]);
    }
    public function test_nomor_surat_tidak_boleh_dipakai_ulang(): void
    {
        $this->terbitkanSurat();
        $p = $this->tindakanSurat($this->ajukanSurat(), 'diproses');
        $this->expectException(ValidationException::class);
        $this->tindakanSurat($p, 'terbit', ['hasil_berkas_id' => 16]);
    }
    public function test_form_lama_ditolak_tanpa_riwayat_tambahan(): void
    {
        $p = $this->ajukanSurat();
        $versi = $p->versiForm();
        $p = $this->tindakanSurat($p, 'diproses');
        try {
            $this->tindakanSurat($p, 'ditolak', ['versi' => $versi]);
            $this->fail('Harus ditolak.');
        } catch (ValidationException) {
            $this->assertSame(2, $p->riwayat()->count());
            $this->assertSame('diproses', $p->fresh()->status);
        }
    }
    public function test_pencabutan_menyimpan_nomor_hasil_dan_memblokir_unduhan(): void
    {
        $p = $this->terbitkanSurat();
        $url = $this->urlHasil($p);
        $p = $this->tindakanSurat($p, 'dibatalkan');
        $this->assertSame(13, $p->hasil_berkas_id);
        $this->assertNotNull($p->nomor_surat);
        $this->assertSame(4, $p->riwayat()->count());
        $this->actingAs(User::findOrFail(4), 'web')->get($url)->assertForbidden();
    }
    public function test_pemilik_dapat_mengunduh_hasil_dengan_tautan_terikat_akun(): void
    {
        $p = $this->terbitkanSurat();
        $url = $this->urlHasil($p);
        $this->actingAs(User::findOrFail(4), 'web')->get($url)->assertOk()->assertDownload();
        $this->actingAs(User::findOrFail(1), 'web')->get($url)->assertForbidden();
        $this->actingAs(User::findOrFail(3), 'web')->get($url)->assertForbidden();
    }
    public function test_tautan_kedaluwarsa_ditolak(): void
    {
        $p = $this->terbitkanSurat();
        $url = $this->urlHasil($p, -1);
        $this->actingAs(User::findOrFail(4), 'web')->get($url)->assertForbidden();
    }
    public function test_unduhan_isi_rusak_tidak_mengirim_dokumen(): void
    {
        $p = $this->terbitkanSurat();
        $b = Berkas::findOrFail(13);
        Storage::disk('berkas_local')->put($b->object_key, str_repeat('X', $b->ukuran_byte));
        $this->actingAs(User::findOrFail(4), 'web')->get($this->urlHasil($p))->assertStatus(503);
    }
    public function test_kegagalan_audit_membatalkan_status_dan_riwayat(): void
    {
        $p = $this->ajukanSurat();
        DB::table('audit_log')->insert([
            'entitas' => 'permohonan_surat',
            'entitas_id' => $p->id,
            'versi_entitas' => 2,
            'aksi' => 'fixture',
            'sesudah' => '{}',
            'waktu' => now('UTC')
        ]);
        try {
            $this->tindakanSurat($p, 'diproses');
            $this->fail('Audit harus gagal.');
        } catch (QueryException) {
            $this->assertSame('diajukan', $p->fresh()->status);
            $this->assertSame(1, $p->riwayat()->count());
        }
    }
    public function test_riwayat_tidak_dapat_diedit(): void
    {
        $r = $this->ajukanSurat()->riwayat()->firstOrFail();
        $this->expectException(LogicException::class);
        DB::transaction(function () use ($r): void {
            $r->catatan = 'Mengganti catatan lama.';
            $r->save();
        });
    }
    public function test_pengajuan_tidak_dapat_dihapus(): void
    {
        $p = $this->ajukanSurat();
        $this->expectException(LogicException::class);
        $p->delete();
    }
    public function test_halaman_form_dan_detail_menggunakan_escaping(): void
    {
        $p = $this->ajukanSurat(['keperluan' => '<script>alert(1)</script>']);
        $this->actingAs(User::findOrFail(4), 'web')->get(route('surat.create'))->assertOk();
        $this->get(route('surat.show', $p))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $p = $this->tindakanSurat($p, 'diproses');
        $this->actingAs(User::findOrFail(1), 'web')->get(route('surat.show', $p))->assertOk()->assertSee('hasil_berkas_id');
    }
    public function test_snapshot_berkas_tidak_bergantung_urutan_kunci_json(): void
    {
        $b = Berkas::findOrFail(11);
        $s = array_reverse(BerkasSurat::snapshot($b), true);
        $this->assertTrue(BerkasSurat::cocok($b, $s));
    }
    public function test_perintah_integritas_menemukan_riwayat_hilang(): void
    {
        $p = $this->terbitkanSurat();
        $this->artisan('surat:periksa-riwayat')->assertExitCode(0);
        DB::table('riwayat_surat')->where('permohonan_surat_id', $p->id)->where('revisi_permohonan', 2)->delete();
        $this->artisan('surat:periksa-riwayat')->assertExitCode(1);
    }
    public function test_admin_tidak_memproses_permohonan_dirinya_sendiri(): void
    {
        $p = $this->ajukanSurat();
        DB::table('user_roles')->insert(['user_id' => 4, 'role_id' => 1]);
        $this->actingAs(User::findOrFail(4), 'web')->postJson(route('surat.tindakan', $p), $this->dataTindakan($p, 'diproses'))->assertForbidden();
    }
    public function test_berkas_surat_tidak_dapat_ditautkan_ke_pembelajaran(): void
    {
        $this->ajukanSurat(['lampiran_berkas_id' => 11]);
        $lampiran = new class extends \Illuminate\Database\Eloquent\Model {
            use \App\Models\Concerns\MenjagaBuktiPembayaranPrivat;
            protected $table = 'materi_berkas';
            public $timestamps = false;
        };
        $lampiran->berkas_id = 11;
        $this->expectException(ValidationException::class);
        DB::transaction(fn() => $lampiran->save());
    }
    private function urlHasil(PermohonanSurat $p, int $menit = 2): string
    {
        return URL::temporarySignedRoute('surat.unduh', now()->addMinutes($menit), [
            'permohonanSurat' => $p->id,
            'bagian' => 'hasil',
            'pemohon' => 4,
            'versi' => $p->revisi,
            'versi_berkas' => 2
        ]);
    }
}
