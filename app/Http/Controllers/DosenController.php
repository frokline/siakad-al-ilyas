<?php

namespace App\Http\Controllers;

use App\Http\Requests\DosenRequest;
use App\Models\Dosen;
use App\Models\Role;
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

class DosenController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $statusAkunOptions = [
            User::STATUS_AKTIF => 'Aktif',
            User::STATUS_NONAKTIF => 'Nonaktif',
        ];

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(Dosen::STATUS)),
            ],
            'status_akun' => [
                'nullable',
                'string',
                Rule::in(array_keys($statusAkunOptions)),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Dosen::query()
            ->select([
                'id',
                'user_id',
                'kode_dosen',
                'nidn',
                'gelar',
                'status',
            ])
            ->with('user:id,nama,username,status');

        $keyword = trim((string) ($filters['q'] ?? ''));

        if ($keyword !== '') {
            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('kode_dosen', 'like', "%{$keyword}%")
                    ->orWhere('nidn', 'like', "%{$keyword}%")
                    ->orWhereHas(
                        'user',
                        function (Builder $query) use ($keyword): void {
                            $query->where(function (Builder $query) use ($keyword): void {
                                $query->where('nama', 'like', "%{$keyword}%")
                                    ->orWhere('username', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%");
                            });
                        }
                    );
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['status_akun'])) {
            $query->whereHas(
                'user',
                fn(Builder $query) => $query->where(
                    'status',
                    $filters['status_akun']
                )
            );
        }

        $daftarDosen = $query
            ->orderBy('kode_dosen')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('dosen.index', [
            'daftarDosen' => $daftarDosen,
            'filters' => $filters,
            'statusOptions' => Dosen::STATUS,
            'statusAkunOptions' => $statusAkunOptions,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('dosen.create', $this->formData(new Dosen()));
    }

    public function store(DosenRequest $request): RedirectResponse
    {
        return $this->persist($request);
    }

    public function show(Request $request, Dosen $dosen): View
    {
        $this->authorizeAccess($request);

        return view('dosen.show', $this->formData($dosen));
    }

    public function edit(Request $request, Dosen $dosen): View
    {
        $this->authorizeAccess($request);

        return view('dosen.edit', $this->formData($dosen));
    }

    public function update(
        DosenRequest $request,
        Dosen $dosen
    ): RedirectResponse {
        return $this->persist($request, $dosen);
    }

    private function persist(
        DosenRequest $request,
        ?Dosen $dosen = null
    ): RedirectResponse {
        $data = $request->validated();
        $creating = $dosen === null;

        try {
            $saved = DB::transaction(function () use (
                $request,
                $data,
                $dosen,
                $creating
            ): Dosen {
                $this->lockActor($request);

                $target = $creating
                    ? new Dosen()
                    : Dosen::query()
                    ->whereKey($dosen->getKey())
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

                $mengaktifkan = ! $target->isAktif()
                    && $data['status'] === Dosen::AKTIF;

                if ($creating || $mengaktifkan) {
                    $akunId = $creating
                        ? (int) $data['user_id']
                        : (int) $target->user_id;

                    $akun = $this->lockEligibleAccount(
                        $akunId,
                        $creating ? 'user_id' : 'status'
                    );

                    if ($creating) {
                        $profilLama = $akun->dosen()
                            ->lockForUpdate()
                            ->first(['id']);

                        if ($profilLama !== null) {
                            throw ValidationException::withMessages([
                                'user_id' => 'Akun sudah terhubung dengan dosen lain.',
                            ]);
                        }

                        $target->user()->associate($akun);
                    }
                }

                $target->fill(Arr::only($data, [
                    'kode_dosen',
                    'nidn',
                    'gelar',
                ]));

                $target->status = $data['status'];
                $target->save();

                return $target;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'dosen' => 'Kode dosen, NIDN, atau akun sudah digunakan oleh dosen lain. Periksa kembali data yang dimasukkan.',
            ]);
        }

        return to_route('admin.dosen.show', $saved)
            ->with(
                'success',
                $creating
                    ? 'Data dosen berhasil ditambahkan.'
                    : 'Data dosen berhasil diperbarui.'
            );
    }

    private function lockEligibleAccount(
        int $userId,
        string $errorKey
    ): User {
        $akun = User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->first();

        if (! $akun || ! $akun->isAktif()) {
            throw ValidationException::withMessages([
                $errorKey => 'Pembuatan atau aktivasi dosen memerlukan akun pengguna aktif.',
            ]);
        }

        // Pembacaan terkunci memeriksa peran terbaru.
        $peranDosen = $akun->roles()
            ->where('roles.kode', Role::DOSEN)
            ->lockForUpdate()
            ->first(['roles.id']);

        if ($peranDosen === null) {
            throw ValidationException::withMessages([
                $errorKey => 'Akun terhubung harus memiliki peran Dosen.',
            ]);
        }

        return $akun;
    }

    private function authorizeAccess(Request $request): void
    {
        $user = $request->user('web');

        abort_unless($user instanceof User, 401);

        Gate::forUser($user)->authorize('kelola-dosen');
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

        Gate::forUser($actor)->authorize('kelola-dosen');
    }

    private function formData(Dosen $dosen): array
    {
        $daftarPengguna = collect();
        $memilikiPeranDosen = false;

        if ($dosen->exists) {
            $dosen->load([
                'user:id,nama,username,email,telepon,status',
                'user.roles',
            ]);

            // Indikator tampilan, bukan pemeriksaan otorisasi.
            $memilikiPeranDosen = $dosen->user->roles
                ->contains('kode', Role::DOSEN);
        } else {
            $daftarPengguna = User::query()
                ->aktif()
                ->whereHas(
                    'roles',
                    fn(Builder $query) => $query->where(
                        'roles.kode',
                        Role::DOSEN
                    )
                )
                ->whereDoesntHave('dosen')
                ->orderBy('nama')
                ->orderBy('id')
                ->get(['id', 'nama', 'username']);
        }

        return [
            'dosen' => $dosen,
            'daftarPengguna' => $daftarPengguna,
            'memilikiPeranDosen' => $memilikiPeranDosen,
            'statusOptions' => Dosen::STATUS,
            'version' => $dosen->exists
                ? $this->version($dosen)
                : null,
        ];
    }

    private function version(Dosen $dosen): string
    {
        $attributes = $dosen->getRawOriginal();

        ksort($attributes);

        return hash_hmac(
            'sha256',
            json_encode($attributes, JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }
}
