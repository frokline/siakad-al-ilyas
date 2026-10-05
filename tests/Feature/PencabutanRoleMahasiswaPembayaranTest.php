<?php

namespace Tests\Feature;

use App\Actions\KelolaPembayaran;
use App\Models\User;
use App\Services\AksesPembayaran;
use App\Services\AksesTagihan;
use Illuminate\Support\Facades\DB;

class PencabutanRoleMahasiswaPembayaranTest extends PembayaranDatabaseTestCase
{
    public function test_role_mahasiswa_dicabut_memblokir_akses_dan_tindakan_pembayaran(): void
    {
        $tagihan = $this->tagihanTerbit();
        $pembayaran = $this->ajukan($tagihan);

        DB::table('user_roles')
            ->where('user_id', 4)
            ->delete();

        $mahasiswa = User::findOrFail(4);

        $aksesTagihan = app(AksesTagihan::class);
        $aksesPembayaran = app(AksesPembayaran::class);

        $this->assertFalse($aksesTagihan->masuk($mahasiswa));
        $this->assertFalse($aksesPembayaran->masuk($mahasiswa));
        $this->assertFalse(
            $aksesPembayaran->lihat($mahasiswa, $pembayaran)
        );
        $this->assertFalse(
            $aksesPembayaran->ajukan($mahasiswa, $tagihan)
        );
        $this->assertFalse(
            $aksesPembayaran->batal($mahasiswa, $pembayaran)
        );

        try {
            app(KelolaPembayaran::class)->batalkan(
                4,
                $pembayaran,
                [
                    'versi' => $pembayaran->versiForm(),
                    'alasan' => 'Menguji pencabutan peran mahasiswa.',
                    'konfirmasi' => 1,
                ]
            );

            $this->fail(
                'Aksi pembayaran harus ditolak setelah role mahasiswa dicabut.'
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}