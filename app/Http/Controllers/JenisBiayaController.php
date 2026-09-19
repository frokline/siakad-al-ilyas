<?php

namespace App\Http\Controllers;

use App\Actions\KelolaJenisBiaya;
use App\Http\Requests\JenisBiayaRequest;
use App\Http\Requests\JenisBiayaStatusRequest;
use App\Models\JenisBiaya;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JenisBiayaController extends Controller
{
    public function index(Request $r): View
    {
        Gate::authorize('viewAny', JenisBiaya::class);
        $filter = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $q = JenisBiaya::query()->select(['id', 'kode', 'nama', 'aktif', 'revisi', 'updated_at']);
        if (! empty($filter['q'])) {
            $q->where(fn($b) => $b->where('kode', 'like', '%' . $filter['q'] . '%')->orWhere('nama', 'like', '%' . $filter['q'] . '%'));
        }
        if (! empty($filter['status'])) {
            $q->where('aktif', $filter['status'] === 'aktif');
        }
        return view('jenis_biaya.index', ['daftar' => $q->orderBy('kode')->paginate(20)->withQueryString(), 'filter' => $filter]);
    }
    public function create(): View
    {
        Gate::authorize('create', JenisBiaya::class);
        return view('jenis_biaya.create', ['jenisBiaya' => new JenisBiaya(), 'token' => (string) Str::uuid()]);
    }
    public function store(JenisBiayaRequest $r, KelolaJenisBiaya $aksi): RedirectResponse
    {
        $j = $aksi->buat($r->user()->id, $r->validated());
        return redirect()->route('keuangan.jenis-biaya.show', $j)->with('info', 'Jenis biaya tersimpan. Belum ada tagihan yang dibuat.');
    }
    public function show(Request $r, JenisBiaya $jenisBiaya): View
    {
        Gate::authorize('view', $jenisBiaya);
        $r->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $jenisBiaya->load('pembuat');
        return view('jenis_biaya.show', [
            'jenisBiaya' => $jenisBiaya,
            'audit' => $jenisBiaya->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10),
            'zona' => (string) config('siakad.timezone', 'Asia/Makassar')
        ]);
    }
    public function edit(JenisBiaya $jenisBiaya): View
    {
        Gate::authorize('update', $jenisBiaya);
        return view('jenis_biaya.edit', ['jenisBiaya' => $jenisBiaya]);
    }
    public function update(JenisBiayaRequest $r, JenisBiaya $jenisBiaya, KelolaJenisBiaya $aksi): RedirectResponse
    {
        $j = $aksi->ubah($r->user()->id, $jenisBiaya, $r->validated());
        return redirect()->route('keuangan.jenis-biaya.show', $j)->with('info', 'Data jenis biaya tersimpan.');
    }
    public function nonaktifkan(JenisBiayaStatusRequest $r, JenisBiaya $jenisBiaya, KelolaJenisBiaya $aksi): RedirectResponse
    {
        $j = $aksi->status($r->user()->id, $jenisBiaya, false, $r->validated());
        return redirect()->route('keuangan.jenis-biaya.show', $j)->with('info', 'Jenis biaya dinonaktifkan. Riwayat tetap disimpan.');
    }
    public function aktifkan(JenisBiayaStatusRequest $r, JenisBiaya $jenisBiaya, KelolaJenisBiaya $aksi): RedirectResponse
    {
        $j = $aksi->status($r->user()->id, $jenisBiaya, true, $r->validated());
        return redirect()->route('keuangan.jenis-biaya.show', $j)->with('info', 'Jenis biaya diaktifkan kembali.');
    }
}
