<?php

namespace App\Http\Controllers;

use App\Http\Requests\MahasiswaRequest;
use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola-mahasiswa');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status_akun' => [
                'nullable',
                Rule::in(['aktif', 'nonaktif']),
            ],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $kata = trim($filter['q'] ?? '');

        $daftar = Mahasiswa::query()
            ->with([
                'user:id,nama,email,status',
                'riwayatAktif.kurikulum.programStudi',
            ])
            ->when($kata !== '', function (Builder $query) use ($kata): void {
                $query->where(function (Builder $pencarian) use ($kata): void {
                    $pencarian->where('nim', 'like', '%' . $kata . '%')
                        ->orWhereHas('user', function (Builder $akun) use ($kata): void {
                            $akun->where(function (Builder $identitas) use ($kata): void {
                                $identitas->where('nama', 'like', '%' . $kata . '%')
                                    ->orWhere('email', 'like', '%' . $kata . '%');
                            });
                        });
                });
            })
            ->when(
                $filter['status_akun'] ?? null,
                function (Builder $query, string $status): void {
                    $query->whereHas(
                        'user',
                        fn(Builder $akun) => $akun->where('status', $status)
                    );
                }
            )
            ->orderBy('nim')
            ->orderBy('id')
            ->paginate(20)
            ->appends(Arr::only($filter, ['q', 'status_akun']));

        return view('mahasiswa.index', compact('daftar', 'filter'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('kelola-mahasiswa');

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $akun = null;

        if (! empty($filter['user_id'])) {
            $akun = $this->akunTersedia()
                ->whereKey($filter['user_id'])
                ->first();
        }

        $akunDaftar = null;

        if ($akun === null) {
            $kata = trim($filter['q'] ?? '');

            $akunDaftar = $this->akunTersedia()
                ->when($kata !== '', function (Builder $query) use ($kata): void {
                    $query->where(function (Builder $pencarian) use ($kata): void {
                        $pencarian->where('nama', 'like', '%' . $kata . '%')
                            ->orWhere('email', 'like', '%' . $kata . '%')
                            ->orWhere('username', 'like', '%' . $kata . '%');
                    });
                })
                ->orderBy('nama')
                ->orderBy('id')
                ->paginate(10)
                ->appends(Arr::only($filter, ['q']));
        }

        return view('mahasiswa.create', [
            'mahasiswa' => new Mahasiswa(),
            'akun' => $akun,
            'akunDaftar' => $akunDaftar,
            'filter' => $filter,
        ]);
    }

    public function store(MahasiswaRequest $request): RedirectResponse
    {
        $mahasiswa = $this->simpan($request);

        return to_route('admin.mahasiswa.show', $mahasiswa)
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function show(Mahasiswa $mahasiswa): View
    {
        Gate::authorize('kelola-mahasiswa');

        $mahasiswa->load('user');

        $riwayatDaftar = $mahasiswa->riwayatStudi()
            ->with('kurikulum.programStudi')
            ->orderByDesc('angkatan')
            ->orderByDesc('id')
            ->paginate(10);

        return view('mahasiswa.show', compact(
            'mahasiswa',
            'riwayatDaftar'
        ));
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        Gate::authorize('kelola-mahasiswa');

        $mahasiswa->load('user');

        abort_if($mahasiswa->user === null, 404);

        return view('mahasiswa.edit', [
            'mahasiswa' => $mahasiswa,
            'akun' => $mahasiswa->user,
        ]);
    }

    public function update(
        MahasiswaRequest $request,
        Mahasiswa $mahasiswa
    ): RedirectResponse {
        $hasil = $this->simpan($request, $mahasiswa);

        return to_route('admin.mahasiswa.show', $hasil)
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    private function akunTersedia(): Builder
    {
        return User::query()
            ->select(['id', 'nama', 'email', 'username', 'status'])
            ->where('status', 'aktif')
            ->whereHas(
                'roles',
                fn(Builder $role) => $role->where(
                    'roles.kode',
                    Role::MAHASISWA
                )
            )
            ->whereDoesntHave('mahasiswa');
    }

    private function simpan(
        MahasiswaRequest $request,
        ?Mahasiswa $mahasiswa = null
    ): Mahasiswa {
        $data = $request->validated();
        $pelakuId = (int) $request->user()->getAuthIdentifier();

        $pemilikId = $mahasiswa !== null
            ? (int) $mahasiswa->user_id
            : (int) $data['user_id'];

        try {
            return DB::transaction(function () use (
                $data,
                $pelakuId,
                $pemilikId,
                $mahasiswa
            ): Mahasiswa {
                // Urutan penguncian konsisten untuk pengguna yang terlibat.
                $pengguna = User::query()
                    ->whereIn('id', array_unique([$pelakuId, $pemilikId]))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $pelaku = $pengguna->get($pelakuId);

                abort_if($pelaku === null, 403);

                Gate::forUser($pelaku)->authorize('kelola-mahasiswa');

                $akun = $pengguna->get($pemilikId);

                if ($akun === null) {
                    throw ValidationException::withMessages([
                        'user_id' => 'Akun pengguna tidak lagi tersedia.',
                    ]);
                }

                if ($mahasiswa !== null) {
                    $record = Mahasiswa::query()
                        ->whereKey($mahasiswa->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        (int) $record->user_id !== $pemilikId
                        || ! hash_equals(
                            $record->versiForm(),
                            $data['versi']
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'versi' => 'Data telah berubah sejak formulir dibuka. Muat ulang formulir sebelum menyimpan.',
                        ]);
                    }
                } else {
                    $memilikiPeran = $akun->roles()
                        ->where('roles.kode', Role::MAHASISWA)
                        ->exists();

                    if ($akun->status !== 'aktif' || ! $memilikiPeran) {
                        throw ValidationException::withMessages([
                            'user_id' => 'Pilih akun aktif yang memiliki peran mahasiswa.',
                        ]);
                    }

                    if (Mahasiswa::query()
                        ->where('user_id', $akun->id)
                        ->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'user_id' => 'Akun ini sudah memiliki data mahasiswa.',
                        ]);
                    }

                    $record = new Mahasiswa();
                    $record->user()->associate($akun);
                }

                $record->fill(Arr::only($data, [
                    'nim',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'alamat',
                ]));

                $record->save();

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (
                $mahasiswa === null
                && Mahasiswa::query()->where('user_id', $pemilikId)->exists()
            ) {
                throw ValidationException::withMessages([
                    'user_id' => 'Akun ini sudah didaftarkan sebagai mahasiswa. Pilih akun lain.',
                ]);
            }

            throw ValidationException::withMessages([
                'nim' => 'NIM sudah digunakan mahasiswa lain.',
            ]);
        }
    }
}
