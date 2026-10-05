<?php

namespace Tests\Feature;

use App\Actions\ProsesVerifikasiPembayaran;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\VerifikasiPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifikasiPembayaranAksesTest extends PembayaranDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $migration = glob(
            database_path(
                'migrations/*_create_verifikasi_pembayaran_table.php'
            )
        );

        $this->assertCount(
            1,
            $migration,
            'Harus ada tepat satu migration verifikasi pembayaran.'
        );

        (require $migration[0])->up();
    }

    private function dataVerifikasi(
        Pembayaran $pembayaran,
        array $ubah = []
    ): array {
        return array_replace([
            'versi' => $pembayaran->versiForm(),
            'catatan' => 'Bukti transfer sudah diperiksa dengan teliti.',
            'konfirmasi' => '1',
        ], $ubah);
    }

    public function test_admin_keuangan_dapat_menerima_pembayaran(): void
    {
        $pembayaran = $this->ajukan(
            $this->tagihanTerbit()
        );

        $hasil = app(ProsesVerifikasiPembayaran::class)->terima(
            1,
            $pembayaran,
            $this->dataVerifikasi($pembayaran)
        );

        $this->assertSame(
            Pembayaran::DITERIMA,
            $hasil->status
        );

        $this->assertSame(
            (int) $hasil->tagihan_id,
            $hasil->tagihan_aktif_id
        );

        $this->assertSame(
            $hasil->bukti_sha256,
            $hasil->bukti_aktif_sha256
        );

        $this->assertSame(2, $hasil->revisi);
        $this->assertSame(1, $hasil->verifikasi()->count());
        $this->assertSame(2, $hasil->audits()->count());

        $verifikasi = $hasil->verifikasi()->firstOrFail();

        $this->assertSame(
            VerifikasiPembayaran::TERIMA,
            $verifikasi->tindakan
        );

        $this->assertSame(
            Pembayaran::MENUNGGU,
            $verifikasi->status_sebelum
        );

        $this->assertSame(
            Pembayaran::DITERIMA,
            $verifikasi->status_sesudah
        );

        $this->assertSame(
            1,
            $verifikasi->petugas_id
        );

        $this->assertSame(
            2,
            $verifikasi->revisi_pembayaran
        );
    }

    public function test_penolakan_melepaskan_slot_pengajuan(): void
    {
        $tagihan = $this->tagihanTerbit();
        $pembayaran = $this->ajukan($tagihan);

        $hasil = app(ProsesVerifikasiPembayaran::class)->tolak(
            1,
            $pembayaran,
            $this->dataVerifikasi($pembayaran, [
                'catatan' => 'Nominal pada bukti transfer tidak terbaca jelas.',
            ])
        );

        $this->assertSame(
            Pembayaran::DITOLAK,
            $hasil->status
        );

        $this->assertNull($hasil->tagihan_aktif_id);
        $this->assertNull($hasil->bukti_aktif_sha256);
        $this->assertSame(2, $hasil->revisi);

        $pengajuanBaru = $this->ajukan($tagihan);

        $this->assertNotSame(
            $hasil->id,
            $pengajuanBaru->id
        );

        $this->assertSame(
            Pembayaran::MENUNGGU,
            $pengajuanBaru->status
        );
    }

    public function test_admin_akademik_tidak_dapat_memverifikasi(): void
    {
        $pembayaran = $this->ajukan(
            $this->tagihanTerbit()
        );

        try {
            app(ProsesVerifikasiPembayaran::class)->terima(
                2,
                $pembayaran,
                $this->dataVerifikasi($pembayaran)
            );

            $this->fail(
                'Admin Akademik seharusnya tidak dapat memverifikasi pembayaran.'
            );
        } catch (HttpException $exception) {
            $this->assertSame(
                403,
                $exception->getStatusCode()
            );
        }

        $this->assertSame(
            Pembayaran::MENUNGGU,
            $pembayaran->fresh()->status
        );

        $this->assertSame(
            0,
            VerifikasiPembayaran::query()->count()
        );
    }

    public function test_mahasiswa_tidak_dapat_memverifikasi_milik_sendiri(): void
    {
        $pembayaran = $this->ajukan(
            $this->tagihanTerbit()
        );

        $this->actingAs(
            User::findOrFail(4),
            'web'
        )->post(
            route('pembayaran.terima', $pembayaran),
            $this->dataVerifikasi($pembayaran)
        )->assertForbidden();

        $this->assertSame(
            Pembayaran::MENUNGGU,
            $pembayaran->fresh()->status
        );
    }

    public function test_form_lama_ditolak_setelah_pembayaran_berubah(): void
    {
        $pembayaran = $this->ajukan(
            $this->tagihanTerbit()
        );

        $dataLama = $this->dataVerifikasi($pembayaran);

        app(ProsesVerifikasiPembayaran::class)->terima(
            1,
            $pembayaran,
            $dataLama
        );

        $this->expectException(
            ValidationException::class
        );

        app(ProsesVerifikasiPembayaran::class)->tolak(
            1,
            $pembayaran,
            $dataLama
        );
    }

    public function test_http_admin_keuangan_dapat_memverifikasi(): void
    {
        $pembayaran = $this->ajukan(
            $this->tagihanTerbit()
        );

        $this->actingAs(
            User::findOrFail(1),
            'web'
        )->get(
            route('pembayaran.show', $pembayaran)
        )->assertOk()
            ->assertSee('Terima pembayaran')
            ->assertSee('Tolak pembayaran');

        $this->post(
            route('pembayaran.terima', $pembayaran),
            $this->dataVerifikasi($pembayaran)
        )->assertRedirect(
            route('pembayaran.show', $pembayaran)
        );

        $this->assertSame(
            Pembayaran::DITERIMA,
            $pembayaran->fresh()->status
        );
    }

    public function test_http_suntikan_status_dan_petugas_ditolak(): void
    {
        $pembayaran = $this->ajukan(
            $this->tagihanTerbit()
        );

        $this->actingAs(
            User::findOrFail(1),
            'web'
        )->postJson(
            route('pembayaran.terima', $pembayaran),
            [
                ...$this->dataVerifikasi($pembayaran),
                'status' => Pembayaran::DITERIMA,
                'petugas_id' => 999,
                'revisi' => 999,
            ]
        )->assertUnprocessable()
            ->assertJsonValidationErrors([
                'status',
                'petugas_id',
                'revisi',
            ]);

        $this->assertSame(
            Pembayaran::MENUNGGU,
            $pembayaran->fresh()->status
        );
    }

    public function test_riwayat_verifikasi_tidak_dapat_diubah_atau_dihapus(): void
    {
        $pembayaran = $this->ajukan(
            $this->tagihanTerbit()
        );

        $hasil = app(ProsesVerifikasiPembayaran::class)->terima(
            1,
            $pembayaran,
            $this->dataVerifikasi($pembayaran)
        );

        $verifikasi = $hasil->verifikasi()->firstOrFail();

        try {
            DB::transaction(function () use ($verifikasi): void {
                $verifikasi->catatan = 'Catatan ini mencoba diubah.';
                $verifikasi->save();
            });

            $this->fail(
                'Riwayat verifikasi seharusnya tidak dapat diubah.'
            );
        } catch (\LogicException $exception) {
            $this->assertNotEmpty(
                $exception->getMessage()
            );
        }

        $this->expectException(
            \LogicException::class
        );

        $verifikasi->delete();
    }
}