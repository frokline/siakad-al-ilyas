<?php

namespace App\Http\Controllers;

use App\Actions\KelolaTagihan;
use App\Http\Requests\TagihanRequest;
use App\Http\Requests\TagihanStatusRequest;
use App\Models\JenisBiaya;
use App\Models\Tagihan;
use App\Services\AksesTagihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function index(Request $r, AksesTagihan $akses): View
    {
        Gate::authorize('viewAny', Tagihan::class);
        $filter = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Tagihan::STATUS))],
            'tahun' => ['nullable', 'integer', 'between:2000,2199'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'page' => ['nullable', 'integer', 'between:1,100000']
        ]);
        $q = $akses->batasi(Tagihan::query(), $r->user())->with(['mahasiswa.user', 'jenisBiaya']);
        if (! empty($filter['q'])) {
            $q->where('nomor', 'like', '%' . $filter['q'] . '%');
        }
        foreach (['status' => 'status', 'tahun' => 'tahun_tagihan', 'bulan' => 'bulan_tagihan'] as $key => $col) {
            if (! empty($filter[$key])) {
                $q->where($col, $filter[$key]);
            }
        }
        return view('tagihan.index', ['daftar' => $q->orderByDesc('id')->paginate(20)->withQueryString(), 'filter' => $filter]);
    }
    public function create(Request $r): View
    {
        Gate::authorize('create', Tagihan::class);
        $f = $r->validate(['cari' => ['nullable', 'string', 'max:100'], 'registrasi' => ['nullable', 'integer', 'min:1'], 'page' => ['nullable', 'integer', 'between:1,100000']]);
        $q = $this->registrasi();
        if (! empty($f['cari'])) {
            $q->where(fn($b) => $b->where('m.nim', 'like', '%' . $f['cari'] . '%')->orWhere('u.nama', 'like', '%' . $f['cari'] . '%'));
        }
        $pilihan = ! empty($f['registrasi']) ? $this->registrasi()->where('r.id', $f['registrasi'])->first() : null;
        if (! empty($f['registrasi'])) {
            abort_unless($pilihan, 404);
        }
        return view('tagihan.create', [
            'daftar' => $q->orderByDesc('r.id')->paginate(15)->withQueryString(),
            'filter' => $f,
            'pilihan' => $pilihan,
            'jenis' => JenisBiaya::query()->where('aktif', true)->orderBy('kode')->get(['id', 'kode', 'nama']),
            'tagihan' => new Tagihan(),
            'token' => (string) Str::uuid()
        ]);
    }
    private function registrasi(): \Illuminate\Database\Query\Builder
    {
        return DB::table('registrasi_semester as r')->join('riwayat_studi as s', 's.id', '=', 'r.riwayat_studi_id')
            ->join('mahasiswa as m', 'm.id', '=', 's.mahasiswa_id')->join('users as u', 'u.id', '=', 'm.user_id')
            ->whereIn('r.status', ['terdaftar', 'aktif'])
            ->select(['r.id', 'r.periode_akademik_id', 'r.semester_studi', 'r.status', 'm.nim', 'u.nama']);
    }
    public function store(TagihanRequest $r, KelolaTagihan $aksi): RedirectResponse
    {
        $t = $aksi->buat($r->user()->id, $r->dataTagihan());
        return redirect()->route('tagihan.show', $t)->with('info', 'Draf tersimpan. Periksa detail sebelum menerbitkan.');
    }
    public function show(Request $r, Tagihan $tagihan): View
    {
        Gate::authorize('view', $tagihan);
        $r->validate(['page' => ['nullable', 'integer', 'between:1,100000']]);
        $tagihan->load(['mahasiswa.user', 'jenisBiaya', 'registrasiSemester']);
        $audit = Gate::allows('audit', $tagihan) ? $tagihan->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10) : null;
        return view('tagihan.show', compact('tagihan', 'audit'));
    }
    public function edit(Tagihan $tagihan): View
    {
        Gate::authorize('update', $tagihan);
        $tagihan->load(['mahasiswa.user', 'jenisBiaya']);
        return view('tagihan.edit', compact('tagihan'));
    }
    public function update(TagihanRequest $r, Tagihan $tagihan, KelolaTagihan $aksi): RedirectResponse
    {
        $t = $aksi->ubah($r->user()->id, $tagihan, $r->dataTagihan());
        return redirect()->route('tagihan.show', $t)->with('info', 'Draf tersimpan.');
    }
    public function terbitkan(TagihanStatusRequest $r, Tagihan $tagihan, KelolaTagihan $aksi): RedirectResponse
    {
        return $this->proses($r, $tagihan, $aksi, 'terbitkan');
    }
    public function batalkan(TagihanStatusRequest $r, Tagihan $tagihan, KelolaTagihan $aksi): RedirectResponse
    {
        return $this->proses($r, $tagihan, $aksi, 'batalkan');
    }
    public function bukaDraf(TagihanStatusRequest $r, Tagihan $tagihan, KelolaTagihan $aksi): RedirectResponse
    {
        return $this->proses($r, $tagihan, $aksi, 'buka_draf');
    }
    private function proses(TagihanStatusRequest $r, Tagihan $t, KelolaTagihan $aksi, string $nama): RedirectResponse
    {
        $t = $aksi->transisi($r->user()->id, $t, $nama, $r->validated());
        return redirect()->route('tagihan.show', $t)->with('info', 'Perubahan status tersimpan beserta audit.');
    }
}
