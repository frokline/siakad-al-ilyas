<?php

namespace Tests\Feature;

use App\Actions\KelolaNotifikasi;
use App\Models\{Notifikasi, User};
use App\Services\{AksesNotifikasi, SinkronNotifikasi, SumberNotifikasi};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotifikasiAksesTest extends NotifikasiDatabaseTestCase
{
    public function test_pengiriman_ganda_tidak_menduplikasi(): void
    {
        $p = $this->terbit($this->draf());
        $a = app(KelolaNotifikasi::class);
        $this->assertTrue($a->kirim(4, 'pengumuman', $p->id));
        $this->assertFalse($a->kirim(4, 'pengumuman', $p->id));
        $this->assertSame(1, Notifikasi::query()->count());
    }
    public function test_judul_ringkas_tidak_menyalin_isi_sumber(): void
    {
        $p = $this->terbit($this->draf(['judul' => 'Rahasia internal', 'isi' => 'Data sensitif tidak disalin.']));
        $n = $this->notif(4, $p);
        $this->assertSame('Pengumuman tersedia', $n->judul);
        $this->assertStringNotContainsString('Rahasia', $n->toJson());
    }
    public function test_sumber_tidak_dikenal_ditolak(): void
    {
        $this->expectException(\LogicException::class);
        app(KelolaNotifikasi::class)->kirim(4, 'users', 1);
    }
    public function test_sumber_nonaktif_ditolak(): void
    {
        config(['notifikasi.sumber_aktif' => []]);
        $this->expectException(\LogicException::class);
        app(KelolaNotifikasi::class)->kirim(4, 'pengumuman', 1);
    }
    public function test_sumber_tidak_ada_tidak_membuat_notifikasi(): void
    {
        $this->assertFalse(app(KelolaNotifikasi::class)->kirim(4, 'pengumuman', 999));
        $this->assertSame(0, Notifikasi::query()->count());
    }
    public function test_draf_tidak_dikirim(): void
    {
        $p = $this->draf();
        $this->assertFalse(app(KelolaNotifikasi::class)->kirim(1, 'pengumuman', $p->id));
    }
    public function test_di_luar_sasaran_tidak_dikirim(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1)]]));
        $this->assertFalse(app(KelolaNotifikasi::class)->kirim(8, 'pengumuman', $p->id));
    }
    public function test_akun_nonaktif_tidak_dikirim(): void
    {
        $p = $this->terbit($this->draf());
        $this->assertFalse(app(KelolaNotifikasi::class)->kirim(5, 'pengumuman', $p->id));
    }
    public function test_penerima_hanya_melihat_kotaknya_sendiri(): void
    {
        $n = $this->notif();
        $a = app(AksesNotifikasi::class);
        $this->assertTrue($a->lihat(User::findOrFail(4), $n));
        $this->assertFalse($a->lihat(User::findOrFail(8), $n));
        $this->assertFalse($a->lihat(User::findOrFail(1), $n));
    }
    public function test_admin_tidak_boleh_menandai_milik_orang_lain(): void
    {
        $n = $this->notif();
        $this->actingAs(User::findOrFail(1))->post(route('notifikasi.baca', $n))->assertNotFound();
        $this->assertNull($n->fresh()->dibaca_at);
    }
    public function test_get_daftar_tidak_mengubah_status_baca(): void
    {
        $n = $this->notif();
        $this->actingAs(User::findOrFail(4))->get(route('notifikasi.index'))->assertOk();
        $this->assertNull($n->fresh()->dibaca_at);
    }
    public function test_waktu_baca_pertama_tetap_saat_diulang(): void
    {
        $n = $this->notif();
        $a = app(KelolaNotifikasi::class);
        $a->baca(4, $n->id);
        $pertama = $n->fresh()->dibaca_at->format('Y-m-d H:i:s');
        $this->travelTo(now('UTC')->addMinutes(5));
        $a->baca(4, $n->id);
        $this->assertSame($pertama, $n->fresh()->dibaca_at->format('Y-m-d H:i:s'));
    }
    public function test_sinkron_ulang_tidak_mereset_status_baca(): void
    {
        $n = $this->notif();
        app(KelolaNotifikasi::class)->baca(4, $n->id);
        $this->assertFalse(app(KelolaNotifikasi::class)->kirim(4, 'pengumuman', $n->sumber_id));
        $this->assertNotNull($n->fresh()->dibaca_at);
    }
    public function test_buka_memakai_route_internal_dan_menandai_dibaca(): void
    {
        $n = $this->notif();
        $this->actingAs(User::findOrFail(4))->post(route('notifikasi.buka', $n))
            ->assertRedirect(route('pengumuman.show', $n->sumber_id));
        $this->assertNotNull($n->fresh()->dibaca_at);
    }
    public function test_client_tidak_boleh_memilih_url(): void
    {
        $n = $this->notif();
        $this->actingAs(User::findOrFail(4))->post(route('notifikasi.buka', $n), ['url' => 'https://example.org'])
            ->assertSessionHasErrors('url');
        $this->assertNull($n->fresh()->dibaca_at);
    }
    public function test_arsip_menyembunyikan_notifikasi_dan_hitungan(): void
    {
        $p = $this->terbit($this->draf());
        $n = $this->notif(4, $p);
        $this->aksiPengumuman($p, 'arsip');
        $this->assertFalse(app(AksesNotifikasi::class)->lihat(User::findOrFail(4), $n));
        $this->actingAs(User::findOrFail(4))->get(route('notifikasi.index'))->assertOk()->assertViewHas('belum', 0);
        $this->assertSame(1, Notifikasi::query()->count());
    }
    public function test_akses_dicabut_sebelum_buka_ditolak(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1)]]));
        $n = $this->notif(4, $p);
        DB::table('krs')->where('id', 1)->update(['status' => 'draf']);
        $this->actingAs(User::findOrFail(4))->post(route('notifikasi.buka', $n))->assertNotFound();
        $this->assertNull($n->fresh()->dibaca_at);
    }
    public function test_notifikasi_kedaluwarsa_tidak_bisa_dibuka(): void
    {
        $p = $this->terbit($this->draf(['berakhir_lokal' => '2026-09-18T08:01']));
        $n = $this->notif(4, $p);
        $this->travelTo(now('UTC')->addMinutes(2));
        $this->expectException(HttpException::class);
        app(KelolaNotifikasi::class)->baca(4, $n->id, true);
    }
    public function test_baca_halaman_hanya_ids_yang_dikirim(): void
    {
        $a = $this->notif();
        $b = $this->notif();
        $this->assertSame(1, app(KelolaNotifikasi::class)->bacaHalaman(4, ['ids' => [$a->id]]));
        $this->assertNotNull($a->fresh()->dibaca_at);
        $this->assertNull($b->fresh()->dibaca_at);
    }
    public function test_baca_halaman_id_asing_membatalkan_seluruhnya(): void
    {
        $a = $this->notif();
        $asing = $this->notif(8);
        try {
            app(KelolaNotifikasi::class)->bacaHalaman(4, ['ids' => [$a->id, $asing->id]]);
            $this->fail('Harus ditolak.');
        } catch (ValidationException) {
            $this->assertNull($a->fresh()->dibaca_at);
            $this->assertNull($asing->fresh()->dibaca_at);
        }
    }
    public function test_baca_halaman_id_duplikat_ditolak(): void
    {
        $n = $this->notif();
        $this->expectException(ValidationException::class);
        app(KelolaNotifikasi::class)->bacaHalaman(4, ['ids' => [$n->id, $n->id]]);
    }
    public function test_baca_halaman_dibatasi_dua_puluh(): void
    {
        $this->expectException(ValidationException::class);
        app(KelolaNotifikasi::class)->bacaHalaman(4, ['ids' => range(1, 21)]);
    }
    public function test_model_tidak_boleh_mengganti_penerima(): void
    {
        $n = $this->notif();
        $this->expectException(\LogicException::class);
        DB::transaction(function () use ($n): void {
            $n->penerima_id = 8;
            $n->save();
        });
    }
    public function test_model_tidak_boleh_mereset_baca(): void
    {
        $n = $this->notif();
        app(KelolaNotifikasi::class)->baca(4, $n->id);
        $n->refresh();
        $this->expectException(\LogicException::class);
        DB::transaction(function () use ($n): void {
            $n->dibaca_at = null;
            $n->save();
        });
    }
    public function test_sinkronisasi_mengambil_sumber_terbaru_secara_idempoten(): void
    {
        $this->terbit($this->draf());
        $s = app(SinkronNotifikasi::class);
        $sejak = CarbonImmutable::now('UTC')->subDays(30);
        $this->assertSame(1, $s->pengguna(4, $sejak));
        $this->assertSame(0, $s->pengguna(4, $sejak));
    }
    public function test_rentang_sinkron_tidak_mengambil_sumber_lama(): void
    {
        $p = $this->terbit($this->draf());
        DB::table('pengumuman')->where('id', $p->id)->update(['updated_at' => now('UTC')->subDays(40)]);
        $s = app(SinkronNotifikasi::class);
        $this->assertSame(0, $s->pengguna(4, CarbonImmutable::now('UTC')->subDays(30)));
        $this->assertSame(1, $s->pengguna(4, CarbonImmutable::now('UTC')->subDays(60)));
    }
    public function test_command_satu_pengguna_dan_pengulangan(): void
    {
        $this->terbit($this->draf());
        $this->artisan('notifikasi:sinkronkan', ['--user' => 4, '--hari' => 30])->assertSuccessful();
        $this->artisan('notifikasi:sinkronkan', ['--user' => 4, '--hari' => 30])->assertSuccessful();
        $this->assertSame(1, Notifikasi::query()->count());
    }
    public function test_command_hari_tidak_valid(): void
    {
        $this->artisan('notifikasi:sinkronkan', ['--hari' => 0])->assertExitCode(2);
    }
    public function test_semua_sumber_terdaftar_memiliki_query_dan_route_tetap(): void
    {
        $this->sumberLain();
        $s = app(SumberNotifikasi::class);
        $u = User::findOrFail(4);
        foreach (['materi', 'kegiatan', 'tagihan', 'pembayaran', 'surat', 'kalender'] as $jenis) {
            $this->assertTrue($s->query($jenis, $u)->whereKey(1)->exists(), $jenis);
            $this->assertTrue(app(KelolaNotifikasi::class)->kirim(4, $jenis, 1), $jenis);
            $this->assertStringStartsWith('/', $s->tujuan($jenis, 1));
        }
    }
    public function test_keuangan_dan_surat_tidak_dikirim_ke_mahasiswa_lain(): void
    {
        $this->sumberLain();
        foreach (['tagihan', 'pembayaran', 'surat'] as $jenis) {
            $this->assertFalse(app(KelolaNotifikasi::class)->kirim(8, $jenis, 1), $jenis);
        }
    }
    public function test_krs_memakai_versi_dan_hanya_route_admin(): void
    {
        $this->sumberLain();
        DB::table('krs')->where('id', 1)->update(['versi' => 7]);
        $a = app(KelolaNotifikasi::class);
        $this->assertFalse($a->kirim(4, 'krs', 1));
        $this->assertTrue($a->kirim(1, 'krs', 1));
        $n = Notifikasi::query()->where('jenis', 'krs')->firstOrFail();
        $this->assertSame(7, $n->sumber_revisi);
        $this->assertSame('/admin/krs/1', app(SumberNotifikasi::class)->tujuan('krs', 1));
    }
    public function test_revisi_baru_membuat_notifikasi_baru_tanpa_mereset_yang_lama(): void
    {
        $this->sumberLain();
        $a = app(KelolaNotifikasi::class);
        $a->kirim(4, 'pembayaran', 1);
        $lama = Notifikasi::query()->firstOrFail();
        $a->baca(4, $lama->id);
        DB::table('pembayaran')->where('id', 1)->update(['status' => 'diterima', 'revisi' => 2]);
        $this->assertTrue($a->kirim(4, 'pembayaran', 1));
        $this->assertSame(2, Notifikasi::query()->count());
        $this->assertNotNull($lama->fresh()->dibaca_at);
    }
    public function test_semua_jenis_dapat_difilter_di_daftar_tanpa_bocor(): void
    {
        $this->sumberLain();
        $a = app(KelolaNotifikasi::class);
        $a->kirim(4, 'materi', 1);
        $a->kirim(4, 'pembayaran', 1);
        $this->actingAs(User::findOrFail(4))->get(route('notifikasi.index', ['jenis' => 'materi']))->assertOk()
            ->assertSee('Pembaruan materi perkuliahan')->assertDontSee('Pembaruan pengajuan pembayaran');
    }
    public function test_sumber_dihapus_membuat_referensi_tidak_terlihat(): void
    {
        $this->sumberLain();
        app(KelolaNotifikasi::class)->kirim(4, 'materi', 1);
        $n = Notifikasi::query()->firstOrFail();
        DB::table('materi')->where('id', 1)->delete();
        $this->assertFalse(app(AksesNotifikasi::class)->lihat(User::findOrFail(4), $n));
    }
    public function test_unique_database_menolak_peristiwa_ganda(): void
    {
        $n = $this->notif();
        $data = $n->getRawOriginal();
        unset($data['id']);
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('notifikasi')->insert($data);
    }
    public function test_model_menolak_penulisan_tanpa_transaksi(): void
    {
        $n = $this->notif();
        $this->expectException(\LogicException::class);
        $n->dibaca_at = now('UTC');
        $n->save();
    }
    public function test_akun_dinonaktifkan_setelah_menerima_ditolak(): void
    {
        $n = $this->notif();
        DB::table('users')->where('id', 4)->update(['status' => 'nonaktif']);
        $this->assertFalse(app(AksesNotifikasi::class)->lihat(User::findOrFail(4), $n));
        $this->expectException(HttpException::class);
        app(KelolaNotifikasi::class)->baca(4, $n->id);
    }
    public function test_modul_dinonaktifkan_menyembunyikan_notifikasi_lama(): void
    {
        $n = $this->notif();
        config(['notifikasi.sumber_aktif' => []]);
        $this->assertFalse(app(AksesNotifikasi::class)->lihat(User::findOrFail(4), $n));
        $this->assertSame(1, Notifikasi::query()->count());
    }
}
