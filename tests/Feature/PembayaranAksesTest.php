<?php

namespace Tests\Feature;

use App\Actions\KelolaPembayaran;
use App\Actions\KelolaTagihan;
use App\Models\Berkas;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\AksesPembayaran;
use App\Services\BuktiPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class PembayaranAksesTest extends PembayaranDatabaseTestCase
{
    public function test_pengajuan_menunggu_snapshot_dan_audit(): void
    {
        $t = $this->tagihanTerbit();
        $p = $this->ajukan($t);
        $this->assertSame('menunggu', $p->status);
        $this->assertSame((int) $t->id, $p->tagihan_aktif_id);
        $this->assertSame(1, $p->audits()->count());
        $this->assertTrue(BuktiPembayaran::cocok($p, Berkas::findOrFail(11)));
    }
    public function test_token_sama_tidak_membuat_pengajuan_ganda(): void
    {
        $t = $this->tagihanTerbit();
        $d = $this->dataBayar($t);
        $a = app(KelolaPembayaran::class);
        $x = $a->ajukan(4, $t, $d);
        $y = $a->ajukan(4, $t, $d);
        $this->assertSame($x->id, $y->id);
        $this->assertSame(1, Pembayaran::count());
        $this->assertSame(1, $x->audits()->count());
    }
    public function test_token_sama_isi_berbeda_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        $d = $this->dataBayar($t);
        $a = app(KelolaPembayaran::class);
        $a->ajukan(4, $t, $d);
        $d['referensi_bank'] = 'LAIN';
        $this->expectException(ValidationException::class);
        $a->ajukan(4, $t, $d);
    }
    public function test_satu_tagihan_tidak_bisa_dua_pengajuan_menunggu(): void
    {
        $t = $this->tagihanTerbit();
        $this->ajukan($t);
        $this->expectException(ValidationException::class);
        $this->ajukan($t, ['bukti_berkas_id' => 14]);
    }
    public function test_nominal_cicilan_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        $this->expectException(ValidationException::class);
        $this->ajukan($t, ['nominal_diajukan' => '100000']);
    }
    public function test_bukti_akun_lain_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        $this->expectException(ValidationException::class);
        $this->ajukan($t, ['bukti_berkas_id' => 12]);
    }
    public function test_isi_bukti_diubah_dengan_ukuran_sama_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        $b = Berkas::findOrFail(11);
        Storage::disk('berkas_local')->put($b->object_key, str_repeat('X', $b->ukuran_byte));
        $this->expectException(ValidationException::class);
        $this->ajukan($t);
    }
    public function test_berkas_belum_tersedia_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        DB::table('berkas')->where('id', 11)->update(['status' => 'menunggu']);
        $this->expectException(ValidationException::class);
        $this->ajukan($t);
    }
    public function test_bukti_identik_tidak_bisa_untuk_tagihan_lain(): void
    {
        $t = $this->tagihanTerbit();
        $p = $this->ajukan($t);
        $this->batal($p);
        $lain = $this->tagihanTerbit(['bulan_tagihan' => 10]);
        $this->expectException(ValidationException::class);
        $this->ajukan($lain, ['bukti_berkas_id' => 14]);
    }
    public function test_batal_melepas_slot_tetapi_menjaga_histori(): void
    {
        $t = $this->tagihanTerbit();
        $p = $this->batal($this->ajukan($t));
        $this->assertNull($p->tagihan_aktif_id);
        $this->assertNull($p->bukti_aktif_sha256);
        $this->assertSame(2, $p->audits()->count());
        $q = $this->ajukan($t);
        $this->assertNotSame($p->id, $q->id);
        $this->assertSame(2, Pembayaran::count());
    }
    public function test_tagihan_dengan_histori_pembayaran_tidak_bisa_dikoreksi(): void
    {
        $t = $this->tagihanTerbit();
        $this->batal($this->ajukan($t));
        $this->expectException(ValidationException::class);
        app(KelolaTagihan::class)->transisi(1, $t, 'batalkan', ['versi' => $t->versiForm(), 'alasan' => 'Mencoba mengoreksi tagihan berhistori.']);
    }
    public function test_form_tagihan_lama_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        $this->expectException(ValidationException::class);
        $this->ajukan($t, ['versi_tagihan' => str_repeat('a', 64)]);
    }
    public function test_rekening_berubah_setelah_form_dibuka_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        $d = $this->dataBayar($t);
        config(['pembayaran.tujuan_transfer' => 'BANK UJI / 999 / TUJUAN BARU']);
        $this->expectException(ValidationException::class);
        app(KelolaPembayaran::class)->ajukan(4, $t, $d);
    }
    public function test_admin_boleh_mengajukan_dengan_berkas_sendiri(): void
    {
        $t = $this->tagihanTerbit();
        $p = $this->ajukan($t, ['bukti_berkas_id' => 13], 1);
        $this->assertSame(1, $p->pengunggah_id);
        $this->assertTrue(app(AksesPembayaran::class)->lihat(User::findOrFail(4), $p));
    }
    public function test_orang_lain_dan_admin_akademik_tidak_melihat_bukti(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $a = app(AksesPembayaran::class);
        $this->assertFalse($a->lihat(User::findOrFail(3), $p));
        $this->assertFalse($a->lihat(User::findOrFail(2), $p));
        $this->assertTrue($a->lihat(User::findOrFail(1), $p));
        $this->assertSame(0, $a->batasi(Pembayaran::query(), User::findOrFail(3))->count());
    }
    public function test_nonaktif_dan_peran_dicabut_menghentikan_akses(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $u = User::findOrFail(1);
        $u->load('roles');
        DB::table('user_roles')->where('user_id', 1)->delete();
        $this->assertFalse(app(AksesPembayaran::class)->lihat($u, $p));
        DB::table('users')->where('id', 4)->update(['status' => 'nonaktif']);
        $this->assertFalse(app(AksesPembayaran::class)->lihat(User::findOrFail(4), $p));
    }
    public function test_gagal_audit_membatalkan_pengajuan(): void
    {
        $t = $this->tagihanTerbit();
        DB::table('audit_log')->insert([
            'pelaku_id' => 1,
            'entitas' => 'pembayaran',
            'entitas_id' => 1,
            'versi_entitas' => 1,
            'aksi' => 'fixture',
            'sesudah' => '{}',
            'waktu' => now('UTC')
        ]);
        try {
            $this->ajukan($t);
            $this->fail('Audit harus gagal.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
        $this->assertSame(0, Pembayaran::count());
    }
    public function test_slot_database_mencegah_pengajuan_aktif_ganda(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $row = (array) DB::table('pembayaran')->where('id', $p->id)->first();
        unset($row['id']);
        $row['nomor_pengajuan'] = 'BYR-FIXTURE';
        $row['form_token'] = (string) \Illuminate\Support\Str::uuid();
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('pembayaran')->insert($row);
    }
    public function test_bukti_dan_nominal_tidak_dapat_ditimpa(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $this->expectException(\LogicException::class);
        DB::transaction(function () use ($p): void {
            $p->nominal_diajukan = '1';
            $p->revisi++;
            $p->save();
        });
    }
    public function test_hard_delete_dilarang(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $this->expectException(\LogicException::class);
        $p->delete();
    }
    public function test_http_suntikan_status_ditolak(): void
    {
        $t = $this->tagihanTerbit();
        $this->actingAs(User::findOrFail(4), 'web')
            ->postJson(route('pembayaran.store', $t), $this->dataBayar($t, ['status' => 'diterima']))->assertUnprocessable()->assertJsonValidationErrors('status');
    }
    public function test_http_detail_escaping_dan_akses_orang_lain(): void
    {
        $p = $this->ajukan($this->tagihanTerbit(), ['referensi_bank' => '<script>alert(1)</script>']);
        $this->actingAs(User::findOrFail(4), 'web')->get(route('pembayaran.show', $p))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->actingAs(User::findOrFail(3), 'web')->get(route('pembayaran.show', $p))->assertForbidden();
    }
    public function test_unduh_bertanda_tangan_terikat_akun_dan_revisi(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $this->actingAs(User::findOrFail(4), 'web');
        $url = $this->get(route('pembayaran.tautan', $p))->assertRedirect()->headers->get('Location');
        $response = $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame(Storage::disk('berkas_local')->get(Berkas::findOrFail(11)->object_key), $response->streamedContent());
        $this->actingAs(User::findOrFail(1), 'web')->get($url)->assertForbidden();
        $this->batal($p);
        $this->actingAs(User::findOrFail(4), 'web')->get($url)->assertForbidden();
    }
    public function test_unduh_tanpa_signature_dan_signature_kedaluwarsa_ditolak(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $this->actingAs(User::findOrFail(4), 'web');
        $this->get(route('pembayaran.unduh', $p))->assertForbidden();
        $url = URL::temporarySignedRoute('pembayaran.unduh', now()->subMinute(), [
            'pembayaran' => $p->id,
            'pemohon' => 4,
            'versi_pembayaran' => $p->revisi,
            'versi_berkas' => 2
        ]);
        $this->get($url)->assertForbidden();
    }

    public function test_tagihan_diterima_tidak_dapat_diajukan_lagi(): void
    {
        $t = $this->tagihanTerbit();
        $p = $this->ajukan($t);
        // Simulasi data hasil modul verifikasi mendatang, bukan contoh penulisan produksi.
        DB::table('pembayaran')->where('id', $p->id)->update(['status' => 'diterima']);
        $this->expectException(ValidationException::class);
        $this->ajukan($t);
    }
    public function test_tunggakan_tetap_boleh_dibayar_saat_katalog_dan_registrasi_nonaktif(): void
    {
        $t = $this->tagihanTerbit();
        DB::table('jenis_biaya')->where('id', 1)->update(['aktif' => false]);
        DB::table('registrasi_semester')->where('id', 1)->update(['status' => 'cuti']);
        $p = $this->ajukan($t);
        $this->assertSame('menunggu', $p->status);
    }
    public function test_pembatalan_form_lama_ditolak(): void
    {
        $p = $this->ajukan($this->tagihanTerbit());
        $versi = $p->versiForm();
        $this->batal($p);
        $this->expectException(ValidationException::class);
        app(KelolaPembayaran::class)->batalkan(4, $p, ['versi' => $versi, 'alasan' => 'Pembatalan ulang dari formulir lama.', 'konfirmasi' => 1]);
    }
    public function test_form_pengajuan_dan_komponen_tagihan_dapat_dirender(): void
    {
        $t = $this->tagihanTerbit();
        $this->actingAs(User::findOrFail(4), 'web');
        $this->get(route('pembayaran.create', $t))->assertOk()->assertSee('Bukti uji 11')->assertDontSee('Bukti uji 12');
        $this->get(route('tagihan.show', $t))->assertOk()->assertSee('Ajukan bukti transfer');
    }

    public function test_lampiran_pembelajaran_tidak_dapat_dijadikan_bukti(): void
    {
        $t = $this->tagihanTerbit();
        \Illuminate\Support\Facades\Schema::create('materi_berkas', function (\Illuminate\Database\Schema\Blueprint $b): void {
            $b->id();
            $b->unsignedBigInteger('berkas_id');
        });
        DB::table('materi_berkas')->insert(['berkas_id' => 11]);
        $this->expectException(ValidationException::class);
        $this->ajukan($t);
    }
    public function test_guard_menolak_bukti_dijadikan_lampiran_pembelajaran(): void
    {
        $this->ajukan($this->tagihanTerbit());
        \Illuminate\Support\Facades\Schema::create('materi_berkas', function (\Illuminate\Database\Schema\Blueprint $b): void {
            $b->id();
            $b->unsignedBigInteger('berkas_id');
        });
        $lampiran = new class extends \Illuminate\Database\Eloquent\Model {
            use \App\Models\Concerns\MenjagaBuktiPembayaranPrivat;
            protected $table = 'materi_berkas';
            protected $guarded = ['*'];
            public $timestamps = false;
        };
        $lampiran->berkas_id = 11;
        $this->expectException(ValidationException::class);
        DB::transaction(fn() => $lampiran->save());
    }
    public function test_guard_tetap_membolehkan_lampiran_biasa(): void
    {
        \Illuminate\Support\Facades\Schema::create('materi_berkas', function (\Illuminate\Database\Schema\Blueprint $b): void {
            $b->id();
            $b->unsignedBigInteger('berkas_id');
        });
        $lampiran = new class extends \Illuminate\Database\Eloquent\Model {
            use \App\Models\Concerns\MenjagaBuktiPembayaranPrivat;
            protected $table = 'materi_berkas';
            protected $guarded = ['*'];
            public $timestamps = false;
        };
        $lampiran->berkas_id = 11;
        DB::transaction(fn() => $lampiran->save());
        $this->assertSame(1, DB::table('materi_berkas')->count());
    }
}
