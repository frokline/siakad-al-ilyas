<?php

namespace App\Http\Controllers;

use App\Actions\KelolaKegiatan;
use App\Http\Requests\KegiatanRequest;
use App\Http\Requests\KegiatanStatusRequest;
use App\Models\Berkas;
use App\Models\KelasKuliah;
use App\Models\Kegiatan;
use App\Models\KegiatanBerkas;
use App\Models\Pertemuan;
use App\Services\AksesKegiatan;
use App\Services\NotifikasiTugasBaru;
use App\Services\PenyimpananBerkas;
use App\Services\SimpanLampiranKegiatan;
use App\Services\WaktuKegiatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class KegiatanController extends Controller
{
    public function index(
        Request $request,
        AksesKegiatan $akses
    ): View {
        Gate::authorize('viewAny', Kegiatan::class);

        $filter = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
            'kelas' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'jenis' => [
                'nullable',
                'string',
                Rule::in(array_keys(Kegiatan::JENIS)),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(Kegiatan::STATUS)),
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
        ]);

        $query = $akses
            ->batasi(
                Kegiatan::query(),
                $request->user()
            )
            ->with('kelasKuliah')
            ->select([
                'kegiatan.id',
                'kelas_kuliah_id',
                'judul',
                'jenis',
                'status',
                'buka_at',
                'tenggat_at',
                'terbit_at',
            ]);

        if (! empty($filter['q'])) {
            $query->where(
                'judul',
                'like',
                '%' . trim($filter['q']) . '%'
            );
        }

        if (! empty($filter['kelas'])) {
            $query->where(
                'kelas_kuliah_id',
                $filter['kelas']
            );
        }

        if (! empty($filter['jenis'])) {
            $query->where(
                'jenis',
                $filter['jenis']
            );
        }

        if (! empty($filter['status'])) {
            $query->where(
                'status',
                $filter['status']
            );
        }

        return view('kegiatan.index', [
            'daftar' => $query
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),

            'filter' => $filter,

            'pengelola' => $akses->pengelola(
                $request->user()
            ),

            'zona' => WaktuKegiatan::zona(),

            'pilihanJenis' => Kegiatan::JENIS,

            'pilihanStatus' => Kegiatan::STATUS,
        ]);
    }

    public function kelas(
        Request $request,
        AksesKegiatan $akses
    ): View {
        abort_unless(
            $akses->pengelola($request->user()),
            403
        );

        $filter = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
        ]);

        $query = $akses->kelasKelola(
            $request->user()
        );

        if (! empty($filter['q'])) {
            $pencarian = trim($filter['q']);

            $query->where(
                static function ($kelas) use (
                    $pencarian
                ): void {
                    $kelas
                        ->where(
                            'kode',
                            'like',
                            '%' . $pencarian . '%'
                        )
                        ->orWhere(
                            'nama_mk_snapshot',
                            'like',
                            '%' . $pencarian . '%'
                        );
                }
            );
        }

        return view('kegiatan.kelas', [
            'daftar' => $query
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),

            'filter' => $filter,
        ]);
    }

    public function create(Request $request): View
    {
        $data = $request->validate([
            'kelas' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $kelas = KelasKuliah::query()
            ->findOrFail($data['kelas']);

        Gate::authorize(
            'create',
            [
                Kegiatan::class,
                $kelas,
            ]
        );

        return view(
            'kegiatan.create',
            $this->form($kelas) + [
                'kegiatan' => new Kegiatan(),
                'token' => (string) Str::uuid(),
            ]
        );
    }

    public function store(
        KegiatanRequest $request,
        KelolaKegiatan $aksi,
        SimpanLampiranKegiatan $lampiran,
        NotifikasiTugasBaru $notifikasi
    ): RedirectResponse {
        $data = $lampiran->untukBaru(
            (int) $request->user()->id,
            $request->validated(),
            $request->file('lampiran_baru', [])
        );

        /*
         * KelolaKegiatan::buat() sudah langsung:
         * - menyimpan kegiatan,
         * - menetapkan status terbit,
         * - menyimpan lampiran,
         * - mencatat audit.
         *
         * Tidak ada lagi pemanggilan aksi "terbitkan".
         */
        $kegiatan = $aksi->buat(
            (int) $request->user()->id,
            $data
        );

        $jumlah = $notifikasi->kirim(
            $kegiatan
        );

        $namaJenis = Kegiatan::JENIS[
            $kegiatan->jenis
        ] ?? 'Pembelajaran';

        return redirect()
            ->route(
                'kegiatan.show',
                $kegiatan
            )
            ->with(
                'info',
                $namaJenis
                    . ' berhasil dibagikan kepada '
                    . $jumlah
                    . ' mahasiswa.'
            );
    }

    public function show(
        Request $request,
        Kegiatan $kegiatan
    ): View {
        Gate::authorize(
            'view',
            $kegiatan
        );

        $request->validate([
            'page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
        ]);

        $bacaIsi = Gate::allows(
            'bacaIsi',
            $kegiatan
        );

        $kegiatan->load([
            'kelasKuliah',
            'pertemuan',
            'pembuat',
        ]);

        if ($bacaIsi) {
            $kegiatan->load(
                'lampiran.berkas'
            );
        }

        return view('kegiatan.show', [
            'kegiatan' => $kegiatan,

            'audit' => Gate::allows(
                'manage',
                $kegiatan
            )
                ? $kegiatan
                    ->audits()
                    ->with('pelaku')
                    ->orderByDesc('versi_entitas')
                    ->paginate(10)
                : null,

            'zona' => WaktuKegiatan::zona(),

            'bacaIsi' => $bacaIsi,
        ]);
    }

    public function edit(
        Kegiatan $kegiatan
    ): View {
        Gate::authorize(
            'update',
            $kegiatan
        );

        $kegiatan->load([
            'kelasKuliah',
            'lampiran.berkas',
        ]);

        return view(
            'kegiatan.edit',
            $this->form(
                $kegiatan->kelasKuliah
            ) + [
                'kegiatan' => $kegiatan,
            ]
        );
    }

    public function update(
        KegiatanRequest $request,
        Kegiatan $kegiatan,
        KelolaKegiatan $aksi,
        SimpanLampiranKegiatan $lampiran,
        NotifikasiTugasBaru $notifikasi
    ): RedirectResponse {
        $data = $lampiran->untukPerubahan(
            (int) $request->user()->id,
            $kegiatan,
            $request->validated(),
            $request->file('lampiran_baru', [])
        );

        $kegiatan = $aksi->ubah(
            (int) $request->user()->id,
            $kegiatan,
            $data
        );

        $jumlah = $notifikasi->kirim(
            $kegiatan
        );

        return redirect()
            ->route(
                'kegiatan.show',
                $kegiatan
            )
            ->with(
                'info',
                'Pembelajaran berhasil diperbarui. '
                    . 'Notifikasi dikirim kepada '
                    . $jumlah
                    . ' mahasiswa.'
            );
    }

    public function tutup(
        KegiatanStatusRequest $request,
        Kegiatan $kegiatan,
        KelolaKegiatan $aksi
    ): RedirectResponse {
        return $this->ubahStatus(
            'tutup',
            $request,
            $kegiatan,
            $aksi
        );
    }

    public function bukaKembali(
        KegiatanStatusRequest $request,
        Kegiatan $kegiatan,
        KelolaKegiatan $aksi
    ): RedirectResponse {
        return $this->ubahStatus(
            'bukaKembali',
            $request,
            $kegiatan,
            $aksi
        );
    }

    public function perpanjang(
        KegiatanStatusRequest $request,
        Kegiatan $kegiatan,
        KelolaKegiatan $aksi
    ): RedirectResponse {
        return $this->ubahStatus(
            'perpanjang',
            $request,
            $kegiatan,
            $aksi
        );
    }

    public function arsipkan(
        KegiatanStatusRequest $request,
        Kegiatan $kegiatan,
        KelolaKegiatan $aksi
    ): RedirectResponse {
        return $this->ubahStatus(
            'arsipkan',
            $request,
            $kegiatan,
            $aksi
        );
    }

    public function pulihkan(
        KegiatanStatusRequest $request,
        Kegiatan $kegiatan,
        KelolaKegiatan $aksi
    ): RedirectResponse {
        return $this->ubahStatus(
            'pulihkan',
            $request,
            $kegiatan,
            $aksi
        );
    }

    public function tautan(
        Request $request,
        Kegiatan $kegiatan,
        KegiatanBerkas $lampiran
    ): RedirectResponse {
        $berkas = $this->lampiran(
            $kegiatan,
            $lampiran
        );

        $tautan = URL::temporarySignedRoute(
            'kegiatan.unduh',
            now()->addMinutes(
                (int) config(
                    'kegiatan.masa_tautan_menit',
                    2
                )
            ),
            [
                'kegiatan' => $kegiatan->id,
                'lampiran' => $lampiran->id,
                'pemohon' => $request->user()->id,
                'versi_kegiatan' => $kegiatan->revisi,
                'versi_berkas' => $berkas->revisi,
            ]
        );

        return redirect()->to($tautan);
    }

    public function unduh(
        Request $request,
        Kegiatan $kegiatan,
        KegiatanBerkas $lampiran,
        PenyimpananBerkas $penyimpanan
    ): StreamedResponse {
        $berkas = $this->lampiran(
            $kegiatan,
            $lampiran
        );

        $this->periksaTokenUnduh(
            $request,
            $kegiatan,
            $berkas
        );

        try {
            $stream = $penyimpanan->buka(
                $berkas
            );
        } catch (\Throwable $exception) {
            Log::warning(
                'Unduh lampiran kegiatan gagal.',
                [
                    'kegiatan_id' => $kegiatan->id,
                    'berkas_id' => $berkas->id,
                    'jenis' => $exception::class,
                ]
            );

            abort(
                503,
                'Lampiran belum dapat diunduh. '
                    . 'Coba lagi atau hubungi pengelola.'
            );
        }

        try {
            $kegiatan->refresh();
            $lampiran->refresh();

            $berkas = $this->lampiran(
                $kegiatan,
                $lampiran
            );

            $this->periksaTokenUnduh(
                $request,
                $kegiatan,
                $berkas
            );
        } catch (\Throwable $exception) {
            fclose($stream);

            throw $exception;
        }

        $nama = Str::slug(
            mb_substr(
                $berkas->label,
                0,
                100
            )
        );

        if ($nama === '') {
            $nama = 'lampiran-' . $berkas->id;
        }

        return response()->streamDownload(
            static function () use ($stream): void {
                try {
                    fpassthru($stream);
                } finally {
                    fclose($stream);
                }
            },
            $nama . '.' . $berkas->ekstensi,
            [
                'Content-Type' =>
                    'application/octet-stream',

                'X-Content-Type-Options' =>
                    'nosniff',

                'Cache-Control' =>
                    'private, no-store',

                'Content-Length' =>
                    (string) $berkas->ukuran_byte,
            ]
        );
    }

    private function lampiran(
        Kegiatan $kegiatan,
        KegiatanBerkas $lampiran
    ): Berkas {
        Gate::authorize(
            'bacaIsi',
            $kegiatan
        );

        abort_unless(
            (int) $lampiran->kegiatan_id
                === (int) $kegiatan->id
                && (bool) $lampiran->aktif,
            404
        );

        $berkas = Berkas::query()
            ->findOrFail(
                $lampiran->berkas_id
            );

        abort_unless(
            $berkas->status === Berkas::TERSEDIA,
            404
        );

        return $berkas;
    }

    private function periksaTokenUnduh(
        Request $request,
        Kegiatan $kegiatan,
        Berkas $berkas
    ): void {
        abort_unless(
            $request->query('pemohon')
                === (string) $request->user()->id
            && $request->query('versi_kegiatan')
                === (string) $kegiatan->revisi
            && $request->query('versi_berkas')
                === (string) $berkas->revisi,
            403,
            'Tautan berubah. Klik Unduh kembali.'
        );
    }

    private function form(
        KelasKuliah $kelas
    ): array {
        return [
            'kelas' => $kelas,

            'pertemuan' => Pertemuan::query()
                ->where(
                    'kelas_kuliah_id',
                    $kelas->id
                )
                ->where(
                    'status',
                    '!=',
                    'batal'
                )
                ->orderBy('nomor')
                ->get(),

            'zona' => WaktuKegiatan::zona(),

            'pilihanJenis' => Kegiatan::JENIS,

            'pilihanEkstensi' =>
                Kegiatan::EKSTENSI_JAWABAN,
        ];
    }

    private function ubahStatus(
        string $namaAksi,
        KegiatanStatusRequest $request,
        Kegiatan $kegiatan,
        KelolaKegiatan $aksi
    ): RedirectResponse {
        $kegiatan = $aksi->status(
            $namaAksi,
            (int) $request->user()->id,
            $kegiatan,
            $request->validated()
        );

        $labelStatus = Kegiatan::STATUS[
            $kegiatan->status
        ] ?? $kegiatan->status;

        return redirect()
            ->route(
                'kegiatan.show',
                $kegiatan
            )
            ->with(
                'info',
                'Status pembelajaran: '
                    . $labelStatus
                    . '.'
            );
    }
}