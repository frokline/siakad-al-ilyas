<?php

namespace App\Services;

use App\Actions\KelolaBerkas;
use App\Models\Kegiatan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SimpanLampiranKegiatan
{
    public function __construct(
        private readonly KelolaBerkas $kelolaBerkas
    ) {
    }

    /**
     * Menyiapkan lampiran saat dosen membuat tugas baru.
     *
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $lampiranBaru
     * @return array<string, mixed>
     */
    public function untukBaru(
        int $userId,
        array $data,
        array $lampiranBaru
    ): array {
        return $this->siapkan(
            $userId,
            (string) ($data['judul'] ?? ''),
            [],
            [],
            $data,
            $lampiranBaru
        );
    }

    /**
     * Menyiapkan lampiran saat dosen mengubah tugas yang masih draf.
     *
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $lampiranBaru
     * @return array<string, mixed>
     */
    public function untukPerubahan(
        int $userId,
        Kegiatan $kegiatan,
        array $data,
        array $lampiranBaru
    ): array {
        $lampiranAktif = $kegiatan
            ->lampiran()
            ->get(['id', 'berkas_id']);

        return $this->siapkan(
            $userId,
            (string) ($data['judul'] ?? $kegiatan->judul),
            $lampiranAktif
                ->pluck('id')
                ->map(fn ($id): string => (string) $id)
                ->all(),
            $lampiranAktif
                ->pluck('berkas_id')
                ->map(fn ($id): string => (string) $id)
                ->all(),
            $data,
            $lampiranBaru
        );
    }

    /**
     * @param array<int, string> $idLampiranAktif
     * @param array<int, string> $idBerkasAktif
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $lampiranBaru
     * @return array<string, mixed>
     */
    private function siapkan(
        int $userId,
        string $judul,
        array $idLampiranAktif,
        array $idBerkasAktif,
        array $data,
        array $lampiranBaru
    ): array {
        $idHapus = array_values(array_unique(array_map(
            'strval',
            (array) ($data['lampiran_dihapus'] ?? [])
        )));

        $tidakSesuai = array_diff($idHapus, $idLampiranAktif);

        if ($tidakSesuai !== []) {
            throw ValidationException::withMessages([
                'lampiran_dihapus' =>
                    'Lampiran berubah atau tidak lagi tersedia. '
                    . 'Muat ulang halaman tugas.',
            ]);
        }

        $idBerkas = [];

        foreach ($idLampiranAktif as $urutan => $idLampiran) {
            if (! in_array($idLampiran, $idHapus, true)) {
                $idBerkas[] = $idBerkasAktif[$urutan];
            }
        }

        $batas = (int) config(
            'kegiatan.maks_lampiran_instruksi',
            10
        );

        if (count($idBerkas) + count($lampiranBaru) > $batas) {
            throw ValidationException::withMessages([
                'lampiran_baru' =>
                    'Jumlah lampiran instruksi melebihi batas '
                    . $batas
                    . ' berkas.',
            ]);
        }

        foreach ($lampiranBaru as $urutan => $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }

            $berkas = $this->kelolaBerkas->unggah(
                $userId,
                $upload,
                [
                    'upload_token' => (string) Str::uuid(),
                    'label' => $this->label($judul, $urutan + 1),
                    'keterangan' => null,
                ]
            );

            $idBerkas[] = (string) $berkas->id;
        }

        unset(
            $data['lampiran_baru'],
            $data['lampiran_dihapus']
        );

        $data['berkas_ids'] = array_values(
            array_unique($idBerkas)
        );

        return $data;
    }

    private function label(string $judul, int $urutan): string
    {
        $judul = trim($judul);

        $label = $judul === ''
            ? 'Lampiran tugas'
            : 'Lampiran tugas: ' . $judul;

        return Str::limit($label, 240, '') . ' #' . $urutan;
    }
}