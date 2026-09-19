<?php

namespace App\Http\Controllers;

use App\Actions\KelolaPertemuan;
use App\Actions\KelolaPresensi;
use App\Http\Requests\PresensiRequest;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\PresensiPertemuan;
use App\Services\AksesPresensi;
use App\Support\TokenPresensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PresensiController extends Controller
{
    public function __construct(private readonly AksesPresensi $akses, private readonly KelolaPresensi $aksi) {}

    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['terjadwal', 'berlangsung', 'selesai', 'batal'])],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $query = $this->akses->batasi(Pertemuan::query(), $request->user())->with(['kelasKuliah', 'presensiPertemuan']);
        if (! empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }
        if (! empty($filter['tanggal'])) {
            $awal = \Carbon\CarbonImmutable::createFromFormat('!Y-m-d', $filter['tanggal'], $this->zona())->utc();
            $akhir = $awal->setTimezone($this->zona())->addDay()->utc();
            $query->where('mulai_rencana', '>=', $awal)->where('mulai_rencana', '<', $akhir);
        }
        $daftar = $query->orderByDesc('mulai_rencana')->orderByDesc('id')->paginate(20)->withQueryString();
        return view('presensi.index', ['daftar' => $daftar, 'filter' => $filter, 'zona' => $this->zona()]);
    }

    public function show(Request $request, Pertemuan $pertemuan): View
    {
        Gate::authorize('lihat-presensi', $pertemuan);
        $filter = $request->validate([
            'status' => ['nullable', 'string', Rule::in(array_keys(Presensi::STATUS))],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        $pertemuan->load(['kelasKuliah.rombel.periodeAkademik', 'presensiPertemuan.pembuka', 'presensiPertemuan.penutup']);
        $daftar = $pertemuan->presensiPertemuan;
        $ringkasan = array_fill_keys(array_keys(Presensi::STATUS), 0);
        $baris = null;
        $semuaBaris = collect();
        if ($daftar !== null) {
            // Satu pembacaan peserta untuk tampilan, total, dan token penutupan yang sama.
            // Hanya peserta satu pertemuan; daftar pertemuan/audit tetap dipaginasi di database.
            $semuaBaris = $daftar->presensi()->with('pencatat')->orderBy('id')->get();
            foreach ($semuaBaris as $orang) {
                $ringkasan[$orang->status]++;
            }
            $terpilih = empty($filter['status']) ? $semuaBaris : $semuaBaris->where('status', $filter['status'])->values();
            $halaman = max(1, min((int) ($filter['page'] ?? 1), max(1, (int) ceil($terpilih->count() / 50))));
            $baris = new LengthAwarePaginator(
                $terpilih->forPage($halaman, 50)->values(),
                $terpilih->count(),
                50,
                $halaman,
                ['path' => route('presensi.show', $pertemuan), 'query' => array_filter(['status' => $filter['status'] ?? null])]
            );
        }
        $bolehCatat = $this->akses->catat($request->user(), $pertemuan)
            && ($daftar === null || $daftar->status === PresensiPertemuan::TERBUKA);
        $bolehJalankan = $this->akses->jalankan($request->user(), $pertemuan);
        return view('presensi.show', [
            'sesi' => $pertemuan,
            'daftar' => $daftar,
            'baris' => $baris,
            'ringkasan' => $ringkasan,
            'filter' => $filter,
            'zona' => $this->zona(),
            'bolehCatat' => $bolehCatat,
            'bolehKoreksi' => $this->akses->admin($request->user()) || $bolehCatat,
            'bolehJalankan' => $bolehJalankan,
            'versiSiapkan' => TokenPresensi::pertemuan($pertemuan),
            'versiTutup' => $daftar?->status === PresensiPertemuan::TERBUKA ? $daftar->versiPenutupan($semuaBaris) : null,
            'versiSesi' => $bolehJalankan && in_array($pertemuan->status, [Pertemuan::TERJADWAL, Pertemuan::BERLANGSUNG], true)
                ? $pertemuan->kelasKuliah->versiPertemuan() : null,
        ]);
    }

    public function siapkan(PresensiRequest $request, Pertemuan $pertemuan): RedirectResponse
    {
        $this->aksi->execute('siapkan', $request->user()->id, $pertemuan, $request->validated());
        return $this->kembali($pertemuan, 'Daftar peserta siap.');
    }
    public function catat(PresensiRequest $request, Pertemuan $pertemuan, Presensi $presensi): RedirectResponse
    {
        $this->pastikanMilik($pertemuan, $presensi);
        $this->aksi->execute('catat', $request->user()->id, $pertemuan, $request->validated(), $presensi->id);
        return redirect()->route('presensi.show', [
            'pertemuan' => $pertemuan,
            'page' => (int) $request->input('halaman', 1),
            'status' => $request->validated('filter_status')
        ])
            ->withFragment('peserta-' . $presensi->id)->with('success', 'Presensi tersimpan.');
    }
    public function tutup(PresensiRequest $request, Pertemuan $pertemuan): RedirectResponse
    {
        $this->aksi->execute('tutup', $request->user()->id, $pertemuan, $request->validated());
        return $this->kembali($pertemuan, 'Daftar presensi ditutup. Pertemuan dapat diselesaikan.');
    }
    public function edit(Request $request, Pertemuan $pertemuan, Presensi $presensi): View
    {
        Gate::authorize('lihat-presensi', $pertemuan);
        $this->pastikanMilik($pertemuan, $presensi);
        $presensi->load('daftar');
        abort_unless($this->akses->admin($request->user()) || ($this->akses->catat($request->user(), $pertemuan)
            && $presensi->daftar->status === PresensiPertemuan::TERBUKA), 403);
        abort_if($presensi->status === Presensi::BELUM, 409, 'Peserta ini belum dicatat.');
        return view('presensi.edit', ['sesi' => $pertemuan, 'baris' => $presensi]);
    }
    public function koreksi(PresensiRequest $request, Pertemuan $pertemuan, Presensi $presensi): RedirectResponse
    {
        $this->pastikanMilik($pertemuan, $presensi);
        $this->aksi->execute('koreksi', $request->user()->id, $pertemuan, $request->validated(), $presensi->id);
        return $this->kembali($pertemuan, 'Koreksi tersimpan beserta auditnya.');
    }
    public function audit(Request $request, Pertemuan $pertemuan, Presensi $presensi): View
    {
        Gate::authorize('lihat-presensi', $pertemuan);
        $this->pastikanMilik($pertemuan, $presensi);
        $audit = $presensi->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(25);
        return view('presensi.audit', ['sesi' => $pertemuan, 'baris' => $presensi, 'audit' => $audit, 'zona' => $this->zona()]);
    }
    public function mulai(Request $request, Pertemuan $pertemuan, KelolaPertemuan $aksi): RedirectResponse
    {
        return $this->jalankan($request, $pertemuan, $aksi, 'mulai');
    }
    public function selesai(Request $request, Pertemuan $pertemuan, KelolaPertemuan $aksi): RedirectResponse
    {
        return $this->jalankan($request, $pertemuan, $aksi, 'selesai');
    }
    private function jalankan(Request $request, Pertemuan $pertemuan, KelolaPertemuan $aksi, string $operasi): RedirectResponse
    {
        Gate::authorize('jalankan-pertemuan-presensi', $pertemuan);
        $data = $request->validate([
            'versi_pertemuan' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:2000'],
            'konfirmasi' => ['accepted'],
            'realisasi' => [$operasi === 'selesai' ? 'required' : 'nullable', 'string', 'min:10', 'max:20000'],
        ]);
        $aksi->execute($operasi, $request->user()->id, $data, $pertemuan);
        return $this->kembali($pertemuan, $operasi === 'mulai' ? 'Pertemuan dimulai. Siapkan daftar peserta.' : 'Pertemuan selesai.');
    }
    private function pastikanMilik(Pertemuan $sesi, Presensi $baris): void
    {
        abort_unless($baris->kelas_kuliah_id === $sesi->kelas_kuliah_id
            && $baris->daftar()->where('pertemuan_id', $sesi->id)->exists(), 404);
    }
    private function kembali(Pertemuan $sesi, string $pesan): RedirectResponse
    {
        return redirect()->route('presensi.show', $sesi)->with('success', $pesan);
    }
    private function zona(): string
    {
        return (string) config('siakad.timezone', 'Asia/Makassar');
    }
}
