<?php

namespace App\Http\Controllers;

use App\Actions\SimpanPengajarKelas;
use App\Http\Requests\PengajarKelasRequest;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\Kurikulum;
use App\Models\PaketSemester;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengajarKelasController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'periode_id' => ['nullable', 'integer', 'min:1', Rule::exists('periode_akademik', 'id')],
            'kelas_id' => ['nullable', 'integer', 'min:1', Rule::exists('kelas_kuliah', 'id')],
            'dosen_id' => ['nullable', 'integer', 'min:1', Rule::exists('dosen', 'id')],
            'peran' => ['nullable', Rule::in(array_keys(PengajarKelas::PERAN))],
            'aktif' => ['nullable', Rule::in(['0', '1'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $query = PengajarKelas::query()->with($this->relasiPenugasan());
        $q = trim((string) ($filter['q'] ?? ''));
        if ($q !== '') {
            $query->where(function (Builder $cari) use ($q): void {
                $cari->whereHas('dosen', fn(Builder $dosen) => $dosen->where('kode_dosen', 'like', '%' . $q . '%'))
                    ->orWhereHas('dosen.user', fn(Builder $user) => $user->where('nama', 'like', '%' . $q . '%'))
                    ->orWhereHas('kelasKuliah', function (Builder $kelas) use ($q): void {
                        $kelas->where(function (Builder $nama) use ($q): void {
                            $nama->where('kode', 'like', '%' . $q . '%')->orWhere('nama_mk_snapshot', 'like', '%' . $q . '%');
                        });
                    });
            });
        }
        if (! empty($filter['periode_id'])) {
            $query->whereHas('kelasKuliah.rombel', fn(Builder $rombel) => $rombel->where('periode_akademik_id', $filter['periode_id']));
        }
        if (! empty($filter['kelas_id'])) {
            $query->where('kelas_kuliah_id', $filter['kelas_id']);
        }
        if (! empty($filter['dosen_id'])) {
            $query->where('dosen_id', $filter['dosen_id']);
        }
        if (! empty($filter['peran'])) {
            $query->where('peran', $filter['peran']);
        }
        if (in_array((string) ($filter['aktif'] ?? ''), ['0', '1'], true)) {
            $query->where('aktif', (bool) (int) $filter['aktif']);
        }

        return view('admin.pengajar-kelas.index', [
            'daftarPenugasan' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'daftarPeriode' => PeriodeAkademik::query()->orderByDesc('mulai')->get(),
            'filter' => $filter,
        ]);
    }

    public function create(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'periode_id' => ['nullable', 'integer', 'min:1', Rule::exists('periode_akademik', 'id')],
            'kelas_id' => ['nullable', 'integer', 'min:1', Rule::exists('kelas_kuliah', 'id')],
            'kelas_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $calon = KelasKuliah::query()->with([
            'rombel.periodeAkademik',
            'rombel.paketSemester.kurikulum.programStudi',
        ])->withCount('pengajarAktif')->whereIn('status', KelasKuliah::BELUM_TUNTAS)
            ->whereHas('rombel.periodeAkademik', fn(Builder $periode) => $periode->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA));

        if (! empty($filter['periode_id'])) {
            $calon->whereHas('rombel', fn(Builder $rombel) => $rombel->where('periode_akademik_id', $filter['periode_id']));
        }
        $q = trim((string) ($filter['q'] ?? ''));
        if ($q !== '') {
            $calon->where(function (Builder $kelas) use ($q): void {
                $kelas->where('kode', 'like', '%' . $q . '%')
                    ->orWhere('nama_mk_snapshot', 'like', '%' . $q . '%')
                    ->orWhereHas('rombel', fn(Builder $rombel) => $rombel->where('kode', 'like', '%' . $q . '%'));
            });
        }

        $kelas = null;
        $dosenPilihan = collect();
        $bolehTambah = false;
        $peranAwal = PengajarKelas::KOORDINATOR;
        if (! empty($filter['kelas_id'])) {
            $kelas = KelasKuliah::query()->with($this->relasiKelas())->findOrFail($filter['kelas_id']);
            $paket = $kelas->rombel->paketSemester;
            $bolehTambah = in_array($kelas->status, KelasKuliah::BELUM_TUNTAS, true)
                && in_array($kelas->rombel->periodeAkademik->status, Rombel::STATUS_PERIODE_TERBUKA, true)
                && $paket->kurikulum->status === Kurikulum::AKTIF
                && $paket->kurikulum->programStudi->aktif
                && in_array($paket->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true);

            $dosenPilihan = Dosen::query()->with('user')->where('status', Dosen::AKTIF)
                ->whereHas('user', fn(Builder $user) => $user->where('status', User::STATUS_AKTIF))
                ->whereHas('user.roles', fn(Builder $role) => $role->where('kode', Role::DOSEN))
                ->whereDoesntHave('pengajarKelas', fn(Builder $penugasan) => $penugasan->where('kelas_kuliah_id', $kelas->id))
                ->orderBy('kode_dosen')->get();

            if ($kelas->pengajarKelas->contains(fn(PengajarKelas $row): bool => $row->isKoordinatorAktif())) {
                $peranAwal = PengajarKelas::PENGAJAR;
            }
        }

        return view('admin.pengajar-kelas.create', [
            'calonKelas' => $calon->orderByDesc('id')->paginate(12, ['*'], 'kelas_page')->withQueryString(),
            'daftarPeriode' => PeriodeAkademik::query()->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA)->orderByDesc('mulai')->get(),
            'filter' => $filter,
            'kelas' => $kelas,
            'penugasan' => null,
            'dosenPilihan' => $dosenPilihan,
            'bolehSimpan' => $bolehTambah && $dosenPilihan->isNotEmpty(),
            'peranAwal' => $peranAwal,
            'versiTim' => $kelas?->versiTimPengajar(),
        ]);
    }

    public function store(PengajarKelasRequest $request, SimpanPengajarKelas $action): RedirectResponse
    {
        $penugasan = $action->execute((int) $request->user()->id, $request->validated());
        return to_route('admin.pengajar-kelas.show', $penugasan)->with('success', 'Penugasan dosen berhasil dibuat.');
    }

    public function show(PengajarKelas $pengajarKelas): View
    {
        [$penugasan, $kelas] = $this->muatTimTerbaru($pengajarKelas);

        return view('admin.pengajar-kelas.show', [
            'penugasan' => $penugasan,
            'kelas' => $kelas,
            'audits' => $penugasan->audits()->with('pelaku')
                ->where('versi_entitas', '<=', $penugasan->revisi)
                ->orderByDesc('versi_entitas')->paginate(10, ['*'], 'audit_page')->withQueryString(),
        ]);
    }

    public function edit(PengajarKelas $pengajarKelas): View
    {
        [$penugasan, $kelas] = $this->muatTimTerbaru($pengajarKelas);

        return view('admin.pengajar-kelas.edit', [
            'penugasan' => $penugasan,
            'kelas' => $kelas,
            'versiTim' => $kelas->versiTimPengajar(),
            'bolehSimpan' => $penugasan->dapatDiubah(),
            'peranAwal' => $penugasan->peran,
        ]);
    }

    public function update(PengajarKelasRequest $request, PengajarKelas $pengajarKelas, SimpanPengajarKelas $action): RedirectResponse
    {
        $penugasan = $action->execute((int) $request->user()->id, $request->validated(), $pengajarKelas);
        return to_route('admin.pengajar-kelas.show', $penugasan)->with('success', 'Penugasan dan riwayat perubahannya berhasil disimpan.');
    }

    private function muatTimTerbaru(PengajarKelas $bound): array
    {
        $kelas = KelasKuliah::query()->with($this->relasiKelas())->findOrFail($bound->kelas_kuliah_id);
        // Nilai formulir dan token berasal dari kumpulan tim yang sama.
        $penugasan = $kelas->pengajarKelas->firstWhere('id', $bound->id);
        abort_unless($penugasan, 404);
        $penugasan->setRelation('kelasKuliah', $kelas);

        return [$penugasan, $kelas];
    }

    private function relasiKelas(): array
    {
        return [
            'rombel.periodeAkademik',
            'rombel.paketSemester.kurikulum.programStudi',
            'pengajarKelas.dosen.user.roles',
        ];
    }

    private function relasiPenugasan(): array
    {
        return [
            'dosen.user.roles',
            'kelasKuliah.rombel.periodeAkademik',
            'kelasKuliah.rombel.paketSemester.kurikulum.programStudi',
        ];
    }
}
