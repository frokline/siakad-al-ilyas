<?php

namespace App\Http\Controllers;

use App\Actions\SimpanKelasKuliah;
use App\Http\Requests\KelasKuliahRequest;
use App\Models\KelasKuliah;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\Rombel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasKuliahController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola-kelas-kuliah');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'periode_akademik_id' => ['nullable', 'integer', 'exists:periode_akademik,id'],
            'program_studi_id' => ['nullable', 'integer', 'exists:program_studi,id'],
            'rombel_id' => ['nullable', 'integer', 'exists:rombel,id'],
            'semester_studi' => ['nullable', 'integer', 'between:1,32767'],
            'status' => ['nullable', 'string', Rule::in(array_keys(KelasKuliah::STATUS))],
        ]);

        $query = KelasKuliah::query()->with($this->relations());
        $q = trim((string) ($filter['q'] ?? ''));

        if ($q !== '') {
            $query->where(function (Builder $query) use ($q): void {
                $query->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama_mk_snapshot', 'like', "%{$q}%")
                    ->orWhereHas('rombel', fn(Builder $rombel) => $rombel->where('kode', 'like', "%{$q}%"));
            });
        }

        foreach (['status', 'rombel_id'] as $field) {
            if (isset($filter[$field]) && $filter[$field] !== '') {
                $query->where($field, $filter[$field]);
            }
        }

        if (! empty($filter['periode_akademik_id'])) {
            $query->whereHas('rombel', function (Builder $rombel) use ($filter): void {
                $rombel->where('periode_akademik_id', $filter['periode_akademik_id']);
            });
        }
        if (! empty($filter['program_studi_id'])) {
            $query->whereHas('rombel.paketSemester.kurikulum', function (Builder $kurikulum) use ($filter): void {
                $kurikulum->where('program_studi_id', $filter['program_studi_id']);
            });
        }
        if (! empty($filter['semester_studi'])) {
            $query->whereHas('rombel.paketSemester', function (Builder $paket) use ($filter): void {
                $paket->where('semester_studi', $filter['semester_studi']);
            });
        }

        return view('admin.kelas-kuliah.index', [
            'kelasDaftar' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'periodePilihan' => PeriodeAkademik::query()->orderByDesc('mulai')->get(),
            'prodiPilihan' => ProgramStudi::query()->orderBy('nama')->get(),
            'rombelFilter' => ! empty($filter['rombel_id'])
                ? Rombel::query()->findOrFail($filter['rombel_id']) : null,
            'filter' => $filter,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('kelola-kelas-kuliah');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rombel_id' => ['nullable', 'integer', 'exists:rombel,id'],
            'periode_akademik_id' => ['nullable', 'integer', 'exists:periode_akademik,id'],
        ]);

        $rombel = null;
        $rombelPilihan = null;
        $detailPilihan = collect();
        $kelasAda = collect();
        $bolehMembuat = false;

        if (! empty($filter['rombel_id'])) {
            $rombel = $this->rombelDenganRingkasan()->findOrFail($filter['rombel_id']);
            $paket = $rombel->paketSemester;

            $bolehMembuat = in_array(
                $rombel->periodeAkademik->status,
                Rombel::STATUS_PERIODE_TERBUKA,
                true
            ) && in_array($paket->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true)
                && $paket->kurikulum->status === 'aktif'
                && $paket->kurikulum->programStudi->aktif;

            if ($bolehMembuat) {
                $detailPilihan = $paket->details()
                    ->with('kurikulumMataKuliah.mataKuliah')
                    ->whereHas('kurikulumMataKuliah', function (Builder $kmk) use ($paket): void {
                        $kmk->where('kurikulum_id', $paket->kurikulum_id)
                            ->whereHas('mataKuliah', fn(Builder $mk) => $mk->where('aktif', true));
                    })
                    ->whereDoesntHave('kelasKuliah', function (Builder $kelas) use ($rombel): void {
                        $kelas->where('rombel_id', $rombel->id);
                    })
                    ->orderBy('id')->get();
            }

            $kelasAda = $rombel->kelasKuliah()->orderBy('kode')->get();
        } else {
            $query = $this->rombelDenganRingkasan()
                ->whereHas('periodeAkademik', function (Builder $periode): void {
                    $periode->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA);
                })
                ->whereHas('paketSemester', function (Builder $paket): void {
                    $paket->whereIn('status', [PaketSemester::DITERBITKAN, PaketSemester::ARSIP])
                        ->has('details')
                        ->whereHas('kurikulum', function (Builder $kurikulum): void {
                            $kurikulum->where('status', 'aktif')
                                ->whereHas('programStudi', fn(Builder $prodi) => $prodi->where('aktif', true));
                        });
                });

            if (! empty($filter['periode_akademik_id'])) {
                $query->where('periode_akademik_id', $filter['periode_akademik_id']);
            }

            $q = trim((string) ($filter['q'] ?? ''));
            if ($q !== '') {
                $query->where(function (Builder $rombel) use ($q): void {
                    $rombel->where('kode', 'like', "%{$q}%")
                        ->orWhereHas('paketSemester', fn(Builder $paket) => $paket->where('nama', 'like', "%{$q}%"));
                });
            }

            $rombelPilihan = $query->orderByDesc('periode_akademik_id')
                ->orderBy('kode')->paginate(15)->withQueryString();
        }

        return view('admin.kelas-kuliah.create', [
            'kelas' => new KelasKuliah(),
            'rombel' => $rombel,
            'rombelPilihan' => $rombelPilihan,
            'detailPilihan' => $detailPilihan,
            'kelasAda' => $kelasAda,
            'bolehMembuat' => $bolehMembuat,
            'periodePilihan' => PeriodeAkademik::query()
                ->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA)
                ->orderByDesc('mulai')->get(),
            'filter' => $filter,
        ]);
    }

    public function store(KelasKuliahRequest $request, SimpanKelasKuliah $action): RedirectResponse
    {
        $kelas = $action->execute((int) $request->user()->id, $request->validated());

        return to_route('admin.kelas-kuliah.show', $kelas)
            ->with('success', 'Kelas berhasil dibuat dengan status persiapan.');
    }

    public function show(KelasKuliah $kelasKuliah): View
    {
        Gate::authorize('kelola-kelas-kuliah');
        $kelasKuliah->load($this->relations());

        return view('admin.kelas-kuliah.show', [
            'kelas' => $kelasKuliah,
            'rombel' => $kelasKuliah->rombel,
        ]);
    }

    public function edit(KelasKuliah $kelasKuliah): View
    {
        Gate::authorize('kelola-kelas-kuliah');
        $kelasKuliah->load($this->relations());
        abort_unless(
            $kelasKuliah->dapatDiubah(),
            409,
            'Kelas hanya dapat dibaca karena periode atau kelas telah diarsipkan secara tetap.'
        );

        return view('admin.kelas-kuliah.edit', [
            'kelas' => $kelasKuliah,
            'rombel' => $kelasKuliah->rombel,
            'detailPilihan' => collect(),
            'versi' => $kelasKuliah->versiForm(),
        ]);
    }

    public function update(
        KelasKuliahRequest $request,
        KelasKuliah $kelasKuliah,
        SimpanKelasKuliah $action
    ): RedirectResponse {
        $kelas = $action->execute(
            (int) $request->user()->id,
            $request->validated(),
            $kelasKuliah
        );

        return to_route('admin.kelas-kuliah.show', $kelas)
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    private function rombelDenganRingkasan(): Builder
    {
        return Rombel::query()
            ->with([
                'periodeAkademik',
                'paketSemester' => function ($query): void {
                    $query->with('kurikulum.programStudi')->withCount('details');
                },
            ])
            ->withCount('kelasKuliah');
    }

    private function relations(): array
    {
        return [
            'rombel.periodeAkademik',
            'rombel.paketSemester.kurikulum.programStudi',
            'detailPaket.kurikulumMataKuliah.mataKuliah',
        ];
    }
}
