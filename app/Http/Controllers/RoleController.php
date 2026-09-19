<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    private const RESPONSE_FIELDS = [
        'id',
        'kode',
        'nama',
        'created_at',
        'updated_at',
    ];

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAccess($request);

        $roles = Role::query()
            ->select(self::RESPONSE_FIELDS)
            ->orderBy('id')
            ->get();

        if ($request->expectsJson()) {
            return $this->json([
                'message' => 'Daftar peran berhasil diambil.',
                'data' => $roles,
            ]);
        }

        return view('roles.index', compact('roles'));
    }

    public function show(Request $request, Role $role): View|JsonResponse
    {
        $this->authorizeAccess($request);

        if ($request->expectsJson()) {
            return $this->json([
                'message' => 'Detail peran berhasil diambil.',
                'data' => $role->only(self::RESPONSE_FIELDS),
            ]);
        }

        return view('roles.show', compact('role'));
    }

    public function edit(Request $request, Role $role): View
    {
        $this->authorizeAccess($request);

        return view('roles.edit', [
            'role' => $role,
            'version' => $this->roleVersion($role),
        ]);
    }

    public function update(
        Request $request,
        Role $role
    ): RedirectResponse|JsonResponse {
        $this->authorizeAccess($request);

        $input = $request->only([
            'nama',
            'kode',
            'version',
        ]);

        if (isset($input['nama']) && is_string($input['nama'])) {
            $input['nama'] = trim($input['nama']);
        }

        $data = Validator::make($input, [
            'nama' => ['required', 'string', 'min:3', 'max:80'],
            'kode' => ['prohibited'],
            'version' => [
                'required',
                'string',
                'regex:/\A[a-f0-9]{64}\z/',
            ],
        ], [
            'nama.required' => 'Nama peran wajib diisi.',
            'nama.min' => 'Nama peran minimal 3 karakter.',
            'nama.max' => 'Nama peran maksimal 80 karakter.',
            'kode.prohibited' => 'Kode peran tidak dapat diubah.',
            'version.required' => 'Muat ulang halaman sebelum menyimpan.',
            'version.regex' => 'Versi formulir tidak valid. Muat ulang halaman.',
        ])->validate();

        $updatedRole = DB::transaction(function () use ($role, $data): Role {
            $lockedRole = Role::query()
                ->lockForUpdate()
                ->findOrFail($role->getKey());

            if (! hash_equals(
                $this->roleVersion($lockedRole),
                $data['version']
            )) {
                throw ValidationException::withMessages([
                    'version' => 'Data sudah berubah sejak halaman dibuka. '
                        . 'Muat ulang halaman dan periksa kembali sebelum menyimpan.',
                ]);
            }

            $lockedRole->fill([
                'nama' => $data['nama'],
            ]);

            $lockedRole->save();

            return $lockedRole;
        }, 3);

        if ($request->expectsJson()) {
            return $this->json([
                'message' => 'Nama peran berhasil diperbarui.',
                'data' => $updatedRole->only(self::RESPONSE_FIELDS),
            ]);
        }

        return to_route('admin.roles.show', $updatedRole)
            ->with('success', 'Nama peran berhasil diperbarui.');
    }

    private function roleVersion(Role $role): string
    {
        return hash('sha256', json_encode([
            $role->getKey(),
            $role->getRawOriginal('nama'),
            $role->getRawOriginal('updated_at'),
        ], JSON_THROW_ON_ERROR));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAccess($request);

        return $this->json([
            'message' => 'Peran V1 sudah ditetapkan melalui migration.',
        ], 405);
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->authorizeAccess($request);

        return $this->json([
            'message' => 'Peran sistem tidak dapat dihapus.',
        ], 405);
    }

    private function authorizeAccess(Request $request): void
    {
        $actor = $request->user();

        abort_unless(
            $actor instanceof User,
            401,
            'Silakan masuk.'
        );

        $active = User::query()
            ->whereKey($actor->getKey())
            ->where('status', User::STATUS_AKTIF)
            ->exists();

        abort_unless(
            $active,
            403,
            'Akun Anda tidak aktif.'
        );

        Gate::forUser($actor)->authorize('kelola-peran');
    }

    private function json(
        array $payload,
        int $status = 200
    ): JsonResponse {
        return response()
            ->json($payload, $status)
            ->header('Cache-Control', 'no-store');
    }
}
