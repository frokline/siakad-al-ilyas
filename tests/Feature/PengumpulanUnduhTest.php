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
    private function jawaban(
        int $userId = 3,
        int $berkasId = 11
    ): Pengumpulan {
        Storage::fake('berkas_local');

        $berkas = Berkas::findOrFail($berkasId);
        $isi = 'ISI_JAWABAN_UJI_' . $userId;

        Storage::disk('berkas_local')->put(
            $berkas->object_key,
            $isi
        );

        DB::table('berkas')
            ->where('id', $berkasId)
            ->update([
                'ukuran_byte' => strlen($isi),
                'sha256' => hash('sha256', $isi),
            ]);

        return $this->jawab(
            $this->kegiatan(),
            null,
            [(string) $berkasId],
            null,
            $userId
        );
    }

    private function tautan(
        Pengumpulan $pengumpulan,
        int $userId = 3
    ): string {
        $this->actingAs(
            User::findOrFail($userId),
            'web'
        );

        return $this->post(
            route(
                'pengumpulan.tautan',
                [
                    'pengumpulan' =>
                        $pengumpulan->id,

                    'lampiran' =>
                        $pengumpulan
                            ->lampiran()
                            ->firstOrFail()
                            ->id,
                ]
            )
        )
            ->assertRedirect()
            ->headers
            ->get('Location');
    }

    public function test_pemilik_dapat_mengunduh_jawaban(): void
    {
        $pengumpulan = $this->jawaban();
        $url = $this->tautan($pengumpulan);

        $response = $this->get($url)
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/octet-stream'
            )
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff'
            );

        $this->assertSame(
            'ISI_JAWABAN_UJI_3',
            $response->streamedContent()
        );
    }

    public function test_tautan_pemilik_tidak_dapat_dipakai_dosen(): void
    {
        $pengumpulan = $this->jawaban();
        $urlPemilik = $this->tautan($pengumpulan);

        $this->actingAs(
            User::findOrFail(2),
            'web'
        )
            ->get($urlPemilik)
            ->assertForbidden();

        $urlDosen = $this->tautan(
            $pengumpulan,
            2
        );

        $this->get($urlDosen)->assertOk();
    }

    public function test_mahasiswa_lain_tidak_bisa_mengunduh(): void
    {
        $pengumpulan = $this->jawaban();
        $url = $this->tautan($pengumpulan);

        $this->actingAs(
            User::findOrFail(6),
            'web'
        )
            ->get($url)
            ->assertForbidden();
    }

    public function test_penugasan_dosen_dicabut_memblokir_tautan(): void
    {
        $pengumpulan = $this->jawaban();

        $url = $this->tautan(
            $pengumpulan,
            2
        );

        DB::table('pengajar_kelas')
            ->where('dosen_id', 20)
            ->update(['aktif' => false]);

        $this->get($url)->assertForbidden();
    }

    public function test_tautan_kedaluwarsa_ditolak(): void
    {
        $pengumpulan = $this->jawaban();
        $url = $this->tautan($pengumpulan);

        $this->jam('2030-01-10 02:03:00');

        $this->get($url)->assertForbidden();
    }

    public function test_objek_yang_berubah_tidak_dikirim(): void
    {
        $pengumpulan = $this->jawaban();
        $url = $this->tautan($pengumpulan);

        $berkas = Berkas::findOrFail(11);

        Storage::disk('berkas_local')->put(
            $berkas->object_key,
            str_repeat('X', $berkas->ukuran_byte)
        );

        $this->get($url)->assertStatus(503);
    }

    public function test_lampiran_pengumpulan_lain_ditolak(): void
    {
        Storage::fake('berkas_local');

        $kegiatan = $this->kegiatan();

        foreach (
            [
                11 => [3, 'ISI_A'],
                12 => [6, 'ISI_B'],
            ] as $berkasId => [$userId, $isi]
        ) {
            $berkas = Berkas::findOrFail($berkasId);

            Storage::disk('berkas_local')->put(
                $berkas->object_key,
                $isi
            );

            DB::table('berkas')
                ->where('id', $berkasId)
                ->update([
                    'ukuran_byte' => strlen($isi),
                    'sha256' => hash('sha256', $isi),
                ]);
        }

        $milikA = $this->jawab(
            $kegiatan,
            null,
            ['11'],
            null,
            3
        );

        $milikB = $this->jawab(
            $kegiatan,
            null,
            ['12'],
            null,
            6
        );

        $lampiranB = $milikB
            ->lampiran()
            ->firstOrFail();

        $berkasB = Berkas::findOrFail(
            $lampiranB->berkas_id
        );

        $this->actingAs(
            User::findOrFail(3),
            'web'
        );

        $url = URL::temporarySignedRoute(
            'pengumpulan.unduh',
            now()->addMinute(),
            [
                'pengumpulan' => $milikA->id,
                'lampiran' => $lampiranB->id,
                'pemohon' => 3,
                'versi_pengumpulan' =>
                    $milikA->revisi,
                'versi_berkas' =>
                    $berkasB->revisi,
            ]
        );

        $this->get($url)->assertNotFound();
    }
}