<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodeAkademikRequest;
use App\Models\PeriodeAkademik;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PeriodeAkademikController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:30'],
            'status' => [
                'nullable',
                Rule::in(array_keys(PeriodeAkademik::STATUS)),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = PeriodeAkademik::query();

        if (filled($filters['q'] ?? null)) {
            $query->where(
                'kode',
                'like',
                '%' . Str::upper(trim($filters['q'])) . '%',
            );
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        $daftarPeriode = $query
            ->orderByDesc('tahun_mulai')
            ->orderByDesc('mulai')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('periode_akademik.index', [
            'daftarPeriode' => $daftarPeriode,
            'filters' => $filters,
            'statusOptions' => PeriodeAkademik::STATUS,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view(
            'periode_akademik.create',
            $this->viewData(new PeriodeAkademik()),
        );
    }

    public function store(
        PeriodeAkademikRequest $request,
    ): RedirectResponse {
        return $this->persist($request);
    }

    public function show(
        Request $request,
        PeriodeAkademik $periodeAkademik,
    ): View {
        $this->authorizeAccess($request);

        return view(
            'periode_akademik.show',
            $this->viewData($periodeAkademik),
        );
    }

    public function edit(
        Request $request,
        PeriodeAkademik $periodeAkademik,
    ): View {
        $this->authorizeAccess($request);

        return view(
            'periode_akademik.edit',
            $this->viewData($periodeAkademik),
        );
    }

    public function update(
        PeriodeAkademikRequest $request,
        PeriodeAkademik $periodeAkademik,
    ): RedirectResponse {
        return $this->persist($request, $periodeAkademik);
    }

    private function persist(
        PeriodeAkademikRequest $request,
        ?PeriodeAkademik $periodeAkademik = null,
    ): RedirectResponse {
        $data = $request->validated();
        $creating = $periodeAkademik === null;

        $krsMulai = $this->toUtc($data['krs_mulai'] ?? null);
        $krsSelesai = $this->toUtc($data['krs_selesai'] ?? null);

        try {
            $saved = DB::transaction(function () use (
                $request,
                $periodeAkademik,
                $data,
                $krsMulai,
                $krsSelesai,
            ): PeriodeAkademik {
                $this->lockActor($request);

                $target = $periodeAkademik === null
                    ? new PeriodeAkademik()
                    : PeriodeAkademik::query()
                    ->whereKey($periodeAkademik->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $target->exists
                    && ! hash_equals(
                        $this->version($target),
                        $data['version'],
                    )
                ) {
                    throw ValidationException::withMessages([
                        'version' => 'Data sudah berubah. Muat ulang halaman dan periksa kembali sebelum menyimpan.',
                    ]);
                }

                $target->fill(Arr::only($data, [
                    'tahun_mulai',
                    'jenis',
                    'mulai',
                    'selesai',
                ]));

                $target->krs_mulai = $krsMulai;
                $target->krs_selesai = $krsSelesai;
                $target->status = $data['status'];
                $target->save();

                return $target;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'tahun_mulai' => 'Kode periode atau kombinasi tahun ajaran dan jenis semester sudah digunakan.',
            ]);
        }

        return to_route('admin.periode-akademik.show', $saved)
            ->with(
                'success',
                $creating
                    ? 'Periode akademik berhasil ditambahkan.'
                    : 'Periode akademik berhasil diperbarui.',
            );
    }

    private function toUtc(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::createFromFormat(
            '!Y-m-d\TH:i',
            $value,
            (string) config('siakad.timezone'),
        )
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }

    private function authorizeAccess(Request $request): void
    {
        $actor = $request->user('web');

        abort_unless($actor instanceof User, 401);

        Gate::forUser($actor)->authorize('kelola-periode-akademik');
    }

    private function lockActor(Request $request): void
    {
        $actor = $request->user('web');

        abort_unless($actor instanceof User, 401);

        $freshActor = User::query()
            ->whereKey($actor->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        Gate::forUser($freshActor)->authorize('kelola-periode-akademik');
    }

    private function viewData(PeriodeAkademik $periodeAkademik): array
    {
        return [
            'periodeAkademik' => $periodeAkademik,
            'jenisOptions' => PeriodeAkademik::JENIS,
            'statusOptions' => PeriodeAkademik::STATUS,
            'version' => $periodeAkademik->exists
                ? $this->version($periodeAkademik)
                : null,
        ];
    }

    private function version(PeriodeAkademik $periodeAkademik): string
    {
        $attributes = $periodeAkademik->getRawOriginal();
        ksort($attributes);

        return hash_hmac(
            'sha256',
            json_encode($attributes, JSON_THROW_ON_ERROR),
            (string) config('app.key'),
        );
    }
}
