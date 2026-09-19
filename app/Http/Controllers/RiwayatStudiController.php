<?php

namespace App\Http\Controllers;

use App\Http\Requests\RiwayatStudiRequest;
use App\Models\Dosen;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\RiwayatStudi;
use App\Models\Role;
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

class RiwayatStudiController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('kelola-riwayat-studi');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(RiwayatStudi::STATUS)),
            ],
            'angkatan' => ['nullable', 'integer', 'between:1900,9999'],
            'program_studi_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::exists('program_studi', 'id'),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = RiwayatStudi::query()->with([
            'mahasiswa:id,user_id,nim',
            'mahasiswa.user:id,nama',
            'kurikulum:id,program_studi_id,kode,nama',
            'kurikulum.programStudi:id,nama',
            'periodeMulai:id,kode',
            'periodeAkhir:id,kode',
        ]);

        $search = trim($filters['q'] ?? '');

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->whereHas(
                'mahasiswa',
                function (Builder $mahasiswa) use ($like): void {
                    $mahasiswa->where(
                        function (Builder $search) use ($like): void {
                            $search->where('nim', 'like', $like)
                                ->orWhereHas(
                                    'user',
                                    fn(Builder $user) =>
                                    $user->where('nama', 'like', $like)
                                );
                        }
                    );
                }
            );
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['angkatan'])) {
            $query->where('angkatan', $filters['angkatan']);
        }

        if (! empty($filters['program_studi_id'])) {
            $query->whereHas(
                'kurikulum',
                fn(Builder $kurikulum) =>
                $kurikulum->where(
                    'program_studi_id',
                    $filters['program_studi_id']
                )
            );
        }

        return view('riwayat_studi.index', [
            'daftarRiwayat' => $query
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString(),

            'daftarProdi' => ProgramStudi::query()
                ->orderBy('nama')
                ->get(['id', 'nama']),

            'filters' => $filters,
            'statusOptions' => RiwayatStudi::STATUS,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('kelola-riwayat-studi');

        return view(
            'riwayat_studi.create',
            $this->formData(new RiwayatStudi())
        );
    }

    public function store(RiwayatStudiRequest $request): RedirectResponse
    {
        return $this->persist($request);
    }

    public function show(RiwayatStudi $riwayatStudi): View
    {
        Gate::authorize('kelola-riwayat-studi');

        $this->loadRelations($riwayatStudi);

        return view('riwayat_studi.show', [
            'riwayatStudi' => $riwayatStudi,
            'statusOptions' => RiwayatStudi::STATUS,
        ]);
    }

    public function edit(RiwayatStudi $riwayatStudi): View
    {
        Gate::authorize('kelola-riwayat-studi');

        abort_unless(
            $riwayatStudi->isAktif(),
            409,
            'Riwayat yang sudah ditutup tidak dapat diubah.'
        );

        return view(
            'riwayat_studi.edit',
            $this->formData($riwayatStudi)
        );
    }

    public function update(
        RiwayatStudiRequest $request,
        RiwayatStudi $riwayatStudi
    ): RedirectResponse {
        return $this->persist($request, $riwayatStudi);
    }

    private function persist(
        RiwayatStudiRequest $request,
        ?RiwayatStudi $riwayatStudi = null
    ): RedirectResponse {
        $data = $request->validated();
        $creating = $riwayatStudi === null;

        try {
            $saved = DB::transaction(function () use (
                $request,
                $data,
                $creating,
                $riwayatStudi
            ): RiwayatStudi {
                $this->lockActor($request);

                $mahasiswaId = $creating
                    ? (int) $data['mahasiswa_id']
                    : $riwayatStudi->mahasiswa_id;

                // Semua perubahan riwayat mahasiswa melewati kunci ini.
                $mahasiswa = Mahasiswa::query()
                    ->whereKey($mahasiswaId)
                    ->lockForUpdate()
                    ->first();

                if ($mahasiswa === null) {
                    $this->invalid('mahasiswa_id', 'Mahasiswa tidak tersedia.');
                }

                $semuaRiwayat = RiwayatStudi::query()
                    ->where('mahasiswa_id', $mahasiswa->getKey())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($creating) {
                    if ($semuaRiwayat->contains('status', RiwayatStudi::AKTIF)) {
                        $this->invalid(
                            'mahasiswa_id',
                            'Mahasiswa sudah memiliki riwayat aktif. '
                                . 'Tutup riwayat sebelumnya terlebih dahulu.'
                        );
                    }

                    $target = new RiwayatStudi();

                    $this->lockEligibleUser(
                        $mahasiswa->user_id,
                        Role::MAHASISWA,
                        'mahasiswa_id'
                    );

                    $kurikulum = Kurikulum::query()
                        ->whereKey((int) $data['kurikulum_id'])
                        ->lockForUpdate()
                        ->first();

                    if (
                        $kurikulum === null
                        || $kurikulum->status !== Kurikulum::AKTIF
                    ) {
                        $this->invalid(
                            'kurikulum_id',
                            'Kurikulum harus berstatus aktif.'
                        );
                    }

                    $prodi = ProgramStudi::query()
                        ->whereKey($kurikulum->program_studi_id)
                        ->lockForUpdate()
                        ->first();

                    if ($prodi === null || ! $prodi->aktif) {
                        $this->invalid(
                            'kurikulum_id',
                            'Program studi pada kurikulum harus aktif.'
                        );
                    }

                    $duplikat = $semuaRiwayat->contains(
                        fn(RiwayatStudi $row): bool =>
                        $row->kurikulum_id === $kurikulum->getKey()
                            && $row->periode_mulai_id
                            === (int) $data['periode_mulai_id']
                    );

                    if ($duplikat) {
                        $this->invalid(
                            'kurikulum_id',
                            'Riwayat dengan kurikulum dan periode mulai '
                                . 'tersebut sudah tercatat.'
                        );
                    }

                    $target->mahasiswa()->associate($mahasiswa);
                    $target->kurikulum()->associate($kurikulum);
                    $target->angkatan = (int) $data['angkatan'];
                } else {
                    $target = $semuaRiwayat->firstWhere(
                        'id',
                        $riwayatStudi->getKey()
                    );

                    abort_if($target === null, 404);

                    if (! hash_equals(
                        $this->version($target),
                        $data['version']
                    )) {
                        $this->invalid(
                            'version',
                            'Data sudah berubah. Muat ulang formulir '
                                . 'sebelum menyimpan kembali.'
                        );
                    }

                    if (! $target->isAktif()) {
                        $this->invalid(
                            'riwayat_studi',
                            'Riwayat yang sudah ditutup tidak dapat diubah.'
                        );
                    }
                }

                $status = $creating
                    ? RiwayatStudi::AKTIF
                    : $data['status'];

                $closing = $status !== RiwayatStudi::AKTIF;

                $mulaiId = $creating
                    ? (int) $data['periode_mulai_id']
                    : $target->periode_mulai_id;

                $akhirId = $closing
                    ? (int) $data['periode_akhir_id']
                    : null;

                $periodeIds = $semuaRiwayat
                    ->pluck('periode_akhir_id')
                    ->push($mulaiId, $akhirId)
                    ->filter(fn($id): bool => $id !== null)
                    ->unique()
                    ->sort()
                    ->values();

                $periode = PeriodeAkademik::query()
                    ->whereIn('id', $periodeIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $mulai = $periode->get($mulaiId);

                if ($mulai === null) {
                    $this->invalid(
                        'periode_mulai_id',
                        'Periode mulai tidak tersedia.'
                    );
                }

                if ($creating) {
                    foreach ($semuaRiwayat as $lama) {
                        $akhirLama = $periode->get($lama->periode_akhir_id);

                        if ($akhirLama === null) {
                            $this->invalid(
                                'mahasiswa_id',
                                'Periode akhir riwayat sebelumnya tidak lengkap.'
                            );
                        }

                        if ($mulai->mulai->lt($akhirLama->mulai)) {
                            $this->invalid(
                                'periode_mulai_id',
                                'Periode mulai tidak boleh mendahului periode '
                                    . 'akhir riwayat sebelumnya.'
                            );
                        }
                    }

                    $target->periodeMulai()->associate($mulai);
                }

                if ($closing) {
                    $akhir = $periode->get($akhirId);

                    if ($akhir === null) {
                        $this->invalid(
                            'periode_akhir_id',
                            'Periode akhir tidak tersedia.'
                        );
                    }

                    if ($akhir->mulai->lt($mulai->mulai)) {
                        $this->invalid(
                            'periode_akhir_id',
                            'Periode akhir tidak boleh mendahului periode mulai.'
                        );
                    }
                }

                $paId = isset($data['dosen_pa_id'])
                    ? (int) $data['dosen_pa_id']
                    : null;

                // PA lama dapat dipertahankan; penugasan baru wajib memenuhi syarat.
                if ($paId !== null && $paId !== $target->dosen_pa_id) {
                    $dosen = Dosen::query()
                        ->whereKey($paId)
                        ->lockForUpdate()
                        ->first();

                    if ($dosen === null || ! $dosen->isAktif()) {
                        $this->invalid(
                            'dosen_pa_id',
                            'Dosen PA yang dipilih harus berstatus aktif.'
                        );
                    }

                    $this->lockEligibleUser(
                        $dosen->user_id,
                        Role::DOSEN,
                        'dosen_pa_id'
                    );
                }

                $target->dosen_pa_id = $paId;
                $target->periode_akhir_id = $akhirId;
                $target->status = $status;
                $target->save();

                return $target;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            $this->invalid(
                'riwayat_studi',
                'Riwayat gagal disimpan: mahasiswa sudah memiliki riwayat aktif '
                    . 'atau kombinasi kurikulum dan periode mulai sudah tercatat.'
            );
        }

        return redirect()
            ->route('admin.riwayat-studi.show', $saved)
            ->with(
                'success',
                $creating
                    ? 'Riwayat studi berhasil dibuat.'
                    : 'Riwayat studi berhasil diperbarui.'
            );
    }

    private function lockActor(Request $request): void
    {
        $actor = User::query()
            ->whereKey($request->user()->getAuthIdentifier())
            ->lockForUpdate()
            ->first();

        abort_if($actor === null, 403);

        Gate::forUser($actor)->authorize('kelola-riwayat-studi');
    }

    private function lockEligibleUser(
        int $userId,
        string $roleKode,
        string $errorKey
    ): User {
        $user = User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->first();

        if ($user === null || ! $user->isAktif()) {
            $this->invalid(
                $errorKey,
                'Akun pengguna yang dipilih harus aktif.'
            );
        }

        $role = $user->roles()
            ->where('roles.kode', $roleKode)
            ->lockForUpdate()
            ->first(['roles.id']);

        if ($role === null) {
            $this->invalid(
                $errorKey,
                'Akun pengguna tidak memiliki peran yang diperlukan.'
            );
        }

        return $user;
    }

    private function formData(RiwayatStudi $riwayatStudi): array
    {
        if ($riwayatStudi->exists) {
            $this->loadRelations($riwayatStudi);
        }

        $daftarMahasiswa = $riwayatStudi->exists
            ? collect()
            : Mahasiswa::query()
            ->with('user:id,nama')
            ->whereHas('user', function (Builder $user): void {
                $user->where('status', User::STATUS_AKTIF)
                    ->whereHas(
                        'roles',
                        fn(Builder $role) =>
                        $role->where('kode', Role::MAHASISWA)
                    );
            })
            ->whereDoesntHave(
                'riwayatStudi',
                fn(Builder $riwayat) =>
                $riwayat->where('status', RiwayatStudi::AKTIF)
            )
            ->orderBy('nim')
            ->get(['id', 'user_id', 'nim']);

        $daftarKurikulum = $riwayatStudi->exists
            ? collect()
            : Kurikulum::query()
            ->with('programStudi:id,nama')
            ->where('status', Kurikulum::AKTIF)
            ->whereHas(
                'programStudi',
                fn(Builder $prodi) => $prodi->where('aktif', true)
            )
            ->orderBy('kode')
            ->get(['id', 'program_studi_id', 'kode', 'nama']);

        $paSaatIni = $riwayatStudi->dosen_pa_id;

        $daftarDosen = Dosen::query()
            ->with('user:id,nama')
            ->where(function (Builder $query) use ($paSaatIni): void {
                $query->where(function (Builder $eligible): void {
                    $eligible->where('status', Dosen::AKTIF)
                        ->whereHas('user', function (Builder $user): void {
                            $user->where('status', User::STATUS_AKTIF)
                                ->whereHas(
                                    'roles',
                                    fn(Builder $role) =>
                                    $role->where('kode', Role::DOSEN)
                                );
                        });
                });

                if ($paSaatIni !== null) {
                    $query->orWhere('id', $paSaatIni);
                }
            })
            ->orderBy('kode_dosen')
            ->get(['id', 'user_id', 'kode_dosen']);

        return [
            'riwayatStudi' => $riwayatStudi,
            'daftarMahasiswa' => $daftarMahasiswa,
            'daftarKurikulum' => $daftarKurikulum,
            'daftarDosen' => $daftarDosen,

            'daftarPeriode' => PeriodeAkademik::query()
                ->orderByDesc('mulai')
                ->orderByDesc('id')
                ->get(['id', 'kode', 'mulai']),

            'statusOptions' => RiwayatStudi::STATUS,

            'version' => $riwayatStudi->exists
                ? $this->version($riwayatStudi)
                : null,
        ];
    }

    private function loadRelations(RiwayatStudi $riwayatStudi): void
    {
        $riwayatStudi->load([
            'mahasiswa:id,user_id,nim',
            'mahasiswa.user:id,nama,status',
            'kurikulum:id,program_studi_id,kode,nama',
            'kurikulum.programStudi:id,kode,nama',
            'periodeMulai:id,kode,mulai',
            'periodeAkhir:id,kode,mulai',
            'dosenPa:id,user_id,kode_dosen',
            'dosenPa.user:id,nama',
        ]);
    }

    private function version(RiwayatStudi $riwayatStudi): string
    {
        $attributes = $riwayatStudi->getRawOriginal();

        ksort($attributes);

        return hash_hmac(
            'sha256',
            json_encode($attributes, JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
