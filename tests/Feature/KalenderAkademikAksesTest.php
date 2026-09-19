<?php

namespace Tests\Feature;

use App\Actions\KelolaKalenderAkademik;
use App\Models\KalenderAkademik;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class KalenderAkademikAksesTest extends KalenderDatabaseTestCase
{
    public function test_buat_draf_dengan_audit_dan_waktu_utc(): void
    {
        $k = $this->buatAgenda();
        $this->assertSame('draf', $k->status);
        $this->assertSame(1, $k->audits()->count());
        $this->assertSame('2026-09-20 00:00:00', $k->mulai_at->format('Y-m-d H:i:s'));
        $this->assertNull($k->diterbitkan_at);
    }
    public function test_pengiriman_ulang_tidak_menduplikasi(): void
    {
        $a = app(KelolaKalenderAkademik::class);
        $v = $this->dataAgenda();
        $k = $a->buat(1, $v);
        $this->assertSame($k->id, $a->buat(1, $v)->id);
        $this->assertSame(1, KalenderAkademik::count());
    }
    public function test_token_sama_isi_berbeda_ditolak(): void
    {
        $a = app(KelolaKalenderAkademik::class);
        $v = $this->dataAgenda();
        $a->buat(1, $v);
        $v['judul'] = 'Judul berbeda';
        $this->expectException(ValidationException::class);
        $a->buat(1, $v);
    }
    public function test_keuangan_dosen_mahasiswa_tidak_boleh_menulis(): void
    {
        foreach ([2, 3, 4, 5] as $id) {
            try {
                app(KelolaKalenderAkademik::class)->buat($id, $this->dataAgenda());
                $this->fail('Harus ditolak.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
        $this->assertSame(0, KalenderAkademik::count());
    }
    public function test_draf_tidak_bocor_di_daftar_dan_url_langsung(): void
    {
        $k = $this->buatAgenda();
        $this->actingAs(User::findOrFail(4), 'web');
        $this->get(route('kalender.index', ['bulan' => '2026-09', 'tampilan' => 'daftar']))->assertOk()->assertDontSee($k->judul);
        $this->get(route('kalender.show', $k))->assertForbidden();
    }
    public function test_publikasi_terlihat_semua_peran_kampus_aktif(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(['program_studi_id' => 1]), 'terbitkan');
        foreach ([1, 2, 3, 4] as $id) {
            $this->actingAs(User::findOrFail($id), 'web')->get(route('kalender.show', $k))->assertOk();
        }
        $this->assertSame(2, $k->audits()->count());
    }
    public function test_akun_nonaktif_ditolak_membaca(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(), 'terbitkan');
        $this->actingAs(User::findOrFail(5), 'web')->get(route('kalender.show', $k))->assertForbidden();
    }
    public function test_pembatalan_agenda_terbit_tetap_terlihat(): void
    {
        $k = $this->tindakanAgenda($this->tindakanAgenda($this->buatAgenda(), 'terbitkan'), 'batalkan');
        $this->assertNotNull($k->diterbitkan_at);
        $this->assertNotNull($k->dibatalkan_at);
        $this->actingAs(User::findOrFail(4), 'web')->get(route('kalender.show', $k))->assertOk()->assertSee('Agenda dibatalkan.');
    }
    public function test_pembatalan_draf_tetap_tersembunyi(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(), 'batalkan');
        $this->assertNull($k->diterbitkan_at);
        $this->actingAs(User::findOrFail(4), 'web')->get(route('kalender.show', $k))->assertForbidden();
    }
    public function test_agenda_batal_tidak_dapat_diedit(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(), 'batalkan');
        $this->expectException(ValidationException::class);
        app(KelolaKalenderAkademik::class)->ubah(1, $k, $this->dataEdit($k, ['judul' => 'Penggantian nama']));
    }
    public function test_koreksi_agenda_terbit_menaikkan_revisi_dan_audit(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(), 'terbitkan');
        $awalTerbit = $k->diterbitkan_at->toISOString();
        $k = app(KelolaKalenderAkademik::class)->ubah(1, $k, $this->dataEdit($k, ['judul' => 'Judul yang diperbaiki']));
        $this->assertSame(3, $k->revisi);
        $this->assertSame(3, $k->audits()->count());
        $this->assertSame($awalTerbit, $k->diterbitkan_at->toISOString());
    }
    public function test_periode_dan_sasaran_tetap_setelah_publikasi(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(), 'terbitkan');
        $this->expectException(ValidationException::class);
        app(KelolaKalenderAkademik::class)->ubah(1, $k, $this->dataEdit($k, ['program_studi_id' => 1]));
    }
    public function test_periode_arsip_menghalangi_penerbitan(): void
    {
        $k = $this->buatAgenda();
        DB::table('periode_akademik')->where('id', 1)->update(['status' => 'arsip']);
        $this->expectException(ValidationException::class);
        $this->tindakanAgenda($k, 'terbitkan');
    }
    public function test_periode_arsip_tetap_boleh_pembatalan(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(), 'terbitkan');
        DB::table('periode_akademik')->where('id', 1)->update(['status' => 'arsip']);
        $this->assertSame('batal', $this->tindakanAgenda($k, 'batalkan')->status);
    }
    public function test_prodi_nonaktif_ditolak(): void
    {
        DB::table('program_studi')->where('id', 1)->update(['aktif' => false]);
        $this->expectException(ValidationException::class);
        $this->buatAgenda(['program_studi_id' => 1]);
    }
    public function test_form_lama_tidak_menimpa_perubahan(): void
    {
        $k = $this->buatAgenda();
        $v = $this->dataEdit($k);
        $this->tindakanAgenda($k, 'terbitkan');
        $this->expectException(ValidationException::class);
        app(KelolaKalenderAkademik::class)->ubah(1, $k, $v);
    }
    public function test_penyimpanan_tanpa_perubahan_tidak_menambah_audit(): void
    {
        $k = $this->buatAgenda();
        $k = app(KelolaKalenderAkademik::class)->ubah(1, $k, $this->dataEdit($k));
        $this->assertSame(1, $k->revisi);
        $this->assertSame(1, $k->audits()->count());
    }
    public function test_peran_dicabut_diperiksa_ulang(): void
    {
        $k = $this->buatAgenda();
        DB::table('user_roles')->where('user_id', 1)->delete();
        try {
            $this->tindakanAgenda($k, 'terbitkan');
            $this->fail('Harus ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('draf', $k->fresh()->status);
        }
    }
    public function test_audit_gagal_membatalkan_perubahan(): void
    {
        $k = $this->buatAgenda();
        DB::table('audit_log')->insert(['entitas' => 'kalender_akademik', 'entitas_id' => $k->id, 'versi_entitas' => 2, 'aksi' => 'fixture', 'sesudah' => '{}', 'waktu' => now('UTC')]);
        try {
            $this->tindakanAgenda($k, 'terbitkan');
            $this->fail('Audit harus gagal.');
        } catch (QueryException) {
            $this->assertSame('draf', $k->fresh()->status);
            $this->assertSame(1, $k->fresh()->revisi);
        }
    }
    public function test_agenda_krs_tidak_mengubah_batas_krs_periode(): void
    {
        $awal = (array) DB::table('periode_akademik')->where('id', 1)->first();
        $k = $this->tindakanAgenda($this->buatAgenda(['jenis' => 'krs', 'mulai_lokal' => '2026-08-01T08:00', 'selesai_lokal' => '2026-08-20T23:00']), 'terbitkan');
        $this->assertSame($awal, (array) DB::table('periode_akademik')->where('id', 1)->first());
        $this->assertSame('terbit', $k->status);
    }
    public function test_filter_prodi_menyertakan_agenda_kampus(): void
    {
        foreach ([[null, 'Agenda kampus'], [1, 'Agenda prodi satu'], [2, 'Agenda prodi dua']] as [$id, $judul]) {
            $this->tindakanAgenda($this->buatAgenda(['judul' => $judul, 'program_studi_id' => $id]), 'terbitkan');
        }
        $this->actingAs(User::findOrFail(4), 'web')->get(route('kalender.index', ['bulan' => '2026-09', 'tampilan' => 'daftar', 'program_studi_id' => 1]))
            ->assertOk()->assertSee('Agenda kampus')->assertSee('Agenda prodi satu')->assertDontSee('Agenda prodi dua');
    }
    public function test_agenda_lintas_bulan_muncul_di_bulan_berikutnya(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(['mulai_lokal' => '2026-08-31T23:00', 'selesai_lokal' => '2026-09-01T01:00']), 'terbitkan');
        $this->actingAs(User::findOrFail(4), 'web')->get(route('kalender.index', ['bulan' => '2026-09', 'tampilan' => 'daftar']))->assertOk()->assertSee($k->judul);
    }
    public function test_akhir_tepat_awal_bulan_tidak_muncul_di_bulan_baru(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(['mulai_lokal' => '2026-08-31T23:00', 'selesai_lokal' => '2026-09-01T00:00']), 'terbitkan');
        $this->actingAs(User::findOrFail(4), 'web')->get(route('kalender.index', ['bulan' => '2026-09', 'tampilan' => 'daftar']))->assertOk()->assertDontSee($k->judul);
    }
    public function test_hard_delete_dilarang(): void
    {
        $k = $this->buatAgenda();
        $this->expectException(LogicException::class);
        $k->delete();
    }
    public function test_keterangan_html_di_escape_dan_audit_tidak_terlihat_mahasiswa(): void
    {
        $k = $this->tindakanAgenda($this->buatAgenda(['keterangan' => '<script>alert(1)</script>']), 'terbitkan');
        $this->actingAs(User::findOrFail(4), 'web')->get(route('kalender.show', $k))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('Audit admin');
    }
    public function test_form_dan_grid_dapat_dirender(): void
    {
        $k = $this->buatAgenda();
        $this->actingAs(User::findOrFail(1), 'web');
        $this->get(route('kalender.create'))->assertOk();
        $this->get(route('kalender.edit', $k))->assertOk();
        $this->get(route('kalender.index', ['bulan' => '2026-09']))->assertOk()->assertSee('Senin')->assertSee($k->judul);
    }
    public function test_tindakan_tanpa_konfirmasi_ditolak(): void
    {
        $k = $this->buatAgenda();
        $this->actingAs(User::findOrFail(1), 'web')->postJson(
            route('kalender.tindakan', $k),
            ['aksi' => 'terbitkan', 'versi' => $k->versiForm(), 'alasan' => 'Menerbitkan agenda kampus.']
        )->assertUnprocessable()->assertJsonValidationErrors('konfirmasi');
        $this->assertSame('draf', $k->fresh()->status);
    }
}
