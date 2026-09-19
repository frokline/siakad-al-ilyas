<?php

namespace Tests\Feature;

use App\Actions\KelolaJenisBiaya;
use App\Models\JenisBiaya;
use App\Models\User;
use App\Services\AksesKeuangan;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JenisBiayaAksesTest extends JenisBiayaDatabaseTestCase
{
    public function test_pemisahan_izin_baca_dan_kelola(): void
    {
        $a = app(AksesKeuangan::class);
        $this->assertTrue($a->lihatMaster(User::findOrFail(1)));
        $this->assertTrue($a->kelola(User::findOrFail(1)));
        $this->assertTrue($a->lihatMaster(User::findOrFail(2)));
        $this->assertFalse($a->kelola(User::findOrFail(2)));
        foreach ([3, 4, 5] as $id) {
            $this->assertFalse($a->lihatMaster(User::findOrFail($id)));
            $this->assertFalse($a->kelola(User::findOrFail($id)));
        }
    }
    public function test_admin_akademik_tidak_bisa_menulis_langsung_melalui_layanan(): void
    {
        try {
            app(KelolaJenisBiaya::class)->buat(2, $this->data());
            $this->fail('Penulisan seharusnya ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame(0, JenisBiaya::count());
        }
    }
    public function test_form_berulang_tidak_menduplikasi_jenis_biaya_dan_audit(): void
    {
        $data = $this->data();
        $a = app(KelolaJenisBiaya::class);
        $j = $a->buat(1, $data);
        $ulang = $a->buat(1, $data);
        $this->assertSame($j->id, $ulang->id);
        $this->assertSame(1, JenisBiaya::count());
        $this->assertSame(1, DB::table('audit_log')->count());
    }
    public function test_token_lama_dengan_isi_berbeda_ditolak(): void
    {
        $data = $this->data();
        $a = app(KelolaJenisBiaya::class);
        $a->buat(1, $data);
        $data['nama'] = 'Nama berbeda';
        $this->expectException(ValidationException::class);
        $a->buat(1, $data);
    }
    public function test_kode_nonaktif_tidak_boleh_dipakai_untuk_record_baru(): void
    {
        $this->ubahStatus($this->buat(), false);
        $this->expectException(ValidationException::class);
        app(KelolaJenisBiaya::class)->buat(6, $this->data(['kode' => 'spp']));
    }
    public function test_nama_dapat_diubah_dengan_audit_dan_revisi_baru(): void
    {
        $j = $this->buat();
        $baru = app(KelolaJenisBiaya::class)->ubah(1, $j, [
            'nama' => 'SPP Bulanan Reguler',
            'keterangan' => null,
            'versi' => $j->versiForm(),
            'alasan' => 'Memperjelas nama jenis biaya.'
        ]);
        $this->assertSame('SPP', $baru->kode);
        $this->assertSame(2, $baru->revisi);
        $audit = $baru->audits()->where('versi_entitas', 2)->firstOrFail();
        $this->assertSame('SPP Bulanan', $audit->sebelum['nama']);
        $this->assertSame('SPP Bulanan Reguler', $audit->sesudah['nama']);
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
        app(KelolaJenisBiaya::class)->status(1, $j, true, ['versi' => $versi, 'alasan' => 'Permintaan dari halaman lama.']);
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
        $this->assertSame('SPP', $aktif->kode);
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
        $this->assertFalse(app(AksesKeuangan::class)->kelola($u));
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
        $this->postJson(route('keuangan.jenis-biaya.store'), $this->data(['aktif' => false, 'pembuat_id' => 6]))
            ->assertUnprocessable()->assertJsonValidationErrors(['aktif', 'pembuat_id']);
        $this->assertSame(0, JenisBiaya::count());
    }
    public function test_kegagalan_audit_membatalkan_pembuatan_jenis_biaya(): void
    {
        DB::table('audit_log')->insert([
            'entitas' => 'jenis_biaya',
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
            $this->assertSame(0, JenisBiaya::count());
            $this->assertSame(1, DB::table('audit_log')->count());
        }
    }
    public function test_keterangan_html_ditampilkan_sebagai_teks(): void
    {
        $j = $this->buat(['keterangan' => '<script>alert(1)</script>']);
        $this->actingAs(User::findOrFail(1), 'web')->get(route('keuangan.jenis-biaya.show', $j))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }
    public function test_penyimpanan_tanpa_perubahan_tidak_menambah_audit(): void
    {
        $j = $this->buat();
        $a = app(KelolaJenisBiaya::class);
        $baru = $a->ubah(1, $j, [
            'nama' => $j->nama,
            'keterangan' => $j->keterangan,
            'versi' => $j->versiForm(),
            'alasan' => 'Memastikan data tetap sama.'
        ]);
        $this->assertSame(1, $baru->revisi);
        $this->assertSame(1, $baru->audits()->count());
    }
    public function test_modul_tidak_memerlukan_tabel_tagihan_dan_nominal_ditolak(): void
    {
        $this->assertFalse(Schema::hasTable('tagihan'));
        $j = $this->buat();
        $this->actingAs(User::findOrFail(2), 'web')->get(route('keuangan.jenis-biaya.index'))->assertOk();
        $this->assertFalse(Gate::forUser(User::findOrFail(2))->allows('update', $j));
        $this->actingAs(User::findOrFail(1), 'web')->postJson(
            route('keuangan.jenis-biaya.store'),
            $this->data(['kode' => 'LAIN', 'nominal' => 200000])
        )->assertUnprocessable()->assertJsonValidationErrors('nominal');
    }
}
