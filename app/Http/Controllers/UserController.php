<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManageUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAccess($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:190'],
            'status' => [
                'nullable',
                Rule::in([
                    User::STATUS_AKTIF,
                    User::STATUS_NONAKTIF,
                ]),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = User::query()->with('roles:id,kode,nama');

        $search = $filters['q'] ?? '';

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        $users = $query
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        if ($request->expectsJson()) {
            return $this->json($users->toArray());
        }

        return view('users.index', compact('users', 'filters'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('users.create', $this->formData(new User()));
    }

    public function store(
        ManageUserRequest $request,
    ): RedirectResponse|JsonResponse {
        return $this->saveUser($request);
    }

    public function show(
        Request $request,
        User $user,
    ): View|JsonResponse {
        $this->authorizeAccess($request);

        $user->load('roles');
        $version = $this->version($user);

        if ($request->expectsJson()) {
            return $this->json([
                'data' => $user,
                'version' => $version,
            ]);
        }

        return view('users.show', compact('user', 'version'));
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeAccess($request);

        return view('users.edit', $this->formData($user));
    }

    public function update(
        ManageUserRequest $request,
        User $user,
    ): RedirectResponse|JsonResponse {
        return $this->saveUser($request, $user);
    }

    public function destroy(
        ManageUserRequest $request,
        User $user,
    ): RedirectResponse|JsonResponse {
        $data = $request->validated();

        DB::transaction(function () use ($request, $user, $data): void {
            $actor = $this->lockAdministrator(
                $request,
                $data['current_password'],
            );

            $target = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertVersion($target, $data['version']);

            if ($target->is($actor)) {
                throw ValidationException::withMessages([
                    'akun' => 'Anda tidak dapat menonaktifkan akun sendiri.',
                ]);
            }

            if ($target->isAktif()) {
                $target->status = User::STATUS_NONAKTIF;
                $target->remember_token = Str::random(60);
                $target->save();
            }
        }, 3);

        if ($request->expectsJson()) {
            return $this->json([
                'message' => 'Akun berhasil dinonaktifkan.',
            ]);
        }

        return to_route('admin.users.index')
            ->with('success', 'Akun berhasil dinonaktifkan.');
    }

    private function saveUser(
        ManageUserRequest $request,
        ?User $user = null,
    ): RedirectResponse|JsonResponse {
        $data = $request->validated();
        $creating = $user === null;

        $passwordHash = filled($data['password'] ?? null)
            ? Hash::make($data['password'])
            : null;

        try {
            $saved = DB::transaction(
                function () use (
                    $request,
                    $user,
                    $data,
                    $passwordHash,
                ): User {
                    $actor = $this->lockAdministrator(
                        $request,
                        $data['current_password'],
                    );

                    $target = $user === null
                        ? new User()
                        : User::query()
                        ->whereKey($user->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($target->exists) {
                        $this->assertVersion($target, $data['version']);
                    }

                    $roleIds = array_map('intval', $data['roles']);
                    sort($roleIds, SORT_NUMERIC);

                    $previousRoles = $target->exists
                        ? array_map('intval', $target->roles->modelKeys())
                        : [];

                    sort($previousRoles, SORT_NUMERIC);

                    $adminRoleId = (int) Role::query()
                        ->where('kode', Role::ADMIN_AKADEMIK)
                        ->value('id');

                    if (
                        $target->is($actor)
                        && (
                            $data['status'] !== User::STATUS_AKTIF
                            || ! in_array($adminRoleId, $roleIds, true)
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'akun' => 'Akun sendiri harus tetap aktif dan memiliki peran Admin Akademik.',
                        ]);
                    }

                    $target->fill([
                        'nama' => $data['nama'],
                        'username' => $data['username'],
                        'email' => $data['email'],
                        'telepon' => $data['telepon'] ?? null,
                    ]);

                    $target->status = $data['status'];

                    if ($passwordHash !== null) {
                        $target->password_hash = $passwordHash;
                    }

                    if (
                        $target->isDirty([
                            'username',
                            'email',
                            'status',
                            'password_hash',
                        ])
                        || $previousRoles !== $roleIds
                    ) {
                        $target->remember_token = Str::random(60);
                    }

                    $target->save();
                    $target->roles()->sync($roleIds);

                    return $target->refresh()->load('roles');
                },
                3,
            );
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'akun' => 'Username atau email sudah digunakan. Periksa kembali keduanya.',
            ]);
        }

        if (
            ! $creating
            && $passwordHash !== null
            && $saved->is($request->user('web'))
        ) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return $this->json([
                    'message' => 'Kata sandi berhasil diubah. Silakan masuk kembali.',
                    'redirect_url' => route('login'),
                ]);
            }

            return to_route('login')->with(
                'success',
                'Kata sandi berhasil diubah. Silakan masuk kembali.',
            );
        }

        $message = $creating
            ? 'Pengguna berhasil ditambahkan.'
            : 'Pengguna berhasil diperbarui.';

        if ($request->expectsJson()) {
            return $this->json([
                'message' => $message,
                'data' => $saved,
                'version' => $this->version($saved),
            ], $creating ? 201 : 200);
        }

        return to_route('admin.users.show', $saved)
            ->with('success', $message);
    }

    private function authorizeAccess(Request $request): void
    {
        $actor = $request->user('web');

        abort_unless($actor instanceof User, 401);

        Gate::forUser($actor)->authorize('kelola-pengguna');
    }

    private function lockAdministrator(
        Request $request,
        string $password,
    ): User {
        // Semua perubahan akun memakai urutan penguncian yang sama.
        Role::query()
            ->where('kode', Role::ADMIN_AKADEMIK)
            ->lockForUpdate()
            ->firstOrFail();

        $actor = User::query()
            ->whereKey($request->user('web')->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        Gate::forUser($actor)->authorize('kelola-pengguna');

        if (! Hash::check($password, $actor->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi admin tidak sesuai.',
            ]);
        }

        return $actor;
    }

    private function formData(User $user): array
    {
        if ($user->exists) {
            $user->load('roles');
        }

        return [
            'user' => $user,
            'roles' => Role::query()
                ->whereIn('kode', Role::KODE_SISTEM)
                ->orderBy('id')
                ->get(['id', 'kode', 'nama']),
            'version' => $user->exists ? $this->version($user) : null,
        ];
    }

    private function version(User $user): string
    {
        $attributes = $user->getRawOriginal();
        ksort($attributes);

        $roleIds = array_map('intval', $user->roles->modelKeys());
        sort($roleIds, SORT_NUMERIC);

        return hash_hmac(
            'sha256',
            json_encode([$attributes, $roleIds], JSON_THROW_ON_ERROR),
            (string) config('app.key'),
        );
    }

    private function assertVersion(User $user, string $version): void
    {
        $user->load('roles');

        if (! hash_equals($this->version($user), $version)) {
            throw ValidationException::withMessages([
                'version' => 'Data sudah berubah. Muat ulang halaman, lalu periksa kembali sebelum menyimpan.',
            ]);
        }
    }

    private function json(array $data, int $status = 200): JsonResponse
    {
        return response()
            ->json($data, $status)
            ->header('Cache-Control', 'no-store, private');
    }
}
