<?php

namespace App\Http\Controllers;

use App\Actions\KelolaKrs;
use App\Http\Requests\KrsRequest;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\RegistrasiSemester;
use App\Models\RiwayatStudi;
use App\Models\Role;
use App\Models\Rombel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KrsController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'periode_id' => ['nullable', 'integer', 'min:1', Rule::exists('periode_akademik', 'id')],
            'program_studi_id' => ['nullable', 'integer', 'min:1', Rule::exists('program_studi', 'id')],
            'status' => ['nullable', Rule::in(array_keys(Krs::STATUS))],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $query = Krs::query()->with($this->relations());
        $q = trim((string) ($filter['q'] ?? ''));
        if ($q !== '') {
            $this->cariMahasiswa($query, 'registrasiSemester.riwayatStudi.mahasiswa', $q);
        }
        if (! empty($filter['periode_id'])) {
            $query->whereHas('registrasiSemester', fn(Builder $reg) => $reg->where('periode_akademik_id', $filter['periode_id']));
        }
        if (! empty($filter['program_studi_id'])) {
            $query->whereHas(
                'registrasiSemester.riwayatStudi.kurikulum',
                fn(Builder $kurikulum) => $kurikulum->where('program_studi_id', $filter['program_studi_id'])
            );
        }
        if (! empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        return view('admin.krs.index', [
            'daftarKrs' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'filter' => $filter,
            'daftarPeriode' => PeriodeAkademik::query()->orderByDesc('mulai')->get(),
            'daftarProdi' => ProgramStudi::query()->orderBy('nama')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'periode_id' => ['nullable', 'integer', 'min:1', Rule::exists('periode_akademik', 'id')],
            'registrasi_id' => ['nullable', 'integer', 'min:1', Rule::exists('registrasi_semester', 'id')],
            'calon_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $calon = RegistrasiSemester::query()->with($this->relasiRegistrasi())
            ->where('status', RegistrasiSemester::AKTIF)
            ->whereHas('riwayatStudi', fn(Builder $riwayat) => $riwayat->where('status', RiwayatStudi::AKTIF))
            ->whereHas('periodeAkademik', fn(Builder $periode) => $periode->where('status', 'aktif'))
            ->whereDoesntHave('krs');

        $q = trim((string) ($filter['q'] ?? ''));
        if ($q !== '') {
            $this->cariMahasiswa($calon, 'riwayatStudi.mahasiswa', $q);
        }
        if (! empty($filter['periode_id'])) {
            $calon->where('periode_akademik_id', $filter['periode_id']);
        }

        $registrasi = null;
        $barisPaket = collect();
        $kendala = [];
        if (! empty($filter['registrasi_id'])) {
            $registrasi = RegistrasiSemester::query()->with(array_merge($this->relasiRegistrasi(), [
                'krs',
                'rombel.kelasKuliah',
                'rombel.paketSemester.details.kurikulumMataKuliah.mataKuliah',
            ]))->findOrFail($filter['registrasi_id']);

            $paket = $registrasi->rombel->paketSemester;
            $kelasPerDetail = $registrasi->rombel->kelasKuliah->keyBy('detail_paket_id');
            $barisPaket = $paket->details->sortBy('id')->map(function ($detail) use ($kelasPerDetail): array {
                $kelas = $kelasPerDetail->get($detail->id);
                return [
                    'nama' => $kelas?->nama_mk_snapshot ?? $detail->kurikulumMataKuliah->mataKuliah->nama,
                    'sks' => (string) ($kelas?->sks_snapshot ?? $detail->kurikulumMataKuliah->sks),
                    'kelas' => $kelas,
                ];
            })->values();

            if ($registrasi->krs !== null) {
                $kendala[] = 'KRS registrasi ini sudah ada. Gunakan tombol Buka KRS yang tersedia.';
            }
            if ($registrasi->status !== RegistrasiSemester::AKTIF || $registrasi->penempatan_dikunci_at === null) {
                $kendala[] = 'Aktifkan registrasi semester terlebih dahulu.';
            }
            if ($registrasi->riwayatStudi->status !== RiwayatStudi::AKTIF) {
                $kendala[] = 'Riwayat studi sudah ditutup.';
            }
            if (! $registrasi->periodeAkademik->isKrsOpen()) {
                $kendala[] = 'Periode atau jendela pengisian KRS belum aktif/sudah ditutup.';
            }
            if (! $registrasi->riwayatStudi->mahasiswa->user->hasRole(Role::MAHASISWA)) {
                $kendala[] = 'Akun mahasiswa harus aktif dan memiliki role mahasiswa.';
            }
            if (
                $registrasi->riwayatStudi->kurikulum->status !== Kurikulum::AKTIF
                || ! $registrasi->riwayatStudi->kurikulum->programStudi->aktif
                || ! in_array($paket->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true)
            ) {
                $kendala[] = 'Periksa status kurikulum, program studi, dan paket semester.';
            }
            if (
                $barisPaket->isEmpty() || $barisPaket->count() !== $kelasPerDetail->count()
                || $barisPaket->contains(fn(array $baris): bool => $baris['kelas'] === null
                    || ! in_array($baris['kelas']->status, [KelasKuliah::PERSIAPAN, KelasKuliah::AKTIF], true))
            ) {
                $kendala[] = 'Lengkapi seluruh kelas paket; kelas harus dalam persiapan atau aktif.';
            }
        }

        return view('admin.krs.create', [
            'calonRegistrasi' => $calon->orderByDesc('id')->paginate(10, ['*'], 'calon_page')->withQueryString(),
            'registrasi' => $registrasi,
            'barisPaket' => $barisPaket,
            'kendala' => $kendala,
            'filter' => $filter,
            'daftarPeriode' => PeriodeAkademik::query()->where('status', 'aktif')->orderByDesc('mulai')->get(),
        ]);
    }

    public function store(KrsRequest $request, KelolaKrs $action): RedirectResponse
    {
        $krs = $action->execute((int) $request->user()->id, 'buat', $request->validated());
        return to_route('admin.krs.show', $krs)->with('success', 'Draf KRS dan seluruh detail paket berhasil dibuat.');
    }

    public function show(Krs $krs): View
    {
        $krs->load($this->relations());
        $registrasi = $krs->registrasiSemester;

        return view('admin.krs.show', [
            'krs' => $krs,
            'registrasi' => $registrasi,
            'versiForm' => $krs->versiForm(),
            'operasi' => $krs->operasiTersedia(),
            'periodeTerbuka' => $registrasi->riwayatStudi->status === RiwayatStudi::AKTIF
                && in_array($registrasi->periodeAkademik->status, Rombel::STATUS_PERIODE_TERBUKA, true),
            'jendelaTerbuka' => $registrasi->periodeAkademik->isKrsOpen(),
            'audits' => $krs->audits()->with('pelaku')
                ->where('versi_entitas', '<=', $krs->versi)
                ->orderByDesc('versi_entitas')->paginate(10, ['*'], 'audit_page')->withQueryString(),
        ]);
    }

    public function edit(Krs $krs): View
    {
        $krs->load($this->relations());
        abort_unless($krs->status === Krs::DRAF, 409, 'Catatan hanya dapat diubah pada KRS draf.');

        return view('admin.krs.edit', [
            'krs' => $krs,
            'registrasi' => $krs->registrasiSemester,
            'versiForm' => $krs->versiForm(),
        ]);
    }

    public function update(KrsRequest $request, Krs $krs, KelolaKrs $action): RedirectResponse
    {
        $krs = $action->execute((int) $request->user()->id, 'ubah', $request->validated(), $krs);
        return to_route('admin.krs.show', $krs)->with('success', 'Catatan draf KRS berhasil disimpan.');
    }

    public function ajukan(KrsRequest $request, Krs $krs, KelolaKrs $action): RedirectResponse
    {
        return $this->jalankan($request, $krs, $action, 'ajukan', 'KRS berhasil diajukan. Keikutsertaan kelas menunggu pengesahan.');
    }

    public function kembalikan(KrsRequest $request, Krs $krs, KelolaKrs $action): RedirectResponse
    {
        return $this->jalankan($request, $krs, $action, 'kembalikan', 'Pengajuan dikembalikan ke draf.');
    }

    public function sahkan(KrsRequest $request, Krs $krs, KelolaKrs $action): RedirectResponse
    {
        return $this->jalankan($request, $krs, $action, 'sahkan', 'KRS disahkan. Seluruh mata kuliah paket menjadi keikutsertaan yang sah.');
    }

    public function revisi(KrsRequest $request, Krs $krs, KelolaKrs $action): RedirectResponse
    {
        return $this->jalankan($request, $krs, $action, 'revisi', 'Revisi dibuka. Keikutsertaan kelas menunggu pengesahan ulang.');
    }

    public function pulihkan(KrsRequest $request, Krs $krs, KelolaKrs $action): RedirectResponse
    {
        return $this->jalankan($request, $krs, $action, 'pulihkan', 'KRS dipulihkan ke draf menggunakan detail yang sama.');
    }

    public function batalkan(KrsRequest $request, Krs $krs, KelolaKrs $action): RedirectResponse
    {
        return $this->jalankan($request, $krs, $action, 'batalkan', 'KRS dan keikutsertaan kelas dibatalkan. Riwayat tetap tersimpan.');
    }

    public function cetak(Request $request, Krs $krs, KelolaKrs $action): View
    {
        $krs = $action->untukCetak((int) $request->user()->id, $krs);
        return view('admin.krs.cetak', [
            'krs' => $krs,
            'registrasi' => $krs->registrasiSemester,
            'dicetakPada' => now('UTC'),
        ]);
    }

    private function jalankan(KrsRequest $request, Krs $krs, KelolaKrs $action, string $operasi, string $pesan): RedirectResponse
    {
        $krs = $action->execute((int) $request->user()->id, $operasi, $request->validated(), $krs);
        return to_route('admin.krs.show', $krs)->with('success', $pesan);
    }

    private function cariMahasiswa(Builder $query, string $relasi, string $q): void
    {
        // Parameter binding dipakai oleh Eloquent; tidak merangkai SQL dari input.
        $query->whereHas($relasi, function (Builder $mahasiswa) use ($q): void {
            $mahasiswa->where(function (Builder $identitas) use ($q): void {
                $identitas->where('nim', 'like', '%' . $q . '%')
                    ->orWhereHas('user', fn(Builder $user) => $user->where('nama', 'like', '%' . $q . '%'));
            });
        });
    }

    private function relasiRegistrasi(): array
    {
        return [
            'riwayatStudi.mahasiswa.user',
            'riwayatStudi.kurikulum.programStudi',
            'periodeAkademik',
            'rombel.paketSemester',
        ];
    }

    private function relations(): array
    {
        return array_merge(
            array_map(fn(string $relasi): string => 'registrasiSemester.' . $relasi, $this->relasiRegistrasi()),
            ['details.kelasKuliah', 'pengesah']
        );
    }
}
