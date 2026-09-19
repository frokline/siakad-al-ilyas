<?php

namespace App\Http\Controllers;

use App\Actions\KelolaNotifikasi;
use App\Http\Requests\NotifikasiRequest;
use App\Models\Notifikasi;
use App\Services\{AksesNotifikasi, SumberNotifikasi};
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $r, AksesNotifikasi $akses, SumberNotifikasi $sumber): View
    {
        Gate::authorize('viewAny', Notifikasi::class);
        $v = $r->validate([
            'status' => ['nullable', Rule::in(['semua', 'belum', 'sudah'])],
            'jenis' => ['nullable', Rule::in($sumber->aktif())],
            'page' => ['nullable', 'integer', 'between:1,100000']
        ]);
        $base = $akses->batasi(Notifikasi::query(), $r->user());
        $belum = (clone $base)->whereNull('dibaca_at')->count();
        if (($v['status'] ?? '') === 'belum') {
            $base->whereNull('dibaca_at');
        }
        if (($v['status'] ?? '') === 'sudah') {
            $base->whereNotNull('dibaca_at');
        }
        if (! empty($v['jenis'])) {
            $base->where('jenis', $v['jenis']);
        }
        return view('notifikasi.index', [
            'daftar' => $base->orderByDesc('id')->paginate(20)->withQueryString(),
            'belum' => $belum,
            'filter' => $v,
            'jenisAktif' => $sumber->aktif()
        ]);
    }
    public function baca(NotifikasiRequest $r, Notifikasi $notifikasi, KelolaNotifikasi $aksi): RedirectResponse
    {
        $aksi->baca((int) $r->user()->id, (int) $notifikasi->id);
        return redirect()->route('notifikasi.index')->with('info', 'Notifikasi ditandai sudah dibaca.');
    }
    public function buka(NotifikasiRequest $r, Notifikasi $notifikasi, KelolaNotifikasi $aksi): RedirectResponse
    {
        $tujuan = $aksi->baca((int) $r->user()->id, (int) $notifikasi->id, true);
        return redirect()->to($tujuan);
    }
    public function bacaHalaman(NotifikasiRequest $r, KelolaNotifikasi $aksi): RedirectResponse
    {
        $jumlah = $aksi->bacaHalaman((int) $r->user()->id, $r->validated());
        return redirect()->route('notifikasi.index')->with('info', "{$jumlah} notifikasi ditandai sudah dibaca.");
    }
}
