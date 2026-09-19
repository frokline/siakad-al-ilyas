<?php

namespace Tests\Feature;

use App\Actions\KelolaJenisSurat;
use App\Models\JenisSurat;
use App\Models\User;
use App\Services\AksesJenisSurat;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JenisSuratAksesTest extends JenisSuratDatabaseTestCase
{
    public function test_hanya_admin_akademik_aktif_dapat_mengelola_katalog(): void
    {
        $a = app(AksesJenisSurat::class);
        $this->assertTrue($a->lihatMaster(User::findOrFail(1)));
        $this->assertTrue($a->kelola(User::findOrFail(1)));
        $this->assertFalse($a->lihatMaster(User::findOrFail(2)));
        $this->assertFalse($a->kelola(User::findOrFail(2)));
        foreach ([3, 4, 5] as $id) {
            $this->assertFalse($a->lihatMaster(User::findOrFail($id)));
            $this->assertFalse($a->kelola(User::findOrFail($id)));
        }
    }
    public function test_admin_keuangan_tidak_bisa_menulis_langsung_melalui_layanan(): void
    {
        try {
            app(KelolaJenisSurat::class)->buat(2, $this->data());
            $this->fail('Penulisan seharusnya ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame(0, JenisSurat::count());
        }
    }
    public function test_form_berulang_tidak_menduplikasi_jenis_surat_dan_audit(): void
    {
        $data = $this->data();
        $a = app(KelolaJenisSurat::class);
        $j = $a->buat(1, $data);
        $ulang = $a->buat(1, $data);
        $this->assertSame($j->id, $ulang->id);
        $this->assertSame(1, JenisSurat::count());
        $this->assertSame(1, DB::table('audit_log')->count());
    }
    public function test_token_lama_dengan_isi_berbeda_ditolak(): void
    {
        $data = $this->data();
        $a = app(KelolaJenisSurat::class);
        $a->buat(1, $data);
        $data['nama'] = 'Nama berbeda';
        $this->expectException(ValidationException::class);
        $a->buat(1, $data);
    }
    public function test_kode_nonaktif_tidak_boleh_dipakai_untuk_record_baru(): void
    {
        $this->ubahStatus($this->buat(), false);
        $this->expectException(ValidationException::class);
        app(KelolaJenisSurat::class)->buat(6, $this->data(['kode' => 'aktif_kuliah']));
    }
    public function test_nama_dapat_diubah_dengan_audit_dan_revisi_baru(): void
    {
        $j = $this->buat();
        $baru = app(KelolaJenisSurat::class)->ubah(1, $j, [
            'nama' => 'Surat Aktif Kuliah Reguler',
            'syarat' => null,
            'versi' => $j->versiForm(),
            'alasan' => 'Memperjelas nama jenis surat.'
        ]);
        $this->assertSame('AKTIF_KULIAH', $baru->kode);
        $this->assertSame(2, $baru->revisi);
        $audit = $baru->audits()->where('versi_entitas', 2)->firstOrFail();
        $this->assertSame('Surat Aktif Kuliah', $audit->sebelum['nama']);
        $this->assertSame('Surat Aktif Kuliah Reguler', $audit->sesudah['nama']);
    }
    public function test_kode_tidak_dapat_diubah_lewat_model(): void
    {
        $j = $this->buat();
        $this->expectException(LogicException::class);
        DB::transaction(function () use ($j): void {
            $j->kode = 'BARU';
            $j->revisi++;
            $j->save();
        });
    }
    public function test_form_lama_tidak_menimpa_revisi_baru(): void
    {
        $j = $this->buat();
        $versi = $j->versiForm();
        $this->ubahStatus($j, false);
        $this->expectException(ValidationException::class);
        app(KelolaJenisSurat::class)->status(1, $j, true, ['versi' => $versi, 'alasan' => 'Permintaan dari halaman lama.', 'konfirmasi' => '1']);
    }
    public function test_nonaktif_dan_aktif_kembali_mempertahankan_identitas(): void
    {
        $j = $this->buat();
        $mati = $this->ubahStatus($j, false);
        $this->assertFalse($mati->aktif);
        $this->assertNotNull($mati->dinonaktifkan_at);
        $aktif = $this->ubahStatus($mati, true);
        $this->assertTrue($aktif->aktif);
        $this->assertNull($aktif->dinonaktifkan_at);
        $this->assertSame($j->id, $aktif->id);
        $this->assertSame('AKTIF_KULIAH', $aktif->kode);
        $this->assertSame(3, $aktif->audits()->count());
    }
    public function test_hard_delete_diblokir(): void
    {
        $j = $this->buat();
        $this->expectException(LogicException::class);
        $j->delete();
    }
    public function test_peran_yang_dicabut_dibaca_ulang_meski_model_sudah_dimiliki(): void
    {
        $u = User::findOrFail(1)->load('roles');
        $j = $this->buat();
        DB::table('user_roles')->where('user_id', 1)->update(['role_id' => 2]);
        $this->assertFalse(app(AksesJenisSurat::class)->kelola($u));
        try {
            $this->ubahStatus($j, false);
            $this->fail('Harus ditolak setelah peran dicabut.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertTrue($j->fresh()->aktif);
        }
    }
    public function test_input_status_dan_pembuat_palsu_ditolak(): void
    {
        $this->actingAs(User::findOrFail(1), 'web');
        $this->postJson(route('admin.jenis-surat.store'), $this->data(['aktif' => false, 'pembuat_id' => 6]))
            ->assertUnprocessable()->assertJsonValidationErrors(['aktif', 'pembuat_id']);
        $this->assertSame(0, JenisSurat::count());
    }
    public function test_kegagalan_audit_membatalkan_pembuatan_jenis_surat(): void
    {
        DB::table('audit_log')->insert([
            'entitas' => 'jenis_surat',
            'entitas_id' => 1,
            'versi_entitas' => 1,
            'aksi' => 'fixture',
            'sesudah' => '{}',
            'waktu' => now('UTC')
        ]);
        try {
            $this->buat();
            $this->fail('Benturan audit seharusnya menggagalkan transaksi.');
        } catch (QueryException) {
            $this->assertSame(0, JenisSurat::count());
            $this->assertSame(1, DB::table('audit_log')->count());
        }
    }
    public function test_syarat_html_ditampilkan_sebagai_teks(): void
    {
        $j = $this->buat(['syarat' => '<script>alert(1)</script>']);
        $this->actingAs(User::findOrFail(1), 'web')->get(route('admin.jenis-surat.show', $j))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }
    public function test_penyimpanan_tanpa_perubahan_tidak_menambah_audit(): void
    {
        $j = $this->buat();
        $a = app(KelolaJenisSurat::class);
        $baru = $a->ubah(1, $j, [
            'nama' => $j->nama,
            'syarat' => $j->syarat,
            'versi' => $j->versiForm(),
            'alasan' => 'Memastikan data tetap sama.'
        ]);
        $this->assertSame(1, $baru->revisi);
        $this->assertSame(1, $baru->audits()->count());
    }
    public function test_katalog_berjalan_sebelum_modul_permohonan(): void
    {
        $this->assertFalse(Schema::hasTable('permohonan_surat'));
        $j = $this->buat();
        $this->actingAs(User::findOrFail(1), 'web')->get(route('admin.jenis-surat.index'))->assertOk();
        $this->actingAs(User::findOrFail(2), 'web')->get(route('admin.jenis-surat.index'))->assertForbidden();
        $this->assertFalse(Gate::forUser(User::findOrFail(2))->allows('update', $j));
    }

    public function test_perubahan_status_memerlukan_konfirmasi(): void
    {
        $j = $this->buat();
        $this->actingAs(User::findOrFail(1), 'web')->postJson(
            route('admin.jenis-surat.nonaktifkan', $j),
            ['versi' => $j->versiForm(), 'alasan' => 'Penutupan sementara layanan.']
        )
            ->assertUnprocessable()->assertJsonValidationErrors('konfirmasi');
        $this->assertTrue($j->fresh()->aktif);
    }
    public function test_jenis_nonaktif_tidak_dapat_diedit(): void
    {
        $j = $this->ubahStatus($this->buat(), false);
        try {
            app(KelolaJenisSurat::class)->ubah(1, $j, [
                'nama' => 'Nama berubah',
                'syarat' => null,
                'versi' => $j->versiForm(),
                'alasan' => 'Mengubah layanan yang nonaktif.'
            ]);
            $this->fail('Edit harus ditolak.');
        } catch (ValidationException) {
            $this->assertSame('Surat Aktif Kuliah', $j->fresh()->nama);
            $this->assertSame(2, $j->audits()->count());
        }
    }
    public function test_akun_nonaktif_ditolak_di_layanan(): void
    {
        try {
            app(KelolaJenisSurat::class)->buat(5, $this->data());
            $this->fail('Harus ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame(0, JenisSurat::count());
        }
    }
    public function test_form_tambah_dan_edit_dapat_dirender(): void
    {
        $j = $this->buat();
        $this->actingAs(User::findOrFail(1), 'web');
        $this->get(route('admin.jenis-surat.create'))->assertOk()->assertSee('form_token');
        $this->get(route('admin.jenis-surat.edit', $j))->assertOk()->assertSee('versi');
    }
    public function test_akun_tanpa_izin_tidak_dapat_membuka_katalog(): void
    {
        foreach ([2, 3, 4, 5] as $id) {
            $this->actingAs(User::findOrFail($id), 'web')->get(route('admin.jenis-surat.index'))->assertForbidden();
        }
    }
}
