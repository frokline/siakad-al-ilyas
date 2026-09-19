<?php

namespace App\Http\Controllers;

use App\Actions\KelolaKalenderAkademik;
use App\Http\Requests\KalenderAkademikRequest;
use App\Http\Requests\KalenderAkademikStatusRequest;
use App\Models\KalenderAkademik;
use App\Services\AksesKalender;
use App\Services\AturanKalender;
use App\Services\GridKalender;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KalenderAkademikController extends Controller
{
    public function index(Request $r, AksesKalender $akses, GridKalender $grid): View
    {
        Gate::authorize('viewAny', KalenderAkademik::class);
        $f = $r->validate([
            'bulan' => ['nullable', 'date_format:Y-m', 'regex:/\A(?:20[0-9]{2}|2100)-(?:0[1-9]|1[0-2])\z/'],
            'hari' => ['nullable', 'date_format:Y-m-d'],
            'tampilan' => ['nullable', Rule::in(['bulan', 'daftar'])],
            'jenis' => ['nullable', Rule::in(array_keys(KalenderAkademik::JENIS))],
            'status' => ['nullable', Rule::in(array_keys(KalenderAkademik::STATUS))],
            'periode_akademik_id' => ['nullable', 'integer', 'min:1'],
            'program_studi_id' => ['nullable', 'integer', 'min:1'],
            'kampus_saja' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'between:1,100000']
        ]);
        $f['bulan'] = $f['bulan'] ?? now(KalenderAkademik::ZONA)->format('Y-m');
        $f['tampilan'] = $f['tampilan'] ?? 'bulan';
        $bulan = CarbonImmutable::createFromFormat('!Y-m', $f['bulan'], KalenderAkademik::ZONA);
        $awal = $bulan->startOfMonth();
        $akhir = $awal->addMonth();
        if ($f['tampilan'] === 'daftar' && ! empty($f['hari'])) {
            if (substr($f['hari'], 0, 7) !== $f['bulan']) {
                throw ValidationException::withMessages(['hari' => 'Hari harus berada pada bulan yang dipilih.']);
            }
            $awal = CarbonImmutable::createFromFormat('!Y-m-d', $f['hari'], KalenderAkademik::ZONA);
            $akhir = $awal->addDay();
        }
        $q = $akses->batasi(KalenderAkademik::query(), $r->user())->with(['periodeAkademik:id,kode', 'programStudi:id,nama'])
            ->where('mulai_at', '<', $akhir->utc()->format('Y-m-d H:i:s'))->where('selesai_at', '>', $awal->utc()->format('Y-m-d H:i:s'));
        foreach (['jenis', 'status', 'periode_akademik_id'] as $key) {
            if (! empty($f[$key])) {
                $q->where($key, $f[$key]);
            }
        }
        if (! empty($f['kampus_saja'])) {
            $q->whereNull('program_studi_id');
        } elseif (! empty($f['program_studi_id'])) {
            // Agenda kampus tetap relevan ketika prodi tertentu dipilih.
            $q->where(fn($b) => $b->whereNull('program_studi_id')->orWhere('program_studi_id', $f['program_studi_id']));
        }
        if (! empty($f['q'])) {
            $q->where('judul', 'like', '%' . $f['q'] . '%');
        }
        $q->orderBy('mulai_at')->orderBy('id');
        $lebih = false;
        $minggu = [];
        $daftar = null;
        if ($f['tampilan'] === 'daftar') {
            $daftar = $q->paginate(25)->withQueryString();
        } else {
            $items = $q->limit(501)->get();
            $lebih = $items->count() > 500;
            // Jika terlalu padat, jangan menampilkan kalender parsial yang menyesatkan.
            if (! $lebih) {
                $minggu = $grid->susun($items, $bulan);
            }
        }
        return view('kalender_akademik.index', [
            'filter' => $f,
            'bulan' => $bulan,
            'minggu' => $minggu,
            'daftar' => $daftar,
            'lebih' => $lebih,
            'periode' => DB::table('periode_akademik')->orderByDesc('id')->get(['id', 'kode']),
            'prodi' => DB::table('program_studi')->orderBy('nama')->get(['id', 'nama']),
            'kelola' => $akses->kelola($r->user())
        ]);
    }
    public function create(): View
    {
        Gate::authorize('create', KalenderAkademik::class);
        return view('kalender_akademik.create', $this->pilihan(new KalenderAkademik()) + ['token' => (string) Str::uuid()]);
    }
    public function store(KalenderAkademikRequest $r, KelolaKalenderAkademik $aksi): RedirectResponse
    {
        $k = $aksi->buat($r->user()->id, $r->all());
        return redirect()->route('kalender.show', $k)->with('info', 'Draf agenda tersimpan. Terbitkan setelah diperiksa.');
    }
    public function show(Request $r, KalenderAkademik $kalenderAkademik): View
    {
        Gate::authorize('view', $kalenderAkademik);
        $r->validate(['page' => ['nullable', 'integer', 'between:1,100000']]);
        $kalenderAkademik->load(['periodeAkademik', 'programStudi']);
        $audit = Gate::allows('audit', $kalenderAkademik) ? $kalenderAkademik->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10) : null;
        return view('kalender_akademik.show', ['agenda' => $kalenderAkademik, 'audit' => $audit]);
    }
    public function edit(KalenderAkademik $kalenderAkademik): View
    {
        Gate::authorize('update', $kalenderAkademik);
        return view('kalender_akademik.edit', $this->pilihan($kalenderAkademik));
    }
    public function update(KalenderAkademikRequest $r, KalenderAkademik $kalenderAkademik, KelolaKalenderAkademik $aksi): RedirectResponse
    {
        $k = $aksi->ubah($r->user()->id, $kalenderAkademik, $r->all());
        return redirect()->route('kalender.show', $k)->with('info', 'Agenda tersimpan. Perubahan agenda terbit langsung terlihat pengguna.');
    }
    public function tindakan(KalenderAkademikStatusRequest $r, KalenderAkademik $kalenderAkademik, KelolaKalenderAkademik $aksi): RedirectResponse
    {
        $v = AturanKalender::tindakan($r->all());
        Gate::authorize($v['aksi'], $kalenderAkademik);
        $k = $aksi->tindakan($r->user()->id, $kalenderAkademik, $v);
        return redirect()->route('kalender.show', $k)->with('info', 'Status agenda: ' . KalenderAkademik::STATUS[$k->status] . '.');
    }
    private function pilihan(KalenderAkademik $agenda): array
    {
        return [
            'agenda' => $agenda,
            'periode' => DB::table('periode_akademik')->where(fn($q) => $q->whereIn('status', ['persiapan', 'aktif'])->orWhere('id', $agenda->periode_akademik_id))
                ->orderByDesc('id')->get(['id', 'kode', 'status']),
            'prodi' => DB::table('program_studi')->where(fn($q) => $q->where('aktif', true)->orWhere('id', $agenda->program_studi_id))
                ->orderBy('nama')->get(['id', 'nama', 'aktif'])
        ];
    }
}
