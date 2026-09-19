<?php

namespace App\Http\Controllers;

use App\Actions\KelolaPengumuman;
use App\Http\Requests\PengumumanRequest;
use App\Http\Requests\PengumumanStatusRequest;
use App\Models\Pengumuman;
use App\Services\AksesMateri;
use App\Services\AksesPengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    public function index(Request $r, AksesPengumuman $akses): View
    {
        Gate::authorize('viewAny', Pengumuman::class);
        $f = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'mode' => ['nullable', Rule::in(['bacaan', 'kelola'])],
            'status' => ['nullable', Rule::in(array_keys(Pengumuman::STATUS))],
            'page' => ['nullable', 'integer', 'between:1,100000']
        ]);
        $mode = $f['mode'] ?? 'bacaan';
        if ($mode === 'kelola') {
            Gate::authorize('create', Pengumuman::class);
        }
        $q = $mode === 'kelola' ? $akses->kelola(Pengumuman::query(), $r->user()) : $akses->bacaan(Pengumuman::query(), $r->user());
        if (! empty($f['q'])) {
            $q->where('judul', 'like', '%' . $f['q'] . '%');
        }
        if ($mode === 'kelola' && ! empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        return view('pengumuman.index', ['daftar' => $q->with('pembuat:id,nama')->orderByDesc('id')->paginate(20)->withQueryString(), 'filter' => $f, 'mode' => $mode]);
    }
    public function create(Request $r): View
    {
        Gate::authorize('create', Pengumuman::class);
        return view('pengumuman.create', $this->pilihan($r, new Pengumuman()) + ['token' => (string) Str::uuid()]);
    }
    public function store(PengumumanRequest $r, KelolaPengumuman $aksi): RedirectResponse
    {
        $p = $aksi->buat($r->user()->id, $r->all());
        return redirect()->route('pengumuman.show', $p)->with('info', 'Draf tersimpan. Periksa sasaran sebelum menerbitkan.');
    }
    public function show(Request $r, Pengumuman $pengumuman, AksesPengumuman $akses): View
    {
        // 404 untuk detail yang tidak boleh dibaca, tanpa membocorkan keberadaan judul.
        abort_unless($akses->membaca($r->user(), $pengumuman), 404);
        $r->validate(['page' => ['nullable', 'integer', 'between:1,100000']]);
        $kelola = $akses->mengelola($r->user(), $pengumuman);
        $pengumuman->load('pembuat:id,nama');
        if ($kelola) {
            $pengumuman->load(['sasaran.programStudi', 'sasaran.kelasKuliah', 'sasaran.role']);
        }
        return view('pengumuman.show', [
            'item' => $pengumuman,
            'kelola' => $kelola,
            'audit' => $kelola ? $pengumuman->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10) : null
        ]);
    }
    public function edit(Request $r, Pengumuman $pengumuman): View
    {
        Gate::authorize('update', $pengumuman);
        return view('pengumuman.edit', $this->pilihan($r, $pengumuman));
    }
    public function update(PengumumanRequest $r, Pengumuman $pengumuman, KelolaPengumuman $aksi): RedirectResponse
    {
        $p = $aksi->ubah($r->user()->id, $pengumuman, $r->all());
        return redirect()->route('pengumuman.show', $p)->with('info', 'Draf tersimpan.');
    }
    public function tindakan(PengumumanStatusRequest $r, Pengumuman $pengumuman, KelolaPengumuman $aksi): RedirectResponse
    {
        $p = $aksi->tindakan($r->user()->id, $pengumuman, $r->all());
        return redirect()->route('pengumuman.show', $p)->with('info', 'Status: ' . Pengumuman::STATUS[$p->status] . '.');
    }
    private function pilihan(Request $r, Pengumuman $p): array
    {
        $v = $r->validate(['q_kelas' => ['nullable', 'string', 'max:100']]);
        $p->load('sasaran');
        $akses = app(AksesPengumuman::class);
        $admin = $akses->admin($r->user());
        $q = app(AksesMateri::class)->kelasKelola($r->user());
        $selected = $p->sasaran->pluck('kelas_kuliah_id')->filter()->all();
        $old = $r->old('sasaran', []);
        if (is_array($old)) {
            foreach (array_slice($old, 0, 10) as $s) {
                if (is_array($s) && isset($s['kelas_kuliah_id']) && is_scalar($s['kelas_kuliah_id']) && ctype_digit((string) $s['kelas_kuliah_id'])) {
                    $selected[] = (int) $s['kelas_kuliah_id'];
                }
            }
        }
        $terpilih = (clone $q)->whereIn('kelas_kuliah.id', $selected)->get(['kelas_kuliah.id', 'kode', 'nama_mk_snapshot', 'status']);
        $q->whereIn('kelas_kuliah.status', ['persiapan', 'aktif'])->whereHas('rombel.periodeAkademik', fn($b) => $b->where('status', 'aktif'));
        if (! empty($v['q_kelas'])) {
            $q->where(fn($b) => $b->where('kode', 'like', '%' . $v['q_kelas'] . '%')->orWhere('nama_mk_snapshot', 'like', '%' . $v['q_kelas'] . '%'));
        }
        $hasil = $q->orderBy('kode')->limit(101)->get(['kelas_kuliah.id', 'kode', 'nama_mk_snapshot', 'status']);
        return [
            'item' => $p,
            'admin' => $admin,
            'qKelas' => $v['q_kelas'] ?? '',
            'lebihKelas' => $hasil->count() > 100,
            'kelas' => $terpilih->merge($hasil->take(100))->unique('id')->sortBy('kode'),
            'prodi' => $admin ? DB::table('program_studi')->where(fn($b) => $b->where('aktif', true)->orWhereIn('id', $p->sasaran->pluck('program_studi_id')->filter()))->orderBy('nama')->get(['id', 'nama', 'aktif']) : collect(),
            'roles' => DB::table('roles')->whereIn('kode', $admin ? AksesPengumuman::ROLES : ['dosen', 'mahasiswa'])->orderBy('kode')->get(['id', 'kode'])
        ];
    }
}
