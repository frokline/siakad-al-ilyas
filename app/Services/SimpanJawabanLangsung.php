<?php

namespace App\Services;

use App\Actions\KelolaBerkas;
use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SimpanJawabanLangsung
{
    public function __construct(
        private readonly KelolaBerkas $kelolaBerkas
    ) {
    }

    /**
     * Menyiapkan jawaban langsung dari formulir mahasiswa.
     *
     * Pengumpulan boleh null ketika mahasiswa pertama kali
     * mengirimkan jawaban.
     *
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $berkasBaru
     * @return array<string, mixed>
     */
    public function siapkan(
        int $userId,
        Kegiatan $kegiatan,
        ?Pengumpulan $pengumpulan,
        array $data,
        array $berkasBaru
    ): array {
        $lampiranAktif = $pengumpulan === null
            ? collect()
            : $pengumpulan
                ->lampiran()
                ->get([
                    'id',
                    'berkas_id',
                ]);

        $idLampiranAktif = $lampiranAktif
            ->pluck('id')
            ->map(
                fn ($id): string => (string) $id
            )
            ->all();

        $idHapus = collect(
            $data['lampiran_dihapus'] ?? []
        )
            ->map(
                fn ($id): string => (string) $id
            )
            ->unique()
            ->values()
            ->all();

        $tidakSesuai = array_diff(
            $idHapus,
            $idLampiranAktif
        );

        if ($tidakSesuai !== []) {
            throw ValidationException::withMessages([
                'lampiran_dihapus' =>
                    'Salah satu lampiran tidak lagi tersedia. '
                    . 'Muat ulang halaman jawaban.',
            ]);
        }

        $idBerkas = $lampiranAktif
            ->reject(
                fn ($lampiran): bool =>
                    in_array(
                        (string) $lampiran->id,
                        $idHapus,
                        true
                    )
            )
            ->pluck('berkas_id')
            ->map(
                fn ($id): string => (string) $id
            )
            ->values()
            ->all();

        $jumlahUpload = collect($berkasBaru)
            ->filter(
                fn ($file): bool =>
                    $file instanceof UploadedFile
            )
            ->count();

        $jumlahSetelahDisimpan =
            count($idBerkas) + $jumlahUpload;

        if (
            $jumlahSetelahDisimpan >
            (int) $kegiatan->maks_berkas
        ) {
            throw ValidationException::withMessages([
                'berkas_baru' =>
                    'Jumlah seluruh berkas melebihi batas tugas, yaitu '
                    . $kegiatan->maks_berkas
                    . ' berkas.',
            ]);
        }

        foreach ($berkasBaru as $urutan => $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }

            $berkas = $this->kelolaBerkas->unggah(
                $userId,
                $upload,
                [
                    'upload_token' => (string) Str::uuid(),

                    'label' => $this->label(
                        $kegiatan,
                        $urutan + 1
                    ),

                    'keterangan' => null,
                ]
            );

            $idBerkas[] = (string) $berkas->getKey();
        }

        $hasil = [
            'jawaban_teks' =>
                $data['jawaban_teks'] ?? null,

            'berkas_ids' => array_values(
                array_unique($idBerkas)
            ),
        ];

        if ($pengumpulan !== null) {
            $hasil['versi_form'] =
                $data['versi_form'] ?? null;
        }

        return $hasil;
    }

    private function label(
        Kegiatan $kegiatan,
        int $urutan
    ): string {
        $judul = trim(
            (string) $kegiatan->judul
        );

        $label = $judul === ''
            ? 'Jawaban pembelajaran'
            : 'Jawaban: ' . $judul;

        return Str::limit(
            $label,
            240,
            ''
        ) . ' #' . $urutan;
    }
}