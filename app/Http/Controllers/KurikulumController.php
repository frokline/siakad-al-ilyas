<?php

namespace App\Http\Controllers;

use App\Http\Requests\KurikulumRequest;
use App\Models\Kurikulum;
use App\Models\ProgramStudi;
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

class KurikulumController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'program_studi_id' => [
                'bail',
                'nullable',
                'integer',
                'min:1',
                Rule::exists('program_studi', 'id'),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(Kurikulum::STATUS)),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Kurikulum::query()
            ->with('programStudi:id,kode,nama,aktif');

        $keyword = trim((string) ($filters['q'] ?? ''));

        if ($keyword !== '') {
            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('kode', 'like', "%{$keyword}%")
                    ->orWhere('nama', 'like', "%{$keyword}%");
            });
        }

        if (isset($filters['program_studi_id'])) {
            $query->where(
                'program_studi_id',
                (int) $filters['program_studi_id']
            );
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $daftarKurikulum = $query
            ->orderByDesc('tahun_berlaku')
            ->orderBy('kode')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $daftarProdi = ProgramStudi::query()
            ->orderBy('nama')
            ->orderBy('id')
            ->get(['id', 'kode', 'nama', 'aktif']);

        return view('kurikulum.index', [
            'daftarKurikulum' => $daftarKurikulum,
            'daftarProdi' => $daftarProdi,
            'filters' => $filters,
            'statusOptions' => Kurikulum::STATUS,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('kurikulum.create', $this->formData(new Kurikulum()));
    }

    public function store(KurikulumRequest $request): RedirectResponse
    {
        return $this->persist($request);
    }

    public function show(Request $request, Kurikulum $kurikulum): View
    {
        $this->authorizeAccess($request);

        $kurikulum->load('programStudi:id,kode,nama,aktif');

        return view('kurikulum.show', [
            'kurikulum' => $kurikulum,
            'statusOptions' => Kurikulum::STATUS,
        ]);
    }

    public function edit(Request $request, Kurikulum $kurikulum): View
    {
        $this->authorizeAccess($request);

        return view('kurikulum.edit', $this->formData($kurikulum));
    }

    public function update(
        KurikulumRequest $request,
        Kurikulum $kurikulum
    ): RedirectResponse {
        return $this->persist($request, $kurikulum);
    }

    private function persist(
        KurikulumRequest $request,
        ?Kurikulum $kurikulum = null
    ): RedirectResponse {
        $data = $request->validated();
        $creating = $kurikulum === null;

        try {
            $saved = DB::transaction(function () use (
                $request,
                $data,
                $kurikulum,
                $creating
            ): Kurikulum {
                $this->lockActor($request);

                $target = $creating
                    ? new Kurikulum()
                    : Kurikulum::query()
                    ->whereKey($kurikulum->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $target->exists
                    && ! hash_equals($this->version($target), $data['version'])
                ) {
                    throw ValidationException::withMessages([
                        'version' => 'Data telah berubah. Muat ulang formulir sebelum menyimpan kembali.',
                    ]);
                }

                $editable = $target->identitasDapatDiubah();

                $programStudiId = $editable
                    ? (int) $data['program_studi_id']
                    : (int) $target->program_studi_id;

                $programStudi = ProgramStudi::query()
                    ->whereKey($programStudiId)
                    ->lockForUpdate()
                    ->first();

                if (! $programStudi) {
                    throw ValidationException::withMessages([
                        'program_studi_id' => 'Program studi tidak ditemukan.',
                    ]);
                }

                $status = $creating
                    ? Kurikulum::DRAF
                    : $data['status'];

                $memilihProdiBaru = ! $target->exists
                    || (int) $target->program_studi_id !== $programStudiId;

                $mengaktifkan = $status === Kurikulum::AKTIF
                    && $target->status !== Kurikulum::AKTIF;

                if (
                    ! $programStudi->aktif
                    && ($memilihProdiBaru || $mengaktifkan)
                ) {
                    throw ValidationException::withMessages([
                        'program_studi_id' => 'Pembuatan atau aktivasi kurikulum memerlukan program studi aktif.',
                    ]);
                }

                if ($editable) {
                    $target->fill(Arr::only($data, [
                        'kode',
                        'nama',
                        'tahun_berlaku',
                    ]));

                    $target->programStudi()->associate($programStudi);
                }

                $target->status = $status;
                $target->save();

                return $target;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'kode' => 'Kode kurikulum sudah digunakan pada program studi ini.',
            ]);
        }

        return to_route('admin.kurikulum.show', $saved)
            ->with(
                'success',
                $creating
                    ? 'Kurikulum berhasil dibuat sebagai draf.'
                    : 'Kurikulum berhasil diperbarui.'
            );
    }

    private function authorizeAccess(Request $request): void
    {
        $user = $request->user('web');

        abort_unless($user instanceof User, 401);

        Gate::forUser($user)->authorize('kelola-kurikulum');
    }

    private function lockActor(Request $request): void
    {
        $user = $request->user('web');

        abort_unless($user instanceof User, 401);

        $actor = User::query()
            ->whereKey($user->getKey())
            ->lockForUpdate()
            ->first();

        abort_unless($actor instanceof User, 403);

        Gate::forUser($actor)->authorize('kelola-kurikulum');
    }

    private function formData(Kurikulum $kurikulum): array
    {
        if ($kurikulum->exists) {
            $kurikulum->load('programStudi:id,kode,nama,aktif');
        }

        $daftarProdi = ProgramStudi::query()
            ->where(function (Builder $query) use ($kurikulum): void {
                $query->where('aktif', true);

                if ($kurikulum->exists) {
                    $query->orWhere('id', $kurikulum->program_studi_id);
                }
            })
            ->orderBy('nama')
            ->orderBy('id')
            ->get(['id', 'kode', 'nama', 'aktif']);

        return [
            'prodiTerkunci' => $kurikulum->exists
                && $kurikulum->details()->exists(),
            'kurikulum' => $kurikulum,
            'daftarProdi' => $daftarProdi,
            'statusOptions' => $kurikulum->pilihanStatus(),
            'version' => $kurikulum->exists
                ? $this->version($kurikulum)
                : null,
        ];
    }

    private function version(Kurikulum $kurikulum): string
    {
        $attributes = $kurikulum->getRawOriginal();

        ksort($attributes);

        return hash_hmac(
            'sha256',
            json_encode($attributes, JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }
}
