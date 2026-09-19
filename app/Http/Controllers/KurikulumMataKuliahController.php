<?php

namespace App\Http\Controllers;

use App\Http\Requests\KurikulumMataKuliahRequest;
use App\Models\Kurikulum;
use App\Models\KurikulumMataKuliah;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KurikulumMataKuliahController extends Controller
{
    public function index(Request $request, Kurikulum $kurikulum): View
    {
        $this->authorizeAccess($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'semester' => ['nullable', 'integer', 'between:1,32767'],
            'sifat' => [
                'nullable',
                'string',
                Rule::in(array_keys(KurikulumMataKuliah::SIFAT)),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = KurikulumMataKuliah::query()
            ->where('kurikulum_id', $kurikulum->getKey());

        $ringkasan = (clone $query)
            ->toBase()
            ->selectRaw('COUNT(*) AS jumlah, COALESCE(SUM(sks), 0) AS total_sks')
            ->first();

        $keyword = trim((string) ($filters['q'] ?? ''));

        if ($keyword !== '') {
            $query->whereHas(
                'mataKuliah',
                function (Builder $query) use ($keyword): void {
                    $query->where(function (Builder $query) use ($keyword): void {
                        $query->where('kode', 'like', "%{$keyword}%")
                            ->orWhere('nama', 'like', "%{$keyword}%");
                    });
                }
            );
        }

        if (isset($filters['semester'])) {
            $query->where(
                'semester_rekomendasi',
                (int) $filters['semester']
            );
        }

        if (isset($filters['sifat'])) {
            $query->where('sifat', $filters['sifat']);
        }

        $daftarDetail = $query
            ->with('mataKuliah:id,kode,nama,aktif')
            ->orderBy('semester_rekomendasi')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('kurikulum_mata_kuliah.index', array_merge(
            $this->context($kurikulum),
            [
                'daftarDetail' => $daftarDetail,
                'filters' => $filters,
                'ringkasan' => $ringkasan,
            ]
        ));
    }

    public function create(Request $request, Kurikulum $kurikulum): View
    {
        $this->authorizeAccess($request);

        $context = $this->context($kurikulum);

        abort_unless(
            $context['bisaTambah'],
            403,
            'Penambahan memerlukan kurikulum draf dan program studi aktif.'
        );

        return view(
            'kurikulum_mata_kuliah.create',
            $this->formData($kurikulum, new KurikulumMataKuliah())
        );
    }

    public function store(
        KurikulumMataKuliahRequest $request,
        Kurikulum $kurikulum
    ): RedirectResponse {
        return $this->persist($request, $kurikulum);
    }

    public function show(
        Request $request,
        Kurikulum $kurikulum,
        KurikulumMataKuliah $detail
    ): View {
        $this->authorizeAccess($request);

        return view(
            'kurikulum_mata_kuliah.show',
            $this->formData($kurikulum, $detail)
        );
    }

    public function edit(
        Request $request,
        Kurikulum $kurikulum,
        KurikulumMataKuliah $detail
    ): View {
        $this->authorizeAccess($request);

        abort_unless(
            $kurikulum->status === Kurikulum::DRAF,
            403,
            'Susunan kurikulum sudah terkunci.'
        );

        return view(
            'kurikulum_mata_kuliah.edit',
            $this->formData($kurikulum, $detail)
        );
    }

    public function update(
        KurikulumMataKuliahRequest $request,
        Kurikulum $kurikulum,
        KurikulumMataKuliah $detail
    ): RedirectResponse {
        return $this->persist($request, $kurikulum, $detail);
    }

    public function destroy(
        KurikulumMataKuliahRequest $request,
        Kurikulum $kurikulum,
        KurikulumMataKuliah $detail
    ): RedirectResponse {
        $data = $request->validated();

        try {
            DB::transaction(function () use (
                $request,
                $kurikulum,
                $detail,
                $data
            ): void {
                $parent = $this->lockContext($request, $kurikulum);

                $target = $parent->details()
                    ->whereKey($detail->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertVersion($parent, $target, $data['version']);

                $target->delete();
            }, 3);
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'detail' => 'Entri masih digunakan oleh data lain sehingga tidak dapat dikeluarkan.',
            ]);
        }

        return to_route(
            'admin.kurikulum.mata-kuliah.index',
            ['kurikulum' => $kurikulum]
        )->with('success', 'Mata kuliah berhasil dikeluarkan dari draf kurikulum.');
    }

    private function persist(
        KurikulumMataKuliahRequest $request,
        Kurikulum $kurikulum,
        ?KurikulumMataKuliah $detail = null
    ): RedirectResponse {
        $data = $request->validated();
        $creating = $detail === null;

        try {
            $saved = DB::transaction(function () use (
                $request,
                $kurikulum,
                $detail,
                $data,
                $creating
            ): KurikulumMataKuliah {
                $parent = $this->lockContext($request, $kurikulum);

                $target = $creating
                    ? new KurikulumMataKuliah()
                    : $parent->details()
                    ->whereKey($detail->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertVersion($parent, $target, $data['version']);

                $mataKuliahId = $creating
                    ? (int) $data['mata_kuliah_id']
                    : (int) $target->mata_kuliah_id;

                $mataKuliah = MataKuliah::query()
                    ->whereKey($mataKuliahId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $programStudi = ProgramStudi::query()
                    ->whereKey($parent->program_studi_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    (int) $mataKuliah->program_studi_id
                    !== (int) $parent->program_studi_id
                ) {
                    throw ValidationException::withMessages([
                        'mata_kuliah_id' => 'Program studi mata kuliah tidak sesuai dengan kurikulum.',
                    ]);
                }

                if (
                    $creating
                    && (! $mataKuliah->aktif || ! $programStudi->aktif)
                ) {
                    throw ValidationException::withMessages([
                        'mata_kuliah_id' => 'Penambahan memerlukan mata kuliah dan program studi aktif.',
                    ]);
                }

                if ($creating) {
                    $target->kurikulum()->associate($parent);
                    $target->mataKuliah()->associate($mataKuliah);
                }

                $target->fill(Arr::only($data, [
                    'sks',
                    'semester_rekomendasi',
                    'sifat',
                ]));

                $target->save();

                return $target;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'mata_kuliah_id' => 'Mata kuliah sudah tercantum dalam kurikulum ini.',
            ]);
        }

        return to_route('admin.kurikulum.mata-kuliah.show', [
            'kurikulum' => $kurikulum,
            'detail' => $saved,
        ])->with(
            'success',
            $creating
                ? 'Mata kuliah berhasil ditambahkan ke kurikulum.'
                : 'Rincian mata kuliah berhasil diperbarui.'
        );
    }

    private function authorizeAccess(Request $request): void
    {
        $user = $request->user('web');

        abort_unless($user instanceof User, 401);

        Gate::forUser($user)->authorize('kelola-kurikulum');
    }

    private function lockContext(
        Request $request,
        Kurikulum $kurikulum
    ): Kurikulum {
        $user = $request->user('web');

        abort_unless($user instanceof User, 401);

        $actor = User::query()
            ->whereKey($user->getKey())
            ->lockForUpdate()
            ->first();

        abort_unless($actor instanceof User, 403);

        Gate::forUser($actor)->authorize('kelola-kurikulum');

        $parent = Kurikulum::query()
            ->whereKey($kurikulum->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        if ($parent->status !== Kurikulum::DRAF) {
            throw ValidationException::withMessages([
                'kurikulum' => 'Susunan mata kuliah hanya dapat diubah pada kurikulum draf.',
            ]);
        }

        return $parent;
    }

    private function context(Kurikulum $kurikulum): array
    {
        $kurikulum->loadMissing('programStudi:id,kode,nama,aktif');

        $bisaUbah = $kurikulum->status === Kurikulum::DRAF;

        return [
            'kurikulum' => $kurikulum,
            'bisaUbah' => $bisaUbah,
            'bisaTambah' => $bisaUbah && $kurikulum->programStudi->aktif,
            'sifatOptions' => KurikulumMataKuliah::SIFAT,
        ];
    }

    private function formData(
        Kurikulum $kurikulum,
        KurikulumMataKuliah $detail
    ): array {
        $context = $this->context($kurikulum);
        $daftarMataKuliah = collect();

        if ($detail->exists) {
            $detail->loadMissing('mataKuliah:id,kode,nama,aktif');
        } elseif ($context['bisaTambah']) {
            $daftarMataKuliah = MataKuliah::query()
                ->aktif()
                ->where('program_studi_id', $kurikulum->program_studi_id)
                ->whereDoesntHave(
                    'kurikulumMataKuliah',
                    fn(Builder $query) => $query->where(
                        'kurikulum_id',
                        $kurikulum->getKey()
                    )
                )
                ->orderBy('nama')
                ->orderBy('id')
                ->get(['id', 'kode', 'nama']);
        }

        return array_merge($context, [
            'detail' => $detail,
            'daftarMataKuliah' => $daftarMataKuliah,
            'version' => $this->version($kurikulum, $detail),
        ]);
    }

    private function assertVersion(
        Kurikulum $kurikulum,
        KurikulumMataKuliah $detail,
        string $version
    ): void {
        if (! hash_equals($this->version($kurikulum, $detail), $version)) {
            throw ValidationException::withMessages([
                'version' => 'Data telah berubah. Muat ulang halaman sebelum melanjutkan.',
            ]);
        }
    }

    private function version(
        Kurikulum $kurikulum,
        KurikulumMataKuliah $detail
    ): string {
        $parentAttributes = $kurikulum->getRawOriginal();

        $detailAttributes = $detail->exists
            ? $detail->getRawOriginal()
            : [];

        ksort($parentAttributes);
        ksort($detailAttributes);

        return hash_hmac(
            'sha256',
            json_encode([
                'kurikulum' => $parentAttributes,
                'detail' => $detailAttributes,
            ], JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }
}
