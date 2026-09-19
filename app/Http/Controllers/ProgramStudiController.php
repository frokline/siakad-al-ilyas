<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgramStudiRequest;
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
use Illuminate\Validation\ValidationException;

class ProgramStudiController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'aktif' => ['nullable', 'in:0,1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = ProgramStudi::query();
        $search = $filters['q'] ?? '';

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('kode', 'like', '%' . $search . '%')
                    ->orWhere('nama', 'like', '%' . $search . '%')
                    ->orWhere('jenjang', 'like', '%' . $search . '%');
            });
        }

        if (isset($filters['aktif'])) {
            $query->where('aktif', (bool) $filters['aktif']);
        }

        $daftarProgramStudi = $query
            ->orderBy('nama')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'program_studi.index',
            compact('daftarProgramStudi', 'filters'),
        );
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('program_studi.create', [
            'programStudi' => new ProgramStudi(),
            'version' => null,
        ]);
    }

    public function store(ProgramStudiRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $programStudi = DB::transaction(
                function () use ($request, $data): ProgramStudi {
                    $this->lockActor($request);

                    $programStudi = new ProgramStudi();

                    $programStudi->fill(
                        Arr::only($data, ['kode', 'nama', 'jenjang']),
                    );

                    $programStudi->aktif = (bool) $data['aktif'];
                    $programStudi->save();

                    return $programStudi;
                },
                3,
            );
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'kode' => 'Kode program studi sudah digunakan.',
            ]);
        }

        return to_route('admin.program-studi.show', $programStudi)
            ->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function show(
        Request $request,
        ProgramStudi $programStudi,
    ): View {
        $this->authorizeAccess($request);

        return view('program_studi.show', compact('programStudi'));
    }

    public function edit(
        Request $request,
        ProgramStudi $programStudi,
    ): View {
        $this->authorizeAccess($request);

        return view('program_studi.edit', [
            'programStudi' => $programStudi,
            'version' => $this->version($programStudi),
        ]);
    }

    public function update(
        ProgramStudiRequest $request,
        ProgramStudi $programStudi,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            DB::transaction(function () use (
                $request,
                $programStudi,
                $data,
            ): void {
                $this->lockActor($request);

                $target = ProgramStudi::query()
                    ->whereKey($programStudi->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! hash_equals(
                    $this->version($target),
                    $data['version'],
                )) {
                    throw ValidationException::withMessages([
                        'version' => 'Data sudah berubah. Muat ulang halaman dan periksa kembali sebelum menyimpan.',
                    ]);
                }

                $target->fill(
                    Arr::only($data, ['kode', 'nama', 'jenjang']),
                );

                $target->aktif = (bool) $data['aktif'];
                $target->save();
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'kode' => 'Kode program studi sudah digunakan.',
            ]);
        }

        return to_route('admin.program-studi.show', $programStudi)
            ->with('success', 'Program studi berhasil diperbarui.');
    }

    private function authorizeAccess(Request $request): void
    {
        $actor = $request->user('web');

        abort_unless($actor instanceof User, 401);

        Gate::forUser($actor)->authorize('kelola-program-studi');
    }

    private function lockActor(Request $request): void
    {
        $actor = $request->user('web');

        abort_unless($actor instanceof User, 401);

        $freshActor = User::query()
            ->whereKey($actor->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        Gate::forUser($freshActor)->authorize('kelola-program-studi');
    }

    private function version(ProgramStudi $programStudi): string
    {
        return hash_hmac(
            'sha256',
            json_encode([
                $programStudi->getKey(),
                $programStudi->kode,
                $programStudi->nama,
                $programStudi->jenjang,
                $programStudi->aktif,
                $programStudi->getRawOriginal('updated_at'),
            ], JSON_THROW_ON_ERROR),
            (string) config('app.key'),
        );
    }
}
