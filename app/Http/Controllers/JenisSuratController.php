<?php

namespace App\Http\Controllers;

use App\Actions\KelolaJenisSurat;
use App\Http\Requests\JenisSuratRequest;
use App\Http\Requests\JenisSuratStatusRequest;
use App\Models\JenisSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JenisSuratController extends Controller
{
    public function index(Request $r): View
    {
        Gate::authorize('viewAny', JenisSurat::class);
        $filter = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $q = JenisSurat::query()->select(['id', 'kode', 'nama', 'aktif', 'revisi', 'updated_at']);
        if (! empty($filter['q'])) {
            $q->where(fn($b) => $b->where('kode', 'like', '%' . $filter['q'] . '%')->orWhere('nama', 'like', '%' . $filter['q'] . '%'));
        }
        if (! empty($filter['status'])) {
            $q->where('aktif', $filter['status'] === 'aktif');
        }
        return view('jenis_surat.index', ['daftar' => $q->orderBy('kode')->paginate(20)->withQueryString(), 'filter' => $filter]);
    }
    public function create(): View
    {
        Gate::authorize('create', JenisSurat::class);
        return view('jenis_surat.create', ['jenisSurat' => new JenisSurat(), 'token' => (string) Str::uuid()]);
    }
    public function store(JenisSuratRequest $r, KelolaJenisSurat $aksi): RedirectResponse
    {
        $j = $aksi->buat($r->user()->id, $r->validated());
        return redirect()->route('admin.jenis-surat.show', $j)->with('info', 'Jenis surat tersimpan. Layanan siap digunakan pada modul Permohonan Surat.');
    }
    public function show(Request $r, JenisSurat $jenisSurat): View
    {
        Gate::authorize('view', $jenisSurat);
        $r->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $jenisSurat->load('pembuat');
        return view('jenis_surat.show', [
            'jenisSurat' => $jenisSurat,
            'audit' => $jenisSurat->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10),
            'zona' => (string) config('siakad.timezone', 'Asia/Makassar')
        ]);
    }
    public function edit(JenisSurat $jenisSurat): View
    {
        Gate::authorize('update', $jenisSurat);
        return view('jenis_surat.edit', ['jenisSurat' => $jenisSurat]);
    }
    public function update(JenisSuratRequest $r, JenisSurat $jenisSurat, KelolaJenisSurat $aksi): RedirectResponse
    {
        $j = $aksi->ubah($r->user()->id, $jenisSurat, $r->validated());
        return redirect()->route('admin.jenis-surat.show', $j)->with('info', 'Data jenis surat tersimpan.');
    }
    public function nonaktifkan(JenisSuratStatusRequest $r, JenisSurat $jenisSurat, KelolaJenisSurat $aksi): RedirectResponse
    {
        $j = $aksi->status($r->user()->id, $jenisSurat, false, $r->validated());
        return redirect()->route('admin.jenis-surat.show', $j)->with('info', 'Jenis surat dinonaktifkan. Riwayat tetap disimpan.');
    }
    public function aktifkan(JenisSuratStatusRequest $r, JenisSurat $jenisSurat, KelolaJenisSurat $aksi): RedirectResponse
    {
        $j = $aksi->status($r->user()->id, $jenisSurat, true, $r->validated());
        return redirect()->route('admin.jenis-surat.show', $j)->with('info', 'Jenis surat diaktifkan kembali.');
    }
}
