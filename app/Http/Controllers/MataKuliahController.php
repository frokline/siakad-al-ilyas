<?php

namespace App\Http\Controllers;

use App\Http\Requests\MataKuliahRequest;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MataKuliahController extends Controller
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

            'aktif' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = MataKuliah::query()
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

        if (isset($filters['aktif'])) {
            $query->where('aktif', (bool) $filters['aktif']);
        }

        $daftarMataKuliah = $query
            ->orderBy('nama')
            ->orderBy('kode')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $daftarProdi = ProgramStudi::query()
            ->orderBy('nama')
            ->orderBy('id')
            ->get(['id', 'kode', 'nama', 'aktif']);

        return view('mata_kuliah.index', [
            'daftarMataKuliah' => $daftarMataKuliah,
            'daftarProdi' => $daftarProdi,
            'filters' => $filters,
            'statusOptions' => MataKuliah::STATUS,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view(
            'mata_kuliah.create',
            $this->formData(new MataKuliah())
        );
    }

    public function store(MataKuliahRequest $request): RedirectResponse
    {
        return $this->persist($request);
    }

    public function show(Request $request, MataKuliah $mataKuliah): View
    {
        $this->authorizeAccess($request);

        $mataKuliah->load('programStudi:id,kode,nama,aktif');

        return view('mata_kuliah.show', [
            'mataKuliah' => $mataKuliah,
        ]);
    }

    public function edit(Request $request, MataKuliah $mataKuliah): View
    {
        $this->authorizeAccess($request);

        return view(
            'mata_kuliah.edit',
            $this->formData($mataKuliah)
        );
    }

    public function update(
        MataKuliahRequest $request,
        MataKuliah $mataKuliah
    ): RedirectResponse {
        return $this->persist($request, $mataKuliah);
    }

    private function persist(
        MataKuliahRequest $request,
        ?MataKuliah $mataKuliah = null
    ): RedirectResponse {
        $data = $request->validated();
        $creating = $mataKuliah === null;

        try {
            $saved = DB::transaction(function () use (
                $request,
                $data,
                $mataKuliah,
                $creating
            ): MataKuliah {
                $this->lockActor($request);

                $target = $creating
                    ? new MataKuliah()
                    : MataKuliah::query()
                    ->whereKey($mataKuliah->getKey())
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

                $programStudiId = $creating
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

                $aktif = (bool) $data['aktif'];
                $mengaktifkan = ! $target->aktif && $aktif;

                if (
                    ! $programStudi->aktif
                    && ($creating || $mengaktifkan)
                ) {
                    throw ValidationException::withMessages([
                        'program_studi_id' => 'Pembuatan atau aktivasi mata kuliah memerlukan program studi aktif.',
                    ]);
                }

                if ($creating) {
                    $target->kode = $data['kode'];
                    $target->programStudi()->associate($programStudi);
                }

                $target->nama = $data['nama'];
                $target->aktif = $aktif;
                $target->save();

                return $target;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'kode' => 'Kode mata kuliah sudah digunakan pada program studi ini.',
            ]);
        }

        return to_route('admin.mata-kuliah.show', $saved)
            ->with(
                'success',
                $creating
                    ? 'Mata kuliah berhasil ditambahkan.'
                    : 'Mata kuliah berhasil diperbarui.'
            );
    }

    private function authorizeAccess(Request $request): void
    {
        $user = $request->user('web');

        abort_unless($user instanceof User, 401);

        Gate::forUser($user)->authorize('kelola-mata-kuliah');
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

        Gate::forUser($actor)->authorize('kelola-mata-kuliah');
    }

    private function formData(MataKuliah $mataKuliah): array
    {
        if ($mataKuliah->exists) {
            $mataKuliah->load('programStudi:id,kode,nama,aktif');
        }

        $daftarProdi = $mataKuliah->exists
            ? collect()
            : ProgramStudi::query()
            ->aktif()
            ->orderBy('nama')
            ->orderBy('id')
            ->get(['id', 'kode', 'nama', 'aktif']);

        return [
            'mataKuliah' => $mataKuliah,
            'daftarProdi' => $daftarProdi,
            'statusOptions' => MataKuliah::STATUS,
            'version' => $mataKuliah->exists
                ? $this->version($mataKuliah)
                : null,
        ];
    }

    private function version(MataKuliah $mataKuliah): string
    {
        $attributes = $mataKuliah->getRawOriginal();

        ksort($attributes);

        return hash_hmac(
            'sha256',
            json_encode($attributes, JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }
}
