<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaketSemesterRequest;
use App\Models\DetailPaket;
use App\Models\Kurikulum;
use App\Models\KurikulumMataKuliah;
use App\Models\MataKuliah;
use App\Models\PaketSemester;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaketSemesterController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola-paket-semester');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'kurikulum_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::exists('kurikulum', 'id'),
            ],
            'semester_studi' => [
                'nullable',
                'integer',
                'between:1,32767',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(PaketSemester::STATUS)),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = PaketSemester::query()
            ->with([
                'kurikulum:id,program_studi_id,kode,nama',
                'kurikulum.programStudi:id,nama',
            ])
            ->withCount('details');

        $search = trim($filters['q'] ?? '');

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function (Builder $query) use ($like): void {
                $query->where('nama', 'like', $like)
                    ->orWhereHas(
                        'kurikulum',
                        fn(Builder $kurikulum) =>
                        $kurikulum->where('kode', 'like', $like)
                    );
            });
        }

        foreach (['kurikulum_id', 'semester_studi', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        return view('paket_semester.index', [
            'daftarPaket' => $query
                ->orderBy('kurikulum_id')
                ->orderBy('semester_studi')
                ->orderByDesc('versi')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString(),

            'daftarKurikulum' => Kurikulum::query()
                ->with('programStudi:id,nama')
                ->orderBy('kode')
                ->get(['id', 'program_studi_id', 'kode', 'nama']),

            'filters' => $filters,
            'statusOptions' => PaketSemester::STATUS,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('kelola-paket-semester');

        return view('paket_semester.create', [
            'paketSemester' => new PaketSemester(),

            'daftarKurikulum' => Kurikulum::query()
                ->with('programStudi:id,nama')
                ->where('status', Kurikulum::AKTIF)
                ->whereHas(
                    'programStudi',
                    fn(Builder $prodi) => $prodi->where('aktif', true)
                )
                ->orderBy('kode')
                ->get(['id', 'program_studi_id', 'kode', 'nama']),
        ]);
    }

    public function store(PaketSemesterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $saved = $this->transaction(function () use (
            $request,
            $data
        ): PaketSemester {
            $this->lockActor($request);

            // Kunci kurikulum juga menyeragamkan pemberian nomor versi.
            $kurikulum = $this->lockKurikulum(
                (int) $data['kurikulum_id'],
                true
            );

            $terakhir = PaketSemester::query()
                ->where('kurikulum_id', $kurikulum->getKey())
                ->where('semester_studi', (int) $data['semester_studi'])
                ->orderByDesc('versi')
                ->lockForUpdate()
                ->first(['versi']);

            $versi = ($terakhir?->versi ?? 0) + 1;

            if ($versi > 32767) {
                $this->invalid(
                    'paket_semester',
                    'Batas nomor versi paket telah tercapai.'
                );
            }

            $paket = new PaketSemester();

            $paket->kurikulum()->associate($kurikulum);
            $paket->semester_studi = (int) $data['semester_studi'];
            $paket->versi = $versi;
            $paket->nama = $data['nama'];
            $paket->status = PaketSemester::DRAF;

            $paket->save();

            return $paket;
        });

        return redirect()
            ->route('admin.paket-semester.edit', $saved)
            ->with(
                'success',
                'Paket versi ' . $saved->versi . ' berhasil dibuat. '
                    . 'Pilih mata kuliah, lalu simpan.'
            );
    }

    public function show(PaketSemester $paketSemester): View
    {
        Gate::authorize('kelola-paket-semester');

        return view(
            'paket_semester.show',
            $this->viewData($paketSemester)
        );
    }

    public function edit(PaketSemester $paketSemester): View
    {
        Gate::authorize('kelola-paket-semester');

        abort_unless(
            $paketSemester->isDraf(),
            409,
            'Hanya paket draf yang dapat diedit.'
        );

        $data = $this->viewData($paketSemester);

        $data['daftarMataKuliah'] = KurikulumMataKuliah::query()
            ->where('kurikulum_id', $paketSemester->kurikulum_id)
            ->with('mataKuliah:id,kode,nama,aktif')
            ->orderBy('semester_rekomendasi')
            ->orderBy('id')
            ->get();

        return view('paket_semester.edit', $data);
    }

    public function update(
        PaketSemesterRequest $request,
        PaketSemester $paketSemester
    ): RedirectResponse {
        $data = $request->validated();

        $saved = $this->transaction(function () use (
            $request,
            $data,
            $paketSemester
        ): PaketSemester {
            $this->lockActor($request);

            $kurikulum = $this->lockKurikulum(
                $paketSemester->kurikulum_id,
                true
            );

            $paket = $this->lockPaket($paketSemester, $kurikulum);

            $details = $paket->details()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $this->assertVersion($paket, $details, $data['version']);

            if (! $paket->isDraf()) {
                $this->invalid(
                    'paket_semester',
                    'Paket sudah diterbitkan atau diarsipkan.'
                );
            }

            $selectedIds = array_map(
                static fn($id): int => (int) $id,
                $data['mata_kuliah_ids']
            );

            sort($selectedIds);

            $existingIds = $details
                ->pluck('kurikulum_mata_kuliah_id')
                ->all();

            $addedIds = array_values(
                array_diff($selectedIds, $existingIds)
            );

            $selected = $this->lockPilihanMataKuliah(
                $kurikulum,
                $selectedIds,
                $addedIds
            );

            $paket->nama = $data['nama'];
            $paket->save();

            foreach ($details as $detail) {
                if (! in_array(
                    $detail->kurikulum_mata_kuliah_id,
                    $selectedIds,
                    true
                )) {
                    $detail->delete();
                }
            }

            foreach ($selected as $mataKuliah) {
                if (in_array($mataKuliah->getKey(), $existingIds, true)) {
                    continue;
                }

                $detail = new DetailPaket();

                $detail->paketSemester()->associate($paket);
                $detail->kurikulumMataKuliah()->associate($mataKuliah);

                $detail->save();
            }

            $paket->touch();

            return $paket;
        });

        return redirect()
            ->route('admin.paket-semester.show', $saved)
            ->with('success', 'Paket dan susunan mata kuliah berhasil disimpan.');
    }

    public function terbitkan(
        PaketSemesterRequest $request,
        PaketSemester $paketSemester
    ): RedirectResponse {
        return $this->changeStatus(
            $request,
            $paketSemester,
            PaketSemester::DITERBITKAN
        );
    }

    public function arsipkan(
        PaketSemesterRequest $request,
        PaketSemester $paketSemester
    ): RedirectResponse {
        return $this->changeStatus(
            $request,
            $paketSemester,
            PaketSemester::ARSIP
        );
    }

    private function changeStatus(
        PaketSemesterRequest $request,
        PaketSemester $paketSemester,
        string $status
    ): RedirectResponse {
        $data = $request->validated();

        $saved = $this->transaction(function () use (
            $request,
            $data,
            $paketSemester,
            $status
        ): PaketSemester {
            $this->lockActor($request);

            $kurikulum = $this->lockKurikulum(
                $paketSemester->kurikulum_id,
                $status === PaketSemester::DITERBITKAN
            );

            $paket = $this->lockPaket($paketSemester, $kurikulum);

            $details = $paket->details()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $this->assertVersion($paket, $details, $data['version']);

            if ($status === PaketSemester::DITERBITKAN) {
                if (! $paket->isDraf()) {
                    $this->invalid(
                        'paket_semester',
                        'Hanya paket draf yang dapat diterbitkan.'
                    );
                }

                if ($details->isEmpty()) {
                    $this->invalid(
                        'paket_semester',
                        'Tambahkan mata kuliah sebelum menerbitkan paket.'
                    );
                }

                $ids = $details
                    ->pluck('kurikulum_mata_kuliah_id')
                    ->all();

                $this->lockPilihanMataKuliah(
                    $kurikulum,
                    $ids,
                    $ids
                );
            } elseif ($paket->isArsip()) {
                $this->invalid(
                    'paket_semester',
                    'Paket sudah diarsipkan.'
                );
            }

            $paket->status = $status;
            $paket->save();

            return $paket;
        });

        return redirect()
            ->route('admin.paket-semester.show', $saved)
            ->with(
                'success',
                $status === PaketSemester::DITERBITKAN
                    ? 'Paket berhasil diterbitkan.'
                    : 'Paket berhasil diarsipkan.'
            );
    }

    private function lockActor(Request $request): void
    {
        $actor = User::query()
            ->whereKey($request->user()->getAuthIdentifier())
            ->lockForUpdate()
            ->first();

        abort_if($actor === null, 403);

        Gate::forUser($actor)->authorize('kelola-paket-semester');
    }

    private function lockKurikulum(
        int $kurikulumId,
        bool $harusAktif
    ): Kurikulum {
        $kurikulum = Kurikulum::query()
            ->whereKey($kurikulumId)
            ->lockForUpdate()
            ->first();

        if ($kurikulum === null) {
            $this->invalid('kurikulum_id', 'Kurikulum tidak tersedia.');
        }

        $prodi = ProgramStudi::query()
            ->whereKey($kurikulum->program_studi_id)
            ->lockForUpdate()
            ->first();

        if ($prodi === null) {
            $this->invalid('kurikulum_id', 'Program studi tidak tersedia.');
        }

        if (
            $harusAktif
            && (
                $kurikulum->status !== Kurikulum::AKTIF
                || ! $prodi->aktif
            )
        ) {
            $this->invalid(
                'kurikulum_id',
                'Kurikulum dan program studi harus aktif.'
            );
        }

        return $kurikulum;
    }

    private function lockPaket(
        PaketSemester $bound,
        Kurikulum $kurikulum
    ): PaketSemester {
        return PaketSemester::query()
            ->whereKey($bound->getKey())
            ->where('kurikulum_id', $kurikulum->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockPilihanMataKuliah(
        Kurikulum $kurikulum,
        array $ids,
        array $wajibAktif
    ): Collection {
        if ($ids === []) {
            return new Collection();
        }

        $items = KurikulumMataKuliah::query()
            ->where('kurikulum_id', $kurikulum->getKey())
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($items->count() !== count($ids)) {
            $this->invalid(
                'mata_kuliah_ids',
                'Ada pilihan mata kuliah yang tidak tersedia '
                    . 'atau berasal dari kurikulum berbeda.'
            );
        }

        $mataKuliah = MataKuliah::query()
            ->whereIn('id', $items->pluck('mata_kuliah_id')->unique())
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id', 'program_studi_id', 'kode', 'aktif'])
            ->keyBy('id');

        foreach ($items as $item) {
            $master = $mataKuliah->get($item->mata_kuliah_id);

            if (
                $master === null
                || (int) $master->program_studi_id
                !== (int) $kurikulum->program_studi_id
            ) {
                $this->invalid(
                    'mata_kuliah_ids',
                    'Data mata kuliah tidak sesuai dengan program studi paket.'
                );
            }

            if (
                in_array($item->getKey(), $wajibAktif, true)
                && ! $master->aktif
            ) {
                $this->invalid(
                    'mata_kuliah_ids',
                    'Mata kuliah ' . $master->kode . ' sudah nonaktif.'
                );
            }
        }

        return $items;
    }

    private function viewData(PaketSemester $paketSemester): array
    {
        $paketSemester->load([
            'kurikulum:id,program_studi_id,kode,nama,status',
            'kurikulum.programStudi:id,nama,aktif',
            'details' => fn($query) => $query->orderBy('id'),
            'details.kurikulumMataKuliah',
            'details.kurikulumMataKuliah.mataKuliah:id,kode,nama,aktif',
        ]);

        $indukAktif = $paketSemester->kurikulum->status === Kurikulum::AKTIF
            && $paketSemester->kurikulum->programStudi->aktif;

        $adaMataKuliahNonaktif = $paketSemester->details->contains(
            fn(DetailPaket $detail): bool =>
            ! $detail->kurikulumMataKuliah->mataKuliah->aktif
        );

        return [
            'paketSemester' => $paketSemester,
            'statusOptions' => PaketSemester::STATUS,
            'indukAktif' => $indukAktif,
            'adaMataKuliahNonaktif' => $adaMataKuliahNonaktif,

            'dapatTerbit' => $paketSemester->isDraf()
                && $indukAktif
                && $paketSemester->details->isNotEmpty()
                && ! $adaMataKuliahNonaktif,

            'totalSks' => $paketSemester->details->sum(
                fn(DetailPaket $detail): float =>
                (float) $detail->kurikulumMataKuliah->sks
            ),

            'version' => $this->version(
                $paketSemester,
                $paketSemester->details
            ),
        ];
    }

    private function assertVersion(
        PaketSemester $paket,
        Collection $details,
        string $version
    ): void {
        if (! hash_equals($this->version($paket, $details), $version)) {
            $this->invalid(
                'version',
                'Paket atau susunan mata kuliah sudah berubah. '
                    . 'Muat ulang halaman dan periksa kembali.'
            );
        }
    }

    private function version(
        PaketSemester $paket,
        Collection $details
    ): string {
        $parent = $paket->getRawOriginal();

        ksort($parent);

        $children = $details
            ->sortBy('id')
            ->map(function (DetailPaket $detail): array {
                $attributes = $detail->getRawOriginal();

                ksort($attributes);

                return $attributes;
            })
            ->values()
            ->all();

        return hash_hmac(
            'sha256',
            json_encode(
                ['paket' => $parent, 'details' => $children],
                JSON_THROW_ON_ERROR
            ),
            (string) config('app.key')
        );
    }

    private function transaction(callable $callback): PaketSemester
    {
        try {
            return DB::transaction($callback, 3);
        } catch (UniqueConstraintViolationException) {
            $this->invalid(
                'paket_semester',
                'Versi paket atau pilihan mata kuliah sudah tercatat. '
                    . 'Muat ulang halaman.'
            );
        } catch (QueryException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1451) {
                $this->invalid(
                    'paket_semester',
                    'Data sudah digunakan oleh catatan akademik lain '
                        . 'sehingga tidak dapat dihapus dari paket.'
                );
            }

            throw $exception;
        }
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
