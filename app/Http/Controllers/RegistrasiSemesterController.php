<?php

namespace App\Http\Controllers;

use App\Actions\SimpanRegistrasiSemester;
use App\Http\Requests\RegistrasiSemesterRequest;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\RegistrasiSemester;
use App\Models\RiwayatStudi;
use App\Models\Role;
use App\Models\Rombel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrasiSemesterController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola-registrasi-semester');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'periode_akademik_id' => ['nullable', 'integer', 'exists:periode_akademik,id'],
            'program_studi_id' => ['nullable', 'integer', 'exists:program_studi,id'],
            'semester_studi' => ['nullable', 'integer', 'between:1,32767'],
            'status' => ['nullable', Rule::in(array_keys(RegistrasiSemester::STATUS))],
        ]);

        $query = RegistrasiSemester::query()->with($this->relations());

        if ($q = trim((string) ($filter['q'] ?? ''))) {
            $query->where(function (Builder $query) use ($q): void {
                $query->whereHas('riwayatStudi.mahasiswa', function (Builder $query) use ($q): void {
                    $query->where('nim', 'like', "%{$q}%")
                        ->orWhereHas('user', fn(Builder $user) => $user->where('nama', 'like', "%{$q}%"));
                })->orWhereHas('rombel', fn(Builder $rombel) => $rombel->where('kode', 'like', "%{$q}%"));
            });
        }

        foreach (['periode_akademik_id', 'semester_studi', 'status'] as $field) {
            if (isset($filter[$field]) && $filter[$field] !== '') {
                $query->where($field, $filter[$field]);
            }
        }

        if (! empty($filter['program_studi_id'])) {
            $query->whereHas('riwayatStudi.kurikulum', function (Builder $query) use ($filter): void {
                $query->where('program_studi_id', $filter['program_studi_id']);
            });
        }

        return view('admin.registrasi-semester.index', [
            'registrasiDaftar' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'periodePilihan' => PeriodeAkademik::query()->orderByDesc('mulai')->get(),
            'prodiPilihan' => ProgramStudi::query()->orderBy('nama')->get(),
            'filter' => $filter,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('kelola-registrasi-semester');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'riwayat_studi_id' => ['nullable', 'integer', 'exists:riwayat_studi,id'],
            'periode_akademik_id' => [
                'nullable',
                'integer',
                Rule::exists('periode_akademik', 'id')->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA),
            ],
        ]);

        $periodePilihan = PeriodeAkademik::query()
            ->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA)
            ->orderByDesc('mulai')->orderByDesc('id')->get();

        $periodeId = (int) ($filter['periode_akademik_id'] ?? $periodePilihan->first()?->id ?? 0);
        $riwayat = null;
        $riwayatPilihan = null;
        $rombelPilihan = collect();
        $registrasiLama = null;
        $calon = $this->riwayatEligible();

        if (! empty($filter['riwayat_studi_id'])) {
            $riwayat = $calon->findOrFail((int) $filter['riwayat_studi_id']);
            $registrasiLama = $riwayat->registrasiSemester()
                ->where('periode_akademik_id', $periodeId)->first();

            if ($periodeId > 0 && $registrasiLama === null) {
                $rombelPilihan = $this->pilihanRombel($riwayat, $periodeId)->get();
            }
        } else {
            if ($q = trim((string) ($filter['q'] ?? ''))) {
                $calon->whereHas('mahasiswa', function (Builder $query) use ($q): void {
                    $query->where('nim', 'like', "%{$q}%")
                        ->orWhereHas('user', fn(Builder $user) => $user->where('nama', 'like', "%{$q}%"));
                });
            }

            $riwayatPilihan = $calon->orderByDesc('id')->paginate(15)->withQueryString();
        }

        return view('admin.registrasi-semester.create', [
            'registrasi' => new RegistrasiSemester(),
            'riwayat' => $riwayat,
            'riwayatPilihan' => $riwayatPilihan,
            'rombelPilihan' => $rombelPilihan,
            'periodePilihan' => $periodePilihan,
            'periodeId' => $periodeId,
            'registrasiLama' => $registrasiLama,
            'filter' => $filter,
        ]);
    }

    public function store(
        RegistrasiSemesterRequest $request,
        SimpanRegistrasiSemester $action
    ): RedirectResponse {
        $registrasi = $action->execute((int) $request->user()->id, $request->validated());

        return to_route('admin.registrasi-semester.show', $registrasi)
            ->with('success', 'Registrasi berhasil dibuat dengan status terdaftar.');
    }

    public function show(RegistrasiSemester $registrasiSemester): View
    {
        Gate::authorize('kelola-registrasi-semester');
        $registrasiSemester->load($this->relations());
        $this->muatKursi($registrasiSemester);

        return view('admin.registrasi-semester.show', [
            'registrasi' => $registrasiSemester,
            'riwayat' => $registrasiSemester->riwayatStudi,
        ]);
    }

    public function edit(RegistrasiSemester $registrasiSemester): View
    {
        Gate::authorize('kelola-registrasi-semester');
        $registrasiSemester->load($this->relations());
        abort_unless(
            $registrasiSemester->dapatDiubah(),
            409,
            'Periode telah diarsipkan atau riwayat studi telah ditutup.'
        );

        $this->muatKursi($registrasiSemester);

        return view('admin.registrasi-semester.edit', [
            'registrasi' => $registrasiSemester,
            'riwayat' => $registrasiSemester->riwayatStudi,
            'rombelPilihan' => $registrasiSemester->penempatanDapatDiubah()
                ? $this->pilihanRombel(
                    $registrasiSemester->riwayatStudi,
                    $registrasiSemester->periode_akademik_id,
                    $registrasiSemester->rombel_id
                )->get()
                : collect(),
            'versi' => $registrasiSemester->versiForm(),
        ]);
    }

    public function update(
        RegistrasiSemesterRequest $request,
        RegistrasiSemester $registrasiSemester,
        SimpanRegistrasiSemester $action
    ): RedirectResponse {
        $registrasi = $action->execute(
            (int) $request->user()->id,
            $request->validated(),
            $registrasiSemester
        );

        return to_route('admin.registrasi-semester.show', $registrasi)
            ->with('success', 'Registrasi semester berhasil diperbarui.');
    }

    private function riwayatEligible(): Builder
    {
        return RiwayatStudi::query()
            ->with(['mahasiswa.user:id,nama,status', 'kurikulum.programStudi'])
            ->where('status', RiwayatStudi::AKTIF)
            ->whereHas('kurikulum', function (Builder $query): void {
                $query->where('status', 'aktif')
                    ->whereHas('programStudi', fn(Builder $prodi) => $prodi->where('aktif', true));
            })
            ->whereHas('mahasiswa.user', function (Builder $query): void {
                $query->where('status', 'aktif')
                    ->whereHas('roles', fn(Builder $role) => $role->where('roles.kode', Role::MAHASISWA));
            });
    }

    private function pilihanRombel(
        RiwayatStudi $riwayat,
        int $periodeId,
        ?int $rombelLamaId = null
    ): Builder {
        return Rombel::query()
            ->with(['paketSemester.kurikulum.programStudi'])
            ->withCount([
                'registrasiSemester as kursi_terpakai' => fn(Builder $query) => $query->menempatiKursi(),
            ])
            ->where('periode_akademik_id', $periodeId)
            ->where(function (Builder $query) use ($riwayat, $rombelLamaId): void {
                $query->whereHas('paketSemester', function (Builder $paket) use ($riwayat): void {
                    $paket->where('kurikulum_id', $riwayat->kurikulum_id)
                        ->where('status', PaketSemester::DITERBITKAN)
                        ->has('details')
                        ->whereDoesntHave(
                            'details.kurikulumMataKuliah.mataKuliah',
                            fn(Builder $mk) => $mk->where('aktif', false)
                        )
                        ->whereHas('kurikulum', function (Builder $kurikulum): void {
                            $kurikulum->where('status', 'aktif')
                                ->whereHas('programStudi', fn(Builder $prodi) => $prodi->where('aktif', true));
                        });
                });

                // Penempatan lama tetap dapat dipilih meskipun paket diarsipkan.
                if ($rombelLamaId !== null) {
                    $query->orWhereKey($rombelLamaId);
                }
            })
            ->orderBy('kode');
    }

    private function muatKursi(RegistrasiSemester $registrasi): void
    {
        $registrasi->rombel->loadCount([
            'registrasiSemester as kursi_terpakai' => fn(Builder $query) => $query->menempatiKursi(),
        ]);
    }

    private function relations(): array
    {
        return [
            'riwayatStudi.mahasiswa.user:id,nama,status',
            'riwayatStudi.kurikulum.programStudi',
            'periodeAkademik',
            'rombel.paketSemester',
        ];
    }
}
