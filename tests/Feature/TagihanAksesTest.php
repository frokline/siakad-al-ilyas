<?php

namespace Tests\Feature;

use App\Actions\KelolaTagihan;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AksesTagihan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\ValidationException;

class TagihanAksesTest extends TagihanDatabaseTestCase
{
    public function test_pemilik_diambil_dari_registrasi_dan_draf_diaudit(): void
    {
        $t = $this->buat();
        $this->assertSame(1, $t->mahasiswa_id);
        $this->assertSame('draf', $t->status);
        $this->assertSame(1, $t->audits()->count());
        $this->assertSame('250000.00', $t->nominal);
    }
    public function test_token_yang_sama_tidak_membuat_duplikat(): void
    {
        $d = $this->data();
        $a = app(KelolaTagihan::class);
        $x = $a->buat(1, $d);
        $y = $a->buat(1, $d);
        $this->assertSame($x->id, $y->id);
        $this->assertSame(1, Tagihan::count());
        $this->assertSame(1, $x->audits()->count());
    }
    public function test_token_sama_isi_berbeda_ditolak(): void
    {
        $d = $this->data();
        app(KelolaTagihan::class)->buat(1, $d);
        $d['nominal'] = '260000';
        $this->expectException(ValidationException::class);
        app(KelolaTagihan::class)->buat(1, $d);
    }
    public function test_bulan_ganda_lintas_registrasi_ditolak(): void
    {
        $this->buat();
        $this->expectException(ValidationException::class);
        $this->buat(['registrasi_semester_id' => 3]);
    }
    public function test_bulan_berbeda_boleh(): void
    {
        $this->buat();
        $this->buat(['bulan_tagihan' => 10]);
        $this->assertSame(2, Tagihan::count());
    }
    public function test_mahasiswa_tidak_melihat_draf_atau_tagihan_orang_lain(): void
    {
        $t = $this->buat();
        $akses = app(AksesTagihan::class);
        $owner = User::findOrFail(4);
        $this->assertFalse($akses->lihat($owner, $t));
        $t = $this->transisi($t, 'terbitkan');
        $this->assertTrue($akses->lihat($owner, $t));
        $this->assertFalse($akses->lihat(User::findOrFail(3), $t));
        $this->assertSame(0, $akses->batasi(Tagihan::query(), User::findOrFail(3))->count());
    }
    public function test_akademik_saja_dan_akun_nonaktif_tidak_berwenang(): void
    {
        $a = app(AksesTagihan::class);
        $this->assertFalse($a->masuk(User::findOrFail(2)));
        $this->assertFalse($a->masuk(User::findOrFail(5)));
    }
    public function test_role_dicabut_menghentikan_penulisan(): void
    {
        DB::table('user_roles')->where('user_id', 1)->delete();
        try {
            $this->buat();
            $this->fail('Harus ditolak.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertSame(0, Tagihan::count());
    }
    public function test_snapshot_tidak_berubah_saat_katalog_berubah(): void
    {
        $t = $this->transisi($this->buat(), 'terbitkan');
        DB::table('jenis_biaya')->where('id', 1)->update(['nama' => 'Nama Baru']);
        $this->assertSame('SPP Bulanan', $t->fresh()->snapshot['jenis_nama']);
    }
    public function test_jenis_nonaktif_menghalangi_penerbitan(): void
    {
        $t = $this->buat();
        DB::table('jenis_biaya')->where('id', 1)->update(['aktif' => false]);
        $this->expectException(ValidationException::class);
        $this->transisi($t, 'terbitkan');
    }
    public function test_registrasi_batal_menghalangi_penerbitan(): void
    {
        $t = $this->buat();
        DB::table('registrasi_semester')->where('id', 1)->update(['status' => 'batal']);
        $this->expectException(ValidationException::class);
        $this->transisi($t, 'terbitkan');
    }
    public function test_form_lama_ditolak(): void
    {
        $t = $this->buat();
        $versi = $t->versiForm();
        $t = $this->transisi($t, 'terbitkan');
        $this->expectException(ValidationException::class);
        app(KelolaTagihan::class)->transisi(1, $t, 'batalkan', ['versi' => $versi, 'alasan' => 'Pembatalan menggunakan formulir lama.']);
    }
    public function test_koreksi_memakai_record_dan_nomor_yang_sama(): void
    {
        $t = $this->buat();
        $id = $t->id;
        $nomor = $t->nomor;
        $t = $this->transisi($t, 'terbitkan');
        $t = $this->transisi($t, 'batalkan');
        $t = $this->transisi($t, 'buka_draf');
        $t = app(KelolaTagihan::class)->ubah(1, $t, ['nominal' => '260000', 'jatuh_tempo' => '2026-10-01', 'versi' => $t->versiForm(), 'alasan' => 'Koreksi jumlah sesuai keputusan akademik.']);
        $t = $this->transisi($t, 'terbitkan');
        $this->assertSame($id, $t->id);
        $this->assertSame($nomor, $t->nomor);
        $this->assertSame(6, $t->audits()->count());
        $this->assertSame('260000.00', $t->nominal);
    }
    public function test_nominal_terbit_tidak_bisa_diedit(): void
    {
        $t = $this->transisi($this->buat(), 'terbitkan');
        $this->expectException(ValidationException::class);
        app(KelolaTagihan::class)->ubah(1, $t, ['nominal' => '1', 'jatuh_tempo' => '2026-10-01', 'versi' => $t->versiForm(), 'alasan' => 'Uji perubahan nominal terbit.']);
    }
    public function test_riwayat_pembayaran_memblokir_pembatalan(): void
    {
        $t = $this->transisi($this->buat(), 'terbitkan');
        Schema::create('pembayaran', function (Blueprint $b): void {
            $b->id();
            $b->unsignedBigInteger('tagihan_id');
        });
        DB::table('pembayaran')->insert(['tagihan_id' => $t->id]);
        $this->expectException(ValidationException::class);
        $this->transisi($t, 'batalkan');
    }
    public function test_gagal_audit_membatalkan_perubahan(): void
    {
        $t = $this->buat();
        $a = DB::table('audit_log')->where('entitas', 'tagihan')->first();
        $row = (array) $a;
        unset($row['id']);
        $row['versi_entitas'] = 2;
        DB::table('audit_log')->insert($row);
        try {
            $this->transisi($t, 'terbitkan');
            $this->fail('Audit harus gagal karena versi duplikat.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
        $this->assertSame('draf', $t->fresh()->status);
        $this->assertSame(1, $t->fresh()->revisi);
    }
    public function test_hapus_model_dilarang(): void
    {
        $t = $this->buat();
        $this->expectException(\LogicException::class);
        $t->delete();
    }
    public function test_http_akses_pemilik_dan_escaping(): void
    {
        $t = $this->transisi($this->buat(['catatan' => '<script>alert(1)</script>']), 'terbitkan');
        $this->actingAs(User::findOrFail(4), 'web')->get(route('tagihan.show', $t))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->actingAs(User::findOrFail(3), 'web')->get(route('tagihan.show', $t))->assertForbidden();
    }
    public function test_http_mahasiswa_tidak_bisa_membuat_tagihan(): void
    {
        $this->actingAs(User::findOrFail(4), 'web')->postJson(route('tagihan.store'), $this->data())->assertForbidden();
    }
    public function test_http_nominal_dan_bulan_invalid_ditolak(): void
    {
        $this->actingAs(User::findOrFail(1), 'web')->postJson(route('tagihan.store'), $this->data(['bulan_tagihan' => 13]))->assertUnprocessable()->assertJsonValidationErrors('bulan_tagihan');
        $this->postJson(route('tagihan.store'), $this->data(['nominal' => '1e6']))->assertUnprocessable()->assertJsonValidationErrors('nominal');
    }
}
