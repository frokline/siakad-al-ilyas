<?php

namespace Tests\Feature;

use App\Actions\KelolaPengumuman;
use App\Models\Pengumuman;
use App\Models\SasaranPengumuman;
use App\Models\User;
use App\Services\AksesPengumuman;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PengumumanAksesTest extends PengumumanDatabaseTestCase
{
    public function test_draf_tidak_terlihat_pembaca(): void
    {
        $p = $this->draf();
        $this->assertFalse($this->terlihat(4, $p));
        $this->actingAs(User::findOrFail(4))->get(route('pengumuman.show', $p))->assertNotFound();
    }
    public function test_kampus_terbit_terlihat_semua_peran_valid(): void
    {
        $p = $this->terbit($this->draf());
        foreach ([1, 2, 3, 4] as $id) {
            $this->assertTrue($this->terlihat($id, $p));
        }
    }
    public function test_akun_nonaktif_ditolak(): void
    {
        $p = $this->terbit($this->draf());
        $this->assertFalse($this->terlihat(5, $p));
        DB::table('users')->where('id', 4)->update(['status' => 'nonaktif']);
        $this->assertFalse($this->terlihat(4, $p));
    }
    public function test_sasaran_prodi_tidak_bocor_ke_prodi_lain(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('prodi', 1)]]));
        $this->assertTrue($this->terlihat(4, $p));
        $this->assertTrue($this->terlihat(3, $p));
        $this->assertFalse($this->terlihat(8, $p));
        $this->assertFalse($this->terlihat(2, $p));
    }
    public function test_sasaran_kelas_memerlukan_keanggotaan(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1)]]));
        $this->assertTrue($this->terlihat(4, $p));
        $this->assertTrue($this->terlihat(3, $p));
        $this->assertFalse($this->terlihat(8, $p));
    }
    public function test_role_dan_keanggotaan_tidak_dicampur_pada_dua_peran(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1, 4)]]));
        // User 7 mengajar kelas 1 tetapi kuliah di kelas 2.
        $this->assertFalse($this->terlihat(7, $p));
        $this->assertTrue($this->terlihat(4, $p));
    }
    public function test_dua_peran_prodi_juga_terikat_persona(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('prodi', 1, 4)]]));
        $this->assertFalse($this->terlihat(7, $p));
    }
    public function test_role_null_mengizinkan_persona_yang_cocok(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1)]]));
        $this->assertTrue($this->terlihat(7, $p));
    }
    public function test_beberapa_sasaran_menggunakan_or(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1, 4), $this->target('kelas', 2, 4)]]));
        $this->assertTrue($this->terlihat(4, $p));
        $this->assertTrue($this->terlihat(8, $p));
        $this->assertFalse($this->terlihat(3, $p));
    }
    public function test_pencabutan_krs_menghentikan_akses(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1)]]));
        DB::table('krs')->where('id', 1)->update(['status' => 'draf']);
        $this->assertFalse($this->terlihat(4, $p));
    }
    public function test_detail_batal_menghentikan_akses(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1)]]));
        DB::table('detail_krs')->where('id', 1)->update(['status' => 'batal']);
        $this->assertFalse($this->terlihat(4, $p));
    }
    public function test_riwayat_nonaktif_menghentikan_akses_prodi(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('prodi', 1)]]));
        DB::table('riwayat_studi')->where('id', 1)->update(['status' => 'keluar']);
        $this->assertFalse($this->terlihat(4, $p));
    }
    public function test_pencabutan_pengajar_menghentikan_akses_dosen(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1)]]));
        DB::table('pengajar_kelas')->where('id', 1)->update(['aktif' => false]);
        $this->assertFalse($this->terlihat(3, $p));
    }
    public function test_dosen_nonaktif_tidak_membaca_kampus_sebagai_dosen(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kampus', null, 3)]]));
        DB::table('dosen')->where('user_id', 3)->update(['status' => 'nonaktif']);
        $this->assertFalse($this->terlihat(3, $p));
    }
    public function test_dosen_boleh_menerbitkan_kelas_sendiri(): void
    {
        $p = $this->terbit($this->draf(['sasaran' => [$this->target('kelas', 1, 4)]], 3), 3);
        $this->assertSame('terbit', $p->status);
        $this->assertTrue($this->terlihat(4, $p));
    }
    public function test_dosen_tidak_boleh_membuat_kampus(): void
    {
        $this->expectException(HttpException::class);
        $this->draf([], 3);
    }
    public function test_dosen_tidak_boleh_membuat_kelas_lain(): void
    {
        $this->expectException(HttpException::class);
        $this->draf(['sasaran' => [$this->target('kelas', 2)]], 3);
    }
    public function test_pengajar_lain_tidak_mengelola_draf_penulis(): void
    {
        $p = $this->draf(['sasaran' => [$this->target('kelas', 1)]], 3);
        $this->assertFalse(app(AksesPengumuman::class)->mengelola(User::findOrFail(7), $p));
    }
    public function test_peran_dicabut_sebelum_publikasi_ditolak(): void
    {
        $p = $this->draf();
        DB::table('user_roles')->where('user_id', 1)->delete();
        $this->expectException(HttpException::class);
        $this->terbit($p);
    }
    public function test_masa_tayang_berakhir_tepat_pada_batas(): void
    {
        $p = $this->terbit($this->draf(['berakhir_lokal' => '2026-09-18T08:01']));
        $this->assertSame('2026-09-18 00:01:00', $p->berakhir_at->format('Y-m-d H:i:s'));
        $this->travelTo(now('UTC')->addMinute());
        $this->assertFalse($this->terlihat(4, $p));
        $this->assertTrue(app(AksesPengumuman::class)->membaca(User::findOrFail(1), $p));
    }
    public function test_arsip_menghilangkan_bacaan(): void
    {
        $p = $this->aksiPengumuman($this->terbit($this->draf()), 'arsip');
        $this->assertFalse($this->terlihat(4, $p));
        $this->assertSame(3, $p->audits()->count());
    }
    public function test_arsip_tidak_dapat_diterbitkan_ulang(): void
    {
        $p = $this->aksiPengumuman($this->draf(), 'arsip');
        $this->expectException(ValidationException::class);
        $this->terbit($p);
    }
    public function test_token_pengiriman_ulang_tidak_menduplikasi(): void
    {
        $v = $this->inputPengumuman();
        $aksi = app(KelolaPengumuman::class);
        $a = $aksi->buat(1, $v);
        $b = $aksi->buat(1, $v);
        $this->assertSame($a->id, $b->id);
        $this->assertSame(1, $a->audits()->count());
    }
    public function test_token_sama_isi_berbeda_ditolak(): void
    {
        $v = $this->inputPengumuman();
        $aksi = app(KelolaPengumuman::class);
        $aksi->buat(1, $v);
        $v['judul'] = 'Isi sudah berbeda';
        $this->expectException(ValidationException::class);
        $aksi->buat(1, $v);
    }
    public function test_form_lama_ditolak(): void
    {
        $p = $this->draf();
        $versi = $p->versiForm();
        $p = $this->terbit($p);
        $this->expectException(ValidationException::class);
        app(KelolaPengumuman::class)->tindakan(1, $p, ['aksi' => 'arsip', 'versi' => $versi, 'konfirmasi' => 1, 'alasan' => 'Arsip dari formulir lama.']);
    }
    public function test_simpan_tanpa_perubahan_tidak_menambah_audit(): void
    {
        $v = $this->inputPengumuman();
        $aksi = app(KelolaPengumuman::class);
        $p = $aksi->buat(1, $v);
        unset($v['form_token']);
        $v['versi'] = $p->versiForm();
        $hasil = $aksi->ubah(1, $p, $v);
        $this->assertSame(1, $hasil->revisi);
        $this->assertSame(1, $hasil->audits()->count());
    }
    public function test_publikasi_tidak_bisa_diedit(): void
    {
        $p = $this->terbit($this->draf());
        $v = $this->inputPengumuman(['versi' => $p->versiForm()]);
        unset($v['form_token']);
        $this->expectException(ValidationException::class);
        app(KelolaPengumuman::class)->ubah(1, $p, $v);
    }
    public function test_sasaran_publikasi_tidak_bisa_dihapus(): void
    {
        $p = $this->terbit($this->draf());
        $this->expectException(\LogicException::class);
        DB::transaction(fn() => $p->sasaran()->firstOrFail()->delete());
    }
    public function test_duplikasi_sasaran_null_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        $this->draf(['sasaran' => [$this->target(), $this->target()]]);
    }
    public function test_unique_sasaran_database_mencegah_duplikasi_model(): void
    {
        $p = $this->draf();
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::transaction(function () use ($p): void {
            $s = new SasaranPengumuman();
            $s->forceFill($this->target() + ['pengumuman_id' => $p->id])->save();
        });
    }
    public function test_sasaran_induk_tidak_sesuai_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        $this->draf(['sasaran' => [array_replace($this->target(), ['kelas_kuliah_id' => 1])]]);
    }
    public function test_periode_arsip_mencegah_publikasi_kelas(): void
    {
        $p = $this->draf(['sasaran' => [$this->target('kelas', 1)]]);
        DB::table('periode_akademik')->where('id', 1)->update(['status' => 'arsip']);
        $this->expectException(ValidationException::class);
        $this->terbit($p);
    }
    public function test_gagal_audit_membatalkan_publikasi(): void
    {
        $p = $this->draf();
        DB::table('audit_log')->insert([
            'pelaku_id' => 1,
            'entitas' => 'pengumuman',
            'entitas_id' => $p->id,
            'versi_entitas' => 2,
            'aksi' => 'fixture',
            'sesudah' => '{}',
            'waktu' => now('UTC')
        ]);
        try {
            $this->terbit($p);
            $this->fail('Audit unik seharusnya gagal.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertSame('draf', $p->fresh()->status);
            $this->assertSame(1, $p->fresh()->revisi);
        }
    }
    public function test_http_meng_escape_isi_dan_menyembunyikan_audit_pembaca(): void
    {
        $p = $this->terbit($this->draf(['isi' => '<script>alert("x")</script>']));
        $this->actingAs(User::findOrFail(4))->get(route('pengumuman.show', $p))->assertOk()
            ->assertSee('&lt;script&gt;', false)->assertDontSee('<script>', false)->assertDontSee('Audit pengelola');
    }
    public function test_http_daftar_tidak_membocorkan_judul_di_luar_sasaran(): void
    {
        $this->terbit($this->draf(['judul' => 'Rahasia kelas kedua', 'sasaran' => [$this->target('kelas', 2)]]));
        $this->actingAs(User::findOrFail(4))->get(route('pengumuman.index'))->assertOk()->assertDontSee('Rahasia kelas kedua');
    }
    public function test_http_form_admin_dapat_dirender(): void
    {
        $this->actingAs(User::findOrFail(1))->get(route('pengumuman.create'))->assertOk()->assertSee('Simpan draf');
    }
    public function test_http_mahasiswa_tidak_boleh_menulis(): void
    {
        $this->actingAs(User::findOrFail(4))->post(route('pengumuman.store'), $this->inputPengumuman())->assertForbidden();
        $this->assertSame(0, Pengumuman::query()->count());
    }
}
