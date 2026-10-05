<?php

namespace App\Actions;

use App\Models\Berkas;
use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Services\AturanJawaban;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class SimpanJawabanLangsung
{
    public function __construct(
        private readonly KelolaBerkas $berkas
    ) {
    }

    /**
     * Menyiapkan jawaban dan mengunggah berkas langsung
     * dari formulir kegiatan.
     *
     * Mahasiswa tidak perlu membuka Berkas Saya
     * dan tidak perlu memasukkan ID berkas secara manual.
     */
    public function siapkan(
        int $userId,
        Kegiatan $kegiatan,
        ?Pengumpulan $pengumpulan,
        array $data,
        array $unggahan
    ): array {
        if ($pengumpulan !== null) {
            abort_unless(
                (int) $pengumpulan->pemilik_id === $userId
                    && (int) $pengumpulan->kegiatan_id
                        === (int) $kegiatan->getKey(),
                403
            );
        }

        $dihapus = AturanJawaban::ids(
            $data['lampiran_dihapus'] ?? []
        );

        if ($pengumpulan === null && $dihapus !== []) {
            AturanJawaban::gagal(
                'Lampiran yang akan dihapus tidak ditemukan.'
            );
        }

        $berkasDipertahankan = [];

        if ($pengumpulan !== null) {
            $lampiranAktif = $pengumpulan
                ->lampiran()
                ->orderBy('id')
                ->get([
                    'id',
                    'berkas_id',
                ]);

            $idLampiranAktif = $lampiranAktif
                ->pluck('id')
                ->map(
                    static fn ($id): int => (int) $id
                )
                ->all();

            foreach ($dihapus as $idLampiran) {
                if (
                    ! in_array(
                        $idLampiran,
                        $idLampiranAktif,
                        true
                    )
                ) {
                    AturanJawaban::gagal(
                        'Salah satu lampiran yang akan dihapus tidak valid.'
                    );
                }
            }

            $berkasDipertahankan = $lampiranAktif
                ->reject(
                    static fn ($lampiran): bool =>
                        in_array(
                            (int) $lampiran->id,
                            $dihapus,
                            true
                        )
                )
                ->pluck('berkas_id')
                ->map(
                    static fn ($id): int => (int) $id
                )
                ->all();
        }

        $unggahan = array_values($unggahan);

        foreach ($unggahan as $file) {
            if (! $file instanceof UploadedFile) {
                AturanJawaban::gagal(
                    'Salah satu unggahan tidak valid.'
                );
            }
        }

        $jumlahAkhir =
            count($berkasDipertahankan)
            + count($unggahan);

        if ($jumlahAkhir > (int) $kegiatan->maks_berkas) {
            AturanJawaban::gagal(
                'Jumlah berkas melebihi batas kegiatan.'
            );
        }

        $berkasBaru = [];

        foreach ($unggahan as $nomor => $upload) {
            $label = mb_substr(
                'Jawaban '
                    . $kegiatan->judul
                    . ' - berkas '
                    . ($nomor + 1),
                0,
                200
            );

            $hasil = $this->berkas->unggah(
                $userId,
                $upload,
                [
                    'upload_token' => (string) Str::uuid(),
                    'label' => $label,
                    'keterangan' => null,
                ]
            );

            if ($hasil->status !== Berkas::TERSEDIA) {
                AturanJawaban::gagal(
                    'Salah satu berkas belum berhasil disimpan. Silakan unggah kembali.'
                );
            }

            $berkasBaru[] = (int) $hasil->getKey();
        }

        $berkasIds = array_values(
            array_unique([
                ...$berkasDipertahankan,
                ...$berkasBaru,
            ])
        );

        sort($berkasIds, SORT_NUMERIC);

        $hasil = [
            'jawaban_teks' =>
                $data['jawaban_teks'] ?? null,

            'berkas_ids' => $berkasIds,
        ];

        if (
            isset($data['versi_form'])
            && is_string($data['versi_form'])
        ) {
            $hasil['versi_form'] =
                $data['versi_form'];
        }

        return $hasil;
    }
}