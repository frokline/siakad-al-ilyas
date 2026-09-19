<?php

namespace Tests\Feature;

use App\Models\Berkas;
use App\Models\Pengumpulan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PengumpulanUnduhTest extends PengumpulanDatabaseTestCase
{
    private function jawaban(): Pengumpulan
    {
        Storage::fake('berkas_local');
        $b = Berkas::findOrFail(11);
        $isi = 'ISI_JAWABAN_UJI';
        Storage::disk('berkas_local')->put($b->object_key, $isi);
        DB::table('berkas')->where('id', 11)->update(['ukuran_byte' => strlen($isi), 'sha256' => hash('sha256', $isi)]);
        return $this->kirim($this->isi($this->draf($this->kegiatan())));
    }
    private function tautan(Pengumpulan $p, int $userId = 3): string
    {
        $this->actingAs(User::findOrFail($userId), 'web');
        return $this->post(route('pengumpulan.tautan', ['pengumpulan' => $p->id, 'lampiran' => $p->lampiran()->firstOrFail()->id]))
            ->assertRedirect()->headers->get('Location');
    }
    public function test_pemilik_mendapat_isi_setelah_verifikasi_hash(): void
    {
        $p = $this->jawaban();
        $url = $this->tautan($p);
        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/octet-stream')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame('ISI_JAWABAN_UJI', $response->streamedContent());
    }
    public function test_url_pemilik_tidak_dapat_dipakai_dosen_meski_berhak_membaca(): void
    {
        $p = $this->jawaban();
        $url = $this->tautan($p);
        $this->actingAs(User::findOrFail(2), 'web')->get($url)->assertForbidden();
        $urlDosen = $this->tautan($p, 2);
        $this->get($urlDosen)->assertOk();
    }
    public function test_mahasiswa_sekelas_tidak_bisa_mengunduh_jawaban_orang_lain(): void
    {
        $p = $this->jawaban();
        $url = $this->tautan($p);
        $this->actingAs(User::findOrFail(6), 'web')->get($url)->assertForbidden();
    }
    public function test_pencabutan_penugasan_membatalkan_akses_url_yang_sudah_dibuat(): void
    {
        $p = $this->jawaban();
        $url = $this->tautan($p, 2);
        DB::table('pengajar_kelas')->where('dosen_id', 20)->update(['aktif' => false]);
        $this->get($url)->assertForbidden();
    }
    public function test_tautan_kedaluwarsa_ditolak(): void
    {
        $p = $this->jawaban();
        $url = $this->tautan($p);
        $this->jam('2030-01-10 02:03:00');
        $this->get($url)->assertForbidden();
    }
    public function test_objek_berubah_tidak_dikirim_ke_browser(): void
    {
        $p = $this->jawaban();
        $url = $this->tautan($p);
        $b = Berkas::findOrFail(11);
        Storage::disk('berkas_local')->put($b->object_key, str_repeat('X', $b->ukuran_byte));
        $this->get($url)->assertStatus(503);
    }
    public function test_lampiran_dari_versi_lain_ditolak_meski_url_bertanda_tangan(): void
    {
        $p = $this->jawaban();
        $baru = $this->isi($this->draf($p->kegiatan, 1));
        $lampiran = $baru->lampiran()->firstOrFail();
        $this->actingAs(User::findOrFail(3), 'web');
        $url = URL::temporarySignedRoute('pengumpulan.unduh', now()->addMinute(), [
            'pengumpulan' => $p->id,
            'lampiran' => $lampiran->id,
            'pemohon' => 3,
            'versi_pengumpulan' => $p->revisi,
            'versi_berkas' => 1,
        ]);
        $this->get($url)->assertNotFound();
    }
}
