<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class AksesKegiatan
{
    public function __construct(
        private readonly AksesMateri $akademik
    ) {
    }

    public function masuk(User $user): bool
    {
        return $this->akademik->masuk($user);
    }

    public function pengelola(User $user): bool
    {
        return $this->akademik->pengelola($user);
    }

    public function kelasKelola(User $user): Builder
    {
        return $this->akademik->kelasKelola($user);
    }

    public function kelola(
        User $user,
        int $kelasId
    ): bool {
        return $this->akademik->kelola(
            $user,
            $kelasId
        );
    }

    public function konteksTulis(
        int $kelasId
    ): bool {
        return $this->akademik->konteksTulis(
            $kelasId
        );
    }

    /**
     * Dosen melihat seluruh pembelajaran kelas yang dikelolanya.
     *
     * Mahasiswa hanya melihat pembelajaran terbit atau ditutup
     * dari kelas yang benar-benar diikutinya.
     */
    public function batasi(
        Builder $query,
        User $user
    ): Builder {
        $kelasKelola = $this->akademik
            ->kelasKelola($user)
            ->select('kelas_kuliah.id');

        $kelasPeserta = $this->akademik
            ->kelasPeserta($user)
            ->select('kelas_kuliah.id');

        return $query->where(
            static function (Builder $pembelajaran) use (
                $kelasKelola,
                $kelasPeserta
            ): void {
                $pembelajaran
                    ->whereIn(
                        'kegiatan.kelas_kuliah_id',
                        $kelasKelola
                    )
                    ->orWhere(
                        static function (Builder $mahasiswa) use (
                            $kelasPeserta
                        ): void {
                            $mahasiswa
                                ->whereIn(
                                    'kegiatan.kelas_kuliah_id',
                                    $kelasPeserta
                                )
                                ->whereIn(
                                    'kegiatan.status',
                                    [
                                        Kegiatan::TERBIT,
                                        Kegiatan::DITUTUP,
                                    ]
                                )
                                ->whereNotNull(
                                    'kegiatan.terbit_at'
                                )
                                ->where(
                                    'kegiatan.terbit_at',
                                    '<=',
                                    now('UTC')
                                )
                                ->where(
                                    static function (
                                        Builder $pertemuan
                                    ): void {
                                        $pertemuan
                                            ->whereNull(
                                                'kegiatan.pertemuan_id'
                                            )
                                            ->orWhereHas(
                                                'pertemuan',
                                                static function (
                                                    Builder $sesi
                                                ): void {
                                                    $sesi->where(
                                                        'status',
                                                        '!=',
                                                        'batal'
                                                    );
                                                }
                                            );
                                    }
                                );
                        }
                    );
            }
        );
    }

    public function lihat(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->batasi(
            Kegiatan::query(),
            $user
        )
            ->whereKey($kegiatan->id)
            ->exists();
    }

    /**
     * Isi pembelajaran langsung dapat dibaca setelah dibagikan.
     *
     * Waktu mulai hanya membatasi kapan mahasiswa dapat
     * mengirim jawaban, bukan membatasi pembacaan instruksi.
     */
    public function bacaIsi(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        if (
            $this->kelola(
                $user,
                $kegiatan->kelas_kuliah_id
            )
        ) {
            return true;
        }

        return $this->lihat(
            $user,
            $kegiatan
        ) && $kegiatan->dapatDilihat();
    }
}