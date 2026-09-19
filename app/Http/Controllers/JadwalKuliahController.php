<?php

namespace App\Http\Controllers;

use App\Actions\SimpanJadwalKuliah;
use App\Http\Requests\JadwalKuliahRequest;
use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\Kurikulum;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\Rombel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JadwalKuliahController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'periode_id' => ['nullable', 'integer', 'exists:periode_akademik,id'],
            'rombel_id' => ['nullable', 'integer', 'exists:rombel,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas_kuliah,id'],
            'dosen_id' => ['nullable', 'integer', 'exists:dosen,id'],
            'hari' => ['nullable', 'integer', 'between:1,7'],
            'aktif' => ['nullable', Rule::in(['0', '1'])],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $query = JadwalKuliah::query()->with($this->relasiDaftar());
        $q = trim($filter['q'] ?? '');
        if ($q !== '') {
            $query->whereHas('kelasKuliah', function (Builder $kelas) use ($q): void {
                $kelas->where(function (Builder $cari) use ($q): void {
                    $cari->where('kode', 'like', '%' . $q . '%')
                        ->orWhere('nama_mk_snapshot', 'like', '%' . $q . '%')
                        ->orWhereHas('rombel', fn(Builder $rombel) => $rombel->where('kode', 'like', '%' . $q . '%'));
                });
            });
        }
        if (! empty($filter['periode_id'])) {
            $query->whereHas('kelasKuliah.rombel', fn(Builder $rombel) => $rombel->where('periode_akademik_id', $filter['periode_id']));
        }
        if (! empty($filter['rombel_id'])) {
            $query->whereHas('kelasKuliah', fn(Builder $kelas) => $kelas->where('rombel_id', $filter['rombel_id']));
        }
        if (! empty($filter['kelas_id'])) {
            $query->where('kelas_kuliah_id', $filter['kelas_id']);
        }
        if (! empty($filter['dosen_id'])) {
            $query->whereHas('kelasKuliah.pengajarKelas', function (Builder $tim) use ($filter): void {
                $tim->where('aktif', true)->where('dosen_id', $filter['dosen_id']);
            });
        }
        if (! empty($filter['hari'])) {
            $query->where('hari', $filter['hari']);
        }
        if (in_array($filter['aktif'] ?? null, ['0', '1'], true)) {
            $query->where('aktif', $filter['aktif'] === '1');
        }
        if (! empty($filter['tanggal'])) {
            $tanggal = CarbonImmutable::createFromFormat('!Y-m-d', $filter['tanggal'], 'UTC');
            $query->where('hari', $tanggal->dayOfWeekIso)
                ->where('berlaku_mulai', '<=', $filter['tanggal'])
                ->where('berlaku_selesai', '>=', $filter['tanggal']);
        }

        return view('admin.jadwal-kuliah.index', [
            'daftarJadwal' => $query->orderBy('hari')->orderBy('jam_mulai')->orderBy('id')->paginate(20)->withQueryString(),
            'filter' => $filter,
            'daftarPeriode' => PeriodeAkademik::query()->orderByDesc('mulai')->get(),
            'daftarDosen' => Dosen::query()->with('user')->orderBy('kode_dosen')->get(),
            'rombelTerpilih' => ! empty($filter['rombel_id']) ? Rombel::find($filter['rombel_id']) : null,
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
        $calon = KelasKuliah::query()->with(['rombel.periodeAkademik'])
            ->whereIn('status', KelasKuliah::BELUM_TUNTAS)
            ->whereHas('rombel.periodeAkademik', fn(Builder $periode) => $periode->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA))
            ->withCount(['jadwalKuliah as jumlah_jadwal_aktif' => fn(Builder $jadwal) => $jadwal->aktif()]);
        if (! empty($filter['periode_id'])) {
            $calon->whereHas('rombel', fn(Builder $rombel) => $rombel->where('periode_akademik_id', $filter['periode_id']));
        }
        $q = trim($filter['q'] ?? '');
        if ($q !== '') {
            $calon->where(function (Builder $kelas) use ($q): void {
                $kelas->where('kode', 'like', '%' . $q . '%')->orWhere('nama_mk_snapshot', 'like', '%' . $q . '%')
                    ->orWhereHas('rombel', fn(Builder $rombel) => $rombel->where('kode', 'like', '%' . $q . '%'));
            });
        }
        $kelas = ! empty($filter['kelas_id']) ? $this->muatKelas((int) $filter['kelas_id']) : null;
        $jadwal = new JadwalKuliah();
        $boleh = false;
        if ($kelas !== null) {
            $jadwal->kelas_kuliah_id = $kelas->id;
            $jadwal->setRelation('kelasKuliah', $kelas);
            $jadwal->hari = 1;
            $jadwal->metode = 'daring';
            $jadwal->aktif = true;
            $jadwal->berlaku_mulai = $kelas->rombel->periodeAkademik->mulai->toDateString();
            $jadwal->berlaku_selesai = $kelas->rombel->periodeAkademik->selesai->toDateString();
            $kurikulum = $kelas->rombel->paketSemester->kurikulum;
            $boleh = $jadwal->dapatDiubah() && $kurikulum->programStudi->aktif
                && $kurikulum->status === Kurikulum::AKTIF
                && in_array($kelas->rombel->paketSemester->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true);
        }

        return view('admin.jadwal-kuliah.create', [
            'filter' => $filter,
            'kelas' => $kelas,
            'jadwal' => $jadwal,
            'boleh' => $boleh,
            'versi' => $kelas?->versiJadwalKuliah(),
            'daftarKelas' => $calon->orderByDesc('id')->paginate(12, ['*'], 'kelas_page')->withQueryString(),
            'daftarPeriode' => PeriodeAkademik::query()->whereIn('status', Rombel::STATUS_PERIODE_TERBUKA)
                ->orderByDesc('mulai')->get(),
        ]);
    }

    public function store(JadwalKuliahRequest $request, SimpanJadwalKuliah $action): RedirectResponse
    {
        $jadwal = $action->execute($request->user()->id, $request->validated());
        return to_route('admin.jadwal-kuliah.show', $jadwal)->with('success', 'Jadwal kuliah berhasil dibuat.');
    }

    public function show(JadwalKuliah $jadwalKuliah): View
    {
        [$kelas, $jadwal] = $this->muatJadwalTerbaru($jadwalKuliah);
        return view('admin.jadwal-kuliah.show', [
            'kelas' => $kelas,
            'jadwal' => $jadwal,
            'versi' => $kelas->versiJadwalKuliah(),
            'audits' => $jadwal->audits()->with('pelaku')->where('versi_entitas', '<=', $jadwal->revisi)
                ->orderByDesc('versi_entitas')->paginate(10, ['*'], 'audit_page')->withQueryString(),
        ]);
    }

    public function edit(JadwalKuliah $jadwalKuliah): View
    {
        [$kelas, $jadwal] = $this->muatJadwalTerbaru($jadwalKuliah);
        return view('admin.jadwal-kuliah.edit', [
            'kelas' => $kelas,
            'jadwal' => $jadwal,
            'versi' => $kelas->versiJadwalKuliah(),
            'boleh' => $jadwal->dapatDiubah(),
        ]);
    }

    public function update(JadwalKuliahRequest $request, JadwalKuliah $jadwalKuliah, SimpanJadwalKuliah $action): RedirectResponse
    {
        $jadwal = $action->execute($request->user()->id, $request->validated(), $jadwalKuliah);
        return to_route('admin.jadwal-kuliah.show', $jadwal)->with('success', 'Perubahan jadwal berhasil disimpan.');
    }

    public function nonaktifkan(JadwalKuliahRequest $request, JadwalKuliah $jadwalKuliah, SimpanJadwalKuliah $action): RedirectResponse
    {
        $jadwal = $action->execute($request->user()->id, $request->validated(), $jadwalKuliah, true);
        return to_route('admin.jadwal-kuliah.show', $jadwal)->with('success', 'Jadwal dinonaktifkan. Pemesanan waktunya dilepaskan.');
    }

    private function muatKelas(int $kelasId): KelasKuliah
    {
        return KelasKuliah::query()->with([
            'rombel.periodeAkademik',
            'rombel.paketSemester.kurikulum.programStudi',
            'pengajarKelas.dosen.user',
            'jadwalKuliah',
        ])->findOrFail($kelasId);
    }

    private function muatJadwalTerbaru(JadwalKuliah $bound): array
    {
        $kelas = $this->muatKelas($bound->kelas_kuliah_id);
        // Form dan token berasal dari koleksi yang sama, bukan route model yang lebih lama.
        $jadwal = $kelas->jadwalKuliah->firstWhere('id', $bound->id);
        abort_unless($jadwal, 404);
        $jadwal->setRelation('kelasKuliah', $kelas);
        return [$kelas, $jadwal];
    }

    private function relasiDaftar(): array
    {
        return ['kelasKuliah.rombel.periodeAkademik', 'kelasKuliah.pengajarKelas.dosen.user'];
    }
}
