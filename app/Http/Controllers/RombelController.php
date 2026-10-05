<?php

namespace App\Http\Controllers;

use App\Http\Requests\RombelRequest;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class RombelController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola-rombel');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'periode_akademik_id' => [
                'nullable',
                'integer',
                Rule::exists('periode_akademik', 'id'),
            ],
            'program_studi_id' => [
                'nullable',
                'integer',
                Rule::exists('program_studi', 'id'),
            ],
            'semester_studi' => ['nullable', 'integer', 'between:1,32767'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Rombel::query()->with($this->relations());
        $keyword = trim((string) ($filters['q'] ?? ''));

        if ($keyword !== '') {
            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('kode', 'like', '%' . $keyword . '%')
                    ->orWhereHas(
                        'paketSemester',
                        fn(Builder $paket): Builder => $paket
                            ->where('nama', 'like', '%' . $keyword . '%')
                            ->orWhereHas(
                                'kurikulum',
                                fn(Builder $kurikulum): Builder => $kurikulum
                                    ->where('kode', 'like', '%' . $keyword . '%')
                                    ->orWhere('nama', 'like', '%' . $keyword . '%')
                            )
                    );
            });
        }

        if (isset($filters['periode_akademik_id'])) {
            $query->where(
                'periode_akademik_id',
                (int) $filters['periode_akademik_id']
            );
        }

        if (isset($filters['program_studi_id'])) {
            $programStudiId = (int) $filters['program_studi_id'];

            $query->whereHas(
                'paketSemester.kurikulum',
                fn(Builder $kurikulum): Builder => $kurikulum
                    ->where('program_studi_id', $programStudiId)
            );
        }

        if (isset($filters['semester_studi'])) {
            $semester = (int) $filters['semester_studi'];

            $query->whereHas(
                'paketSemester',
                fn(Builder $paket): Builder => $paket
                    ->where('semester_studi', $semester)
            );
        }

        return view('rombel.index', [
            'daftarRombel' => $query
                ->orderByDesc('periode_akademik_id')
                ->orderBy('kode')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'daftarPeriode' => PeriodeAkademik::query()
                ->orderByDesc('mulai')
                ->orderByDesc('id')
                ->get(['id', 'kode', 'status']),
            'daftarProdi' => ProgramStudi::query()
                ->orderBy('nama')
                ->orderBy('id')
                ->get(['id', 'nama']),
            'statusPeriodeOptions' => PeriodeAkademik::STATUS,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('kelola-rombel');

        return view('rombel.create', [
            'rombel' => new Rombel(),
            'daftarPeriode' => PeriodeAkademik::query()
                ->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA)
                ->orderByDesc('mulai')
                ->orderByDesc('id')
                ->get(['id', 'kode', 'status']),
            'daftarPaket' => $this->paketLayak()->get(),
            'statusPeriodeOptions' => PeriodeAkademik::STATUS,
            'version' => null,
        ]);
    }

    public function store(RombelRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $rombel = DB::transaction(function () use ($request, $data): Rombel {
                $this->lockActor($request);

                $periode = PeriodeAkademik::query()
                    ->whereKey((int) $data['periode_akademik_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    in_array($periode->status, Rombel::STATUS_PERIODE_TERBUKA, true),
                    409,
                    'Periode akademik tidak lagi terbuka untuk pengelolaan rombel.'
                );

                $paket = $this->paketLayak()
                    ->whereKey((int) $data['paket_semester_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $rombel = new Rombel();
                $rombel->periodeAkademik()->associate($periode);
                $rombel->paketSemester()->associate($paket);
                $rombel->fill(Arr::only($data, ['kode', 'kapasitas']));
                $rombel->save();

                return $rombel;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'kode' => 'Kode rombel sudah digunakan pada periode akademik ini.',
            ]);
        }

        return to_route('admin.rombel.show', $rombel)
            ->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function show(Rombel $rombel): View
    {
        Gate::authorize('kelola-rombel');
        $rombel->load($this->relations());

        return view('rombel.show', [
            'rombel' => $rombel,
            'statusPeriodeOptions' => PeriodeAkademik::STATUS,
            'statusPaketOptions' => PaketSemester::STATUS,
        ]);
    }

    public function edit(Rombel $rombel): View
    {
        Gate::authorize('kelola-rombel');
        $rombel->load($this->relations());

        abort_unless(
            $rombel->dapatDiubah(),
            409,
            'Periode telah diarsipkan sehingga rombel tidak dapat diubah.'
        );

        return view('rombel.edit', [
            'rombel' => $rombel,
            'version' => $this->version($rombel),
        ]);
    }

    public function update(
        RombelRequest $request,
        Rombel $rombel
    ): RedirectResponse {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($request, $rombel, $data): void {
                $this->lockActor($request);

                $target = Rombel::query()
                    ->whereKey($rombel->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $target->load('periodeAkademik');

                if (! hash_equals($this->version($target), $data['version'])) {
                    throw ValidationException::withMessages([
                        'version' => 'Data rombel sudah berubah. Muat ulang formulir sebelum menyimpan.',
                    ]);
                }

                abort_unless(
                    $target->dapatDiubah(),
                    409,
                    'Periode telah diarsipkan sehingga rombel tidak dapat diubah.'
                );

                $target->fill(Arr::only($data, ['kode', 'kapasitas']));
                $target->save();
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'kode' => 'Kode rombel sudah digunakan pada periode akademik ini.',
            ]);
        }

        return to_route('admin.rombel.show', $rombel)
            ->with('success', 'Rombel berhasil diperbarui.');
    }

    private function paketLayak(): Builder
    {
        return PaketSemester::query()
            ->with('kurikulum.programStudi')
            ->where('status', PaketSemester::DITERBITKAN)
            ->has('details')
            ->whereHas('kurikulum', function (Builder $kurikulum): void {
                $kurikulum->where('status', 'aktif')
                    ->whereHas(
                        'programStudi',
                        fn(Builder $prodi): Builder => $prodi->where('aktif', true)
                    );
            })
            ->whereDoesntHave(
                'details.kurikulumMataKuliah.mataKuliah',
                fn(Builder $mataKuliah): Builder => $mataKuliah->where('aktif', false)
            )
            ->orderBy('kurikulum_id')
            ->orderBy('semester_studi')
            ->orderByDesc('versi')
            ->orderBy('id');
    }

    private function lockActor(Request $request): void
    {
        $actor = $request->user('web');
        abort_unless($actor instanceof User, 401);

        $freshActor = User::query()
            ->whereKey($actor->getAuthIdentifier())
            ->where('status', User::STATUS_AKTIF)
            ->lockForUpdate()
            ->firstOrFail();

        $hasRole = $freshActor->roles()
            ->where('roles.kode', Role::ADMIN_AKADEMIK)
            ->lockForUpdate()
            ->exists();

        abort_unless($hasRole, 403);
    }

    private function version(Rombel $rombel): string
    {
        return hash_hmac(
            'sha256',
            json_encode([
                $rombel->getKey(),
                $rombel->periode_akademik_id,
                $rombel->paket_semester_id,
                $rombel->kode,
                $rombel->kapasitas,
                $rombel->getRawOriginal('updated_at'),
            ], JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }

    private function relations(): array
    {
        return [
            'periodeAkademik',
            'paketSemester.kurikulum.programStudi',
        ];
    }
}
