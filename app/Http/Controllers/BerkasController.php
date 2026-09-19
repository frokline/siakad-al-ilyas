<?php

namespace App\Http\Controllers;

use App\Actions\KelolaBerkas;
use App\Http\Requests\BerkasRequest;
use App\Models\Berkas;
use App\Services\PenyimpananBerkas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Berkas::class);
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(array_keys(Berkas::STATUS))],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $query = Berkas::query()->where('diunggah_oleh', $request->user()->id);
        if (! empty($filter['q'])) {
            $query->where('label', 'like', '%' . $filter['q'] . '%');
        }
        if (! empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }
        return view('berkas.index', [
            'daftar' => $query->orderByDesc('id')->paginate(20)->withQueryString(),
            'filter' => $filter,
            'terpakai' => (int) Berkas::query()->where('diunggah_oleh', $request->user()->id)->sum('ukuran_byte'),
            'kuota' => (int) config('berkas.kuota_byte'),
        ]);
    }
    public function create(): View
    {
        Gate::authorize('create', Berkas::class);
        return view('berkas.create', ['token' => (string) Str::uuid()]);
    }
    public function store(BerkasRequest $request, KelolaBerkas $aksi): RedirectResponse
    {
        $file = $aksi->unggah($request->user()->id, $request->file('file'), $request->validated());
        return redirect()->route('berkas.show', $file)->with('info', 'Status unggahan: ' . $file->labelStatus() . '.');
    }
    public function show(Berkas $berkas): View
    {
        Gate::authorize('view', $berkas);
        return view('berkas.show', [
            'file' => $berkas,
            'audit' => $berkas->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(15),
            'zona' => (string) config('siakad.timezone', 'Asia/Makassar')
        ]);
    }
    public function edit(Berkas $berkas): View
    {
        Gate::authorize('update', $berkas);
        return view('berkas.edit', ['file' => $berkas]);
    }
    public function update(BerkasRequest $request, Berkas $berkas, KelolaBerkas $aksi): RedirectResponse
    {
        $file = $aksi->ubah('ubah', $request->user()->id, $berkas, $request->validated());
        return redirect()->route('berkas.show', $file)->with('info', 'Keterangan berkas tersimpan.');
    }
    public function nonaktifkan(BerkasRequest $request, Berkas $berkas, KelolaBerkas $aksi): RedirectResponse
    {
        $file = $aksi->ubah('nonaktifkan', $request->user()->id, $berkas, $request->validated());
        return redirect()->route('berkas.show', $file)->with('info', 'Berkas dinonaktifkan. Riwayat tetap disimpan.');
    }
    public function pulihkan(BerkasRequest $request, Berkas $berkas, KelolaBerkas $aksi): RedirectResponse
    {
        $file = $aksi->ubah('pulihkan', $request->user()->id, $berkas, $request->validated());
        return redirect()->route('berkas.show', $file)->with('info', 'Berkas dipulihkan.');
    }
    public function tautan(Request $request, Berkas $berkas): RedirectResponse
    {
        Gate::authorize('download', $berkas);
        return redirect()->to(URL::temporarySignedRoute(
            'berkas.unduh',
            now()->addMinutes((int) config('berkas.masa_tautan_menit')),
            [
                'berkas' => $berkas->id,
                'pemohon' => $request->user()->id,
                'versi' => $berkas->revisi,
            ]
        ));
    }
    public function unduh(Request $request, Berkas $berkas, PenyimpananBerkas $storage): StreamedResponse
    {
        // Middleware signed memeriksa tanda tangan DAN masa berlaku URL.
        Gate::authorize('download', $berkas);
        abort_unless($request->query('pemohon') === (string) $request->user()->id
            && $request->query('versi') === (string) $berkas->revisi, 403, 'Tautan sudah tidak sesuai. Klik Unduh kembali.');
        try {
            $stream = $storage->buka($berkas);
        } catch (\Throwable $error) {
            Log::warning('Pembacaan objek berkas gagal.', ['berkas_id' => $berkas->id, 'jenis' => $error::class]);
            abort(503, 'Berkas belum dapat diunduh. Coba lagi atau hubungi pengelola.');
        }
        $nama = Str::slug(mb_substr($berkas->label, 0, 100)) ?: 'berkas-' . $berkas->id;
        return response()->streamDownload(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, $nama . '.' . $berkas->ekstensi, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Content-Length' => (string) $berkas->ukuran_byte,
        ]);
    }
}
