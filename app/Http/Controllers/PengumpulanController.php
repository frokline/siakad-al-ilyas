<?php

namespace App\Http\Controllers;

use App\Actions\KelolaPengumpulan;
use App\Http\Requests\SimpanJawabanLangsungRequest;
use App\Models\Berkas;
use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Models\PengumpulanBerkas;
use App\Services\AksesPengumpulan;
use App\Services\PenyimpananBerkas;
use App\Services\SimpanJawabanLangsung;
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

class PengumpulanController extends Controller
{
    public function index(
        Request $request,
        AksesPengumpulan $akses
    ): View {
        Gate::authorize(
            'viewAny',
            Pengumpulan::class
        );

        $filter = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
            'kegiatan' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    Pengumpulan::TERKIRIM,
                    Pengumpulan::DIBATALKAN,
                    'berlaku',
                ]),
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
                Pengumpulan::query(),
                $request->user()
            )
            ->select([
                'pengumpulan.id',
                'kegiatan_id',
                'detail_krs_id',
                'pemilik_id',
                'status',
                'dikirim_at',
                'diubah_at',
                'dibatalkan_at',
                'revisi',
                'updated_at',
            ])
            ->with([
                'pemilik:id,nama',
                'kegiatan:id,kelas_kuliah_id,judul',
                'kegiatan.kelasKuliah',
            ]);

        if (! empty($filter['q'])) {
            $pencarian = trim($filter['q']);

            $query->whereHas(
                'kegiatan',
                fn ($kegiatan) =>
                    $kegiatan->where(
                        'judul',
                        'like',
                        '%' . $pencarian . '%'
                    )
            );
        }

        if (! empty($filter['kegiatan'])) {
            $query->where(
                'kegiatan_id',
                $filter['kegiatan']
            );
        }

        if (($filter['status'] ?? null) === 'berlaku') {
            $query->berlaku();
        } elseif (! empty($filter['status'])) {
            $query->where(
                'pengumpulan.status',
                $filter['status']
            );
        }

        $daftar = $query
            ->orderByDesc('pengumpulan.id')
            ->paginate(20)
            ->withQueryString();

        $berlakuIds = Pengumpulan::query()
            ->berlaku()
            ->whereKey(
                $daftar->getCollection()->modelKeys()
            )
            ->pluck('id')
            ->map(
                fn ($id): int => (int) $id
            )
            ->all();

        return view('pengumpulan.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'zona' => WaktuKegiatan::zona(),
            'berlakuIds' => $berlakuIds,
        ]);
    }

    /**
     * Ruang pengerjaan mahasiswa untuk satu kegiatan.
     */
    public function saya(
        Request $request,
        Kegiatan $kegiatan,
        AksesPengumpulan $akses
    ): View {
        Gate::authorize(
            'ruangSaya',
            [
                Pengumpulan::class,
                $kegiatan,
            ]
        );

        $kegiatan->load([
            'kelasKuliah',
            'lampiran',
        ]);

        $detailId = $akses->detail(
            $request->user(),
            $kegiatan
        );

        $pengumpulan = $detailId === null
            ? null
            : Pengumpulan::query()
                ->where(
                    'kegiatan_id',
                    $kegiatan->getKey()
                )
                ->where(
                    'detail_krs_id',
                    $detailId
                )
                ->where(
                    'pemilik_id',
                    $request->user()->getKey()
                )
                ->with([
                    'lampiran.berkas',
                ])
                ->first();

        return view('pengumpulan.saya', [
            'kegiatan' => $kegiatan,
            'pengumpulan' => $pengumpulan,
            'bolehTulis' => $akses->bolehTulis(
                $request->user(),
                $kegiatan
            ),
            'zona' => WaktuKegiatan::zona(),
        ]);
    }

    /**
     * Rekap jawaban mahasiswa untuk dosen.
     */
    public function rekap(
        Request $request,
        Kegiatan $kegiatan,
        AksesPengumpulan $akses
    ): View {
        Gate::authorize(
            'rekap',
            [
                Pengumpulan::class,
                $kegiatan,
            ]
        );

        $filter = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'sudah',
                    'belum',
                ]),
            ],
            'peserta_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
            'kiriman_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
        ]);

        $kegiatan->load('kelasKuliah');

        $jawabanAktif = Pengumpulan::query()
            ->berlaku()
            ->where(
                'kegiatan_id',
                $kegiatan->getKey()
            )
            ->select([
                'pengumpulan.id',
                'detail_krs_id',
                'dikirim_at',
                'diubah_at',
            ]);

        $peserta = $akses
            ->peserta($kegiatan->kelas_kuliah_id)
            ->leftJoinSub(
                $jawabanAktif,
                'jawaban',
                fn ($join) =>
                    $join->on(
                        'jawaban.detail_krs_id',
                        '=',
                        'd.id'
                    )
            );

        $total = (clone $peserta)->count('d.id');

        $sudah = (clone $peserta)
            ->whereNotNull('jawaban.id')
            ->count('d.id');

        if (! empty($filter['q'])) {
            $pencarian = trim($filter['q']);

            $peserta->where(
                fn ($query) =>
                    $query
                        ->where(
                            'u.nama',
                            'like',
                            '%' . $pencarian . '%'
                        )
                        ->orWhere(
                            'm.nim',
                            'like',
                            '%' . $pencarian . '%'
                        )
            );
        }

        if (($filter['status'] ?? null) === 'sudah') {
            $peserta->whereNotNull('jawaban.id');
        }

        if (($filter['status'] ?? null) === 'belum') {
            $peserta->whereNull('jawaban.id');
        }

        $historis = Pengumpulan::query()
            ->where(
                'kegiatan_id',
                $kegiatan->getKey()
            )
            ->select([
                'id',
                'kegiatan_id',
                'detail_krs_id',
                'pemilik_id',
                'status',
                'dikirim_at',
                'diubah_at',
                'dibatalkan_at',
                'revisi',
            ])
            ->with('pemilik:id,nama');

        return view('pengumpulan.rekap', [
            'kegiatan' => $kegiatan,
            'filter' => $filter,
            'total' => $total,
            'sudah' => $sudah,

            'peserta' => $peserta
                ->orderBy('m.nim')
                ->orderBy('d.id')
                ->paginate(
                    20,
                    [
                        'd.id as detail_id',
                        'm.nim',
                        'u.nama',
                        'jawaban.id as pengumpulan_id',
                        'jawaban.dikirim_at',
                        'jawaban.diubah_at',
                    ],
                    'peserta_page'
                )
                ->withQueryString(),

            'historis' => $historis
                ->orderByDesc('id')
                ->paginate(
                    20,
                    ['*'],
                    'kiriman_page'
                )
                ->withQueryString(),

            'zona' => WaktuKegiatan::zona(),
        ]);
    }

    /**
     * Menyimpan jawaban pertama.
     */
    public function store(
        SimpanJawabanLangsungRequest $request,
        Kegiatan $kegiatan,
        KelolaPengumpulan $aksi,
        SimpanJawabanLangsung $jawaban
    ): RedirectResponse {
        $data = $jawaban->siapkan(
            (int) $request->user()->getKey(),
            $kegiatan,
            null,
            $request->validated(),
            $request->file('berkas_baru', [])
        );

        $pengumpulan = $aksi->simpan(
            (int) $request->user()->getKey(),
            $kegiatan,
            $data
        );

        return redirect()
            ->route(
                'pengumpulan.show',
                $pengumpulan
            )
            ->with(
                'info',
                'Jawaban berhasil dikumpulkan.'
            );
    }

    public function show(
        Request $request,
        Pengumpulan $pengumpulan,
        AksesPengumpulan $akses
    ): View {
        Gate::authorize(
            'view',
            $pengumpulan
        );

        $pengumpulan->load([
            'pemilik',
            'kegiatan.kelasKuliah',
            'lampiran.berkas',
        ]);

        $pemilik = $akses->pemilik(
            $request->user(),
            $pengumpulan
        );

        return view('pengumpulan.show', [
            'pengumpulan' => $pengumpulan,

            'berlaku' => Pengumpulan::query()
                ->berlaku()
                ->whereKey($pengumpulan->getKey())
                ->exists(),

            'pemilik' => $pemilik,

            'bolehTulis' =>
                $pemilik
                && $pengumpulan->status
                    === Pengumpulan::TERKIRIM
                && $akses->bolehTulis(
                    $request->user(),
                    $pengumpulan->kegiatan
                ),

            'audit' => $pengumpulan
                ->audits()
                ->with('pelaku')
                ->orderByDesc('versi_entitas')
                ->paginate(10),

            'zona' => WaktuKegiatan::zona(),
        ]);
    }

    public function edit(
        Pengumpulan $pengumpulan
    ): View {
        Gate::authorize(
            'update',
            $pengumpulan
        );

        $pengumpulan->load([
            'kegiatan.kelasKuliah',
            'lampiran',
        ]);

        return view('pengumpulan.edit', [
            'pengumpulan' => $pengumpulan,
            'zona' => WaktuKegiatan::zona(),
        ]);
    }

    /**
     * Mengubah jawaban sebelum tenggat.
     */
    public function update(
        SimpanJawabanLangsungRequest $request,
        Pengumpulan $pengumpulan,
        KelolaPengumpulan $aksi,
        SimpanJawabanLangsung $jawaban
    ): RedirectResponse {
        $pengumpulan->loadMissing('kegiatan');

        $data = $jawaban->siapkan(
            (int) $request->user()->getKey(),
            $pengumpulan->kegiatan,
            $pengumpulan,
            $request->validated(),
            $request->file('berkas_baru', [])
        );

        $hasil = $aksi->simpan(
            (int) $request->user()->getKey(),
            $pengumpulan->kegiatan,
            $data
        );

        return redirect()
            ->route(
                'pengumpulan.show',
                $hasil
            )
            ->with(
                'info',
                'Perubahan jawaban berhasil disimpan.'
            );
    }

    /**
     * Membatalkan atau menghapus jawaban sebelum tenggat.
     */
    public function destroy(
        Request $request,
        Pengumpulan $pengumpulan,
        KelolaPengumpulan $aksi
    ): RedirectResponse {
        Gate::authorize(
            'delete',
            $pengumpulan
        );

        $data = $request->validate([
            'versi_form' => [
                'required',
                'string',
                'regex:/\A[a-f0-9]{64}\z/',
            ],
        ]);

        $kegiatan = $pengumpulan->kegiatan()
            ->firstOrFail();

        $aksi->batalkan(
            (int) $request->user()->getKey(),
            $pengumpulan,
            $data
        );

        return redirect()
            ->route(
                'pengumpulan.saya',
                $kegiatan
            )
            ->with(
                'info',
                'Jawaban berhasil dihapus.'
            );
    }

    public function tautan(
        Request $request,
        Pengumpulan $pengumpulan,
        PengumpulanBerkas $lampiran
    ): RedirectResponse {
        $berkas = $this->lampiran(
            $pengumpulan,
            $lampiran
        );

        $tautan = URL::temporarySignedRoute(
            'pengumpulan.unduh',
            now()->addMinutes(
                max(
                    1,
                    min(
                        5,
                        (int) config(
                            'pengumpulan.masa_tautan_menit',
                            2
                        )
                    )
                )
            ),
            [
                'pengumpulan' => $pengumpulan->getKey(),
                'lampiran' => $lampiran->getKey(),
                'pemohon' => $request->user()->getKey(),
                'versi_pengumpulan' =>
                    $pengumpulan->revisi,
                'versi_berkas' => $berkas->revisi,
            ]
        );

        return redirect()->to($tautan);
    }

    public function unduh(
        Request $request,
        Pengumpulan $pengumpulan,
        PengumpulanBerkas $lampiran,
        PenyimpananBerkas $penyimpanan
    ): StreamedResponse {
        $berkas = $this->lampiran(
            $pengumpulan,
            $lampiran
        );

        $this->periksaTokenUnduh(
            $request,
            $pengumpulan,
            $berkas
        );

        try {
            $stream = $penyimpanan->buka($berkas);
        } catch (\Throwable $exception) {
            Log::warning(
                'Unduh jawaban gagal.',
                [
                    'pengumpulan_id' =>
                        $pengumpulan->getKey(),

                    'berkas_id' => $berkas->getKey(),

                    'jenis' => $exception::class,
                ]
            );

            abort(
                503,
                'Berkas belum dapat diunduh. Coba lagi atau hubungi pengelola.'
            );
        }

        try {
            $pengumpulan->refresh();
            $lampiran->refresh();

            $berkas = $this->lampiran(
                $pengumpulan,
                $lampiran
            );

            $this->periksaTokenUnduh(
                $request,
                $pengumpulan,
                $berkas
            );
        } catch (\Throwable $exception) {
            fclose($stream);

            throw $exception;
        }

        $namaDasar = Str::slug(
            mb_substr(
                pathinfo(
                    $lampiran->nama_asli,
                    PATHINFO_FILENAME
                ),
                0,
                100
            )
        );

        if ($namaDasar === '') {
            $namaDasar =
                'jawaban-' . $lampiran->getKey();
        }

        $namaUnduhan =
            $namaDasar . '.' . $lampiran->ekstensi;

        return response()->streamDownload(
            function () use ($stream): void {
                try {
                    fpassthru($stream);
                } finally {
                    fclose($stream);
                }
            },
            $namaUnduhan,
            [
                'Content-Type' =>
                    'application/octet-stream',

                'X-Content-Type-Options' =>
                    'nosniff',

                'Cache-Control' =>
                    'private, no-store',

                'Referrer-Policy' =>
                    'no-referrer',

                'Content-Length' =>
                    (string) $lampiran->ukuran_byte,
            ]
        );
    }

    private function lampiran(
        Pengumpulan $pengumpulan,
        PengumpulanBerkas $lampiran
    ): Berkas {
        Gate::authorize(
            'view',
            $pengumpulan
        );

        abort_unless(
            (int) $lampiran->pengumpulan_id
                === (int) $pengumpulan->getKey()
                && $lampiran->aktif,
            404
        );

        $berkas = Berkas::query()
            ->findOrFail($lampiran->berkas_id);

        abort_unless(
            $berkas->status === Berkas::TERSEDIA
                && (int) $berkas->diunggah_oleh
                    === (int) $pengumpulan->pemilik_id
                && $lampiran->cocok($berkas),
            404
        );

        return $berkas;
    }

    private function periksaTokenUnduh(
        Request $request,
        Pengumpulan $pengumpulan,
        Berkas $berkas
    ): void {
        abort_unless(
            $request->query('pemohon')
                === (string) $request->user()->getKey()
                && $request->query('versi_pengumpulan')
                    === (string) $pengumpulan->revisi
                && $request->query('versi_berkas')
                    === (string) $berkas->revisi,
            403,
            'Tautan berubah. Klik tombol Unduh kembali.'
        );
    }
}