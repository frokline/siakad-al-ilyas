<?php

namespace App\Http\Controllers;

use App\Actions\KelolaPertemuan;
use App\Http\Requests\PertemuanRequest;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\PeriodeAkademik;
use App\Models\Pertemuan;
use App\Models\Rombel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PertemuanController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'periode_id' => ['nullable', 'integer', 'exists:periode_akademik,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas_kuliah,id'],
            'dosen_id' => ['nullable', 'integer', 'exists:dosen,id'],
            'status' => ['nullable', Rule::in(array_keys(Pertemuan::STATUS))],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $query = Pertemuan::query()->with(['kelasKuliah.rombel.periodeAkademik', 'pengajarKelas.dosen.user']);
        $q = trim($filter['q'] ?? '');
        if ($q !== '') {
            $query->where(function (Builder $cari) use ($q): void {
                $cari->where('topik', 'like', '%' . $q . '%')->orWhereHas('kelasKuliah', function (Builder $kelas) use ($q): void {
                    $kelas->where('kode', 'like', '%' . $q . '%')->orWhere('nama_mk_snapshot', 'like', '%' . $q . '%');
                });
            });
        }
        if (! empty($filter['periode_id'])) {
            $query->whereHas('kelasKuliah.rombel', fn(Builder $r) => $r->where('periode_akademik_id', $filter['periode_id']));
        }
        if (! empty($filter['kelas_id'])) {
            $query->where('kelas_kuliah_id', $filter['kelas_id']);
        }
        if (! empty($filter['dosen_id'])) {
            $query->whereHas('pengajarKelas', fn(Builder $p) => $p->where('dosen_id', $filter['dosen_id']));
        }
        if (! empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }
        if (! empty($filter['tanggal'])) {
            $awal = CarbonImmutable::createFromFormat('!Y-m-d', $filter['tanggal'], config('siakad.timezone', 'Asia/Makassar'));
            $query->where('mulai_rencana', '>=', $awal->utc()->format('Y-m-d H:i:s'))
                ->where('mulai_rencana', '<', $awal->addDay()->utc()->format('Y-m-d H:i:s'));
        }
        return view('admin.pertemuan.index', [
            'daftarPertemuan' => $query->orderByDesc('mulai_rencana')->orderByDesc('id')->paginate(20)->withQueryString(),
            'filter' => $filter,
            'daftarPeriode' => PeriodeAkademik::query()->orderByDesc('mulai')->get(),
            'daftarDosen' => Dosen::query()->with('user')->orderBy('kode_dosen')->get(),
            'kelasTerpilih' => ! empty($filter['kelas_id']) ? KelasKuliah::find($filter['kelas_id']) : null,
        ]);
    }

    public function create(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'periode_id' => ['nullable', 'integer', 'exists:periode_akademik,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas_kuliah,id'],
            'kelas_page' => ['nullable', 'integer', 'min:1'],
        ]);
        $calon = KelasKuliah::query()->with('rombel.periodeAkademik')->whereIn('status', KelasKuliah::BELUM_TUNTAS)
            ->whereHas('rombel.periodeAkademik', fn(Builder $p) => $p->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA))
            ->withCount('pertemuan');
        if (! empty($filter['periode_id'])) {
            $calon->whereHas('rombel', fn(Builder $r) => $r->where('periode_akademik_id', $filter['periode_id']));
        }
        $q = trim($filter['q'] ?? '');
        if ($q !== '') {
            $calon->where(function (Builder $kelas) use ($q): void {
                $kelas->where('kode', 'like', '%' . $q . '%')->orWhere('nama_mk_snapshot', 'like', '%' . $q . '%');
            });
        }
        $kelas = ! empty($filter['kelas_id']) ? $this->muatKelas((int) $filter['kelas_id']) : null;
        $sesi = new Pertemuan();
        $sesi->jenis = 'kuliah';
        $sesi->metode = 'daring';
        $sesi->status = Pertemuan::TERJADWAL;
        $sesi->nomor = $kelas ? (int) $kelas->pertemuan->max('nomor') + 1 : 1;
        if ($kelas) {
            $sesi->setRelation('kelasKuliah', $kelas);
        }
        return view('admin.pertemuan.create', [
            'kelas' => $kelas,
            'sesi' => $sesi,
            'filter' => $filter,
            'versi' => $kelas?->versiPertemuan(),
            'boleh' => $kelas !== null && $sesi->konteksTerbuka() && $sesi->nomor <= 65535,
            'daftarKelas' => $calon->orderByDesc('id')->paginate(12, ['*'], 'kelas_page')->withQueryString(),
            'daftarPeriode' => PeriodeAkademik::query()->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA)->orderByDesc('mulai')->get(),
        ]);
    }

    public function store(PertemuanRequest $request, KelolaPertemuan $action): RedirectResponse
    {
        return $this->simpan('buat', $request, $action);
    }

    public function show(Pertemuan $pertemuan): View
    {
        [$kelas, $sesi] = $this->muatTerbaru($pertemuan);
        return view('admin.pertemuan.show', [
            'kelas' => $kelas,
            'sesi' => $sesi,
            'versi' => $kelas->versiPertemuan(),
            'audits' => $sesi->audits()->with('pelaku')->where('versi_entitas', '<=', $sesi->revisi)
                ->orderByDesc('versi_entitas')->paginate(10, ['*'], 'audit_page')->withQueryString(),
        ]);
    }

    public function edit(Pertemuan $pertemuan): View
    {
        [$kelas, $sesi] = $this->muatTerbaru($pertemuan);
        return view('admin.pertemuan.edit', [
            'kelas' => $kelas,
            'sesi' => $sesi,
            'versi' => $kelas->versiPertemuan(),
            'boleh' => $sesi->dapatDiubah(),
        ]);
    }

    public function update(PertemuanRequest $request, Pertemuan $pertemuan, KelolaPertemuan $action): RedirectResponse
    {
        return $this->simpan('ubah', $request, $action, $pertemuan);
    }

    public function mulai(PertemuanRequest $request, Pertemuan $pertemuan, KelolaPertemuan $action): RedirectResponse
    {
        return $this->simpan('mulai', $request, $action, $pertemuan);
    }

    public function selesai(PertemuanRequest $request, Pertemuan $pertemuan, KelolaPertemuan $action): RedirectResponse
    {
        return $this->simpan('selesai', $request, $action, $pertemuan);
    }

    public function batalkan(PertemuanRequest $request, Pertemuan $pertemuan, KelolaPertemuan $action): RedirectResponse
    {
        return $this->simpan('batalkan', $request, $action, $pertemuan);
    }

    public function pulihkan(PertemuanRequest $request, Pertemuan $pertemuan, KelolaPertemuan $action): RedirectResponse
    {
        return $this->simpan('pulihkan', $request, $action, $pertemuan);
    }

    private function simpan(string $aksi, PertemuanRequest $request, KelolaPertemuan $action, ?Pertemuan $sesi = null): RedirectResponse
    {
        $hasil = $action->execute($aksi, $request->user()->id, $request->validated(), $sesi);
        return to_route('admin.pertemuan.show', $hasil)->with('success', Pertemuan::AKSI[$aksi] . ' berhasil.');
    }

    private function muatKelas(int $id): KelasKuliah
    {
        return KelasKuliah::query()->with([
            'rombel.periodeAkademik',
            'rombel.paketSemester.kurikulum.programStudi',
            'pengajarKelas.dosen.user',
            'jadwalKuliah',
            'pertemuan',
        ])->findOrFail($id);
    }

    private function muatTerbaru(Pertemuan $bound): array
    {
        $kelas = $this->muatKelas($bound->kelas_kuliah_id);
        $sesi = $kelas->pertemuan->firstWhere('id', $bound->id);
        abort_unless($sesi, 404);
        $sesi->setRelation('kelasKuliah', $kelas);
        $sesi->setRelation('pengajarKelas', $kelas->pengajarKelas->firstWhere('id', $sesi->pengajar_kelas_id));
        $sesi->setRelation('jadwalKuliah', $kelas->jadwalKuliah->firstWhere('id', $sesi->jadwal_kuliah_id));
        return [$kelas, $sesi];
    }
}
