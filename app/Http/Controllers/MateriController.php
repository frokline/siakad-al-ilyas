<?php

namespace App\Http\Controllers;

use App\Actions\KelolaMateri;
use App\Http\Requests\MateriRequest;
use App\Http\Requests\MateriStatusRequest;
use App\Models\Berkas;
use App\Models\KelasKuliah;
use App\Models\Materi;
use App\Models\MateriBerkas;
use App\Models\Pertemuan;
use App\Services\AksesMateri;
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

class MateriController extends Controller
{
    public function index(Request $request, AksesMateri $akses): View
    {
        Gate::authorize('viewAny', Materi::class);
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(array_keys(Materi::STATUS))],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $q = $akses->batasi(Materi::query(), $request->user())->with('kelasKuliah');
        if (! empty($filter['q'])) {
            $q->where('judul', 'like', '%' . $filter['q'] . '%');
        }
        if (! empty($filter['kelas'])) {
            $q->where('kelas_kuliah_id', $filter['kelas']);
        }
        if (! empty($filter['status'])) {
            $q->where('status', $filter['status']);
        }
        return view('materi.index', [
            'daftar' => $q->orderByDesc('id')->paginate(20)->withQueryString(),
            'filter' => $filter,
            'pengelola' => $akses->pengelola($request->user())
        ]);
    }
    public function kelas(Request $request, AksesMateri $akses): View
    {
        abort_unless($akses->pengelola($request->user()), 403);
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $q = $akses->kelasKelola($request->user());
        if (! empty($filter['q'])) {
            $q->where(fn($b) => $b->where('kode', 'like', '%' . $filter['q'] . '%')->orWhere('nama_mk_snapshot', 'like', '%' . $filter['q'] . '%'));
        }
        return view('materi.kelas', ['daftar' => $q->orderByDesc('id')->paginate(20)->withQueryString(), 'filter' => $filter]);
    }
    public function create(Request $request): View
    {
        $data = $request->validate(['kelas' => ['required', 'integer', 'min:1']]);
        $kelas = KelasKuliah::query()->findOrFail($data['kelas']);
        Gate::authorize('create', [Materi::class, $kelas]);
        return view('materi.create', $this->form($kelas) + ['materi' => new Materi(), 'token' => (string) Str::uuid()]);
    }
    public function store(MateriRequest $request, KelolaMateri $aksi): RedirectResponse
    {
        $m = $aksi->buat($request->user()->id, $request->validated());
        return redirect()->route('materi.show', $m)->with('info', 'Draf materi tersimpan. Periksa isinya sebelum diterbitkan.');
    }
    public function show(Materi $materi): View
    {
        Gate::authorize('view', $materi);
        $materi->load(['kelasKuliah', 'pertemuan', 'pembuat', 'lampiran.berkas']);
        return view('materi.show', [
            'materi' => $materi,
            'audit' => Gate::allows('manage', $materi) ? $materi->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10) : null,
            'zona' => (string) config('siakad.timezone', 'Asia/Makassar')
        ]);
    }
    public function edit(Materi $materi): View
    {
        Gate::authorize('update', $materi);
        $materi->load(['kelasKuliah', 'lampiran.berkas']);
        return view('materi.edit', $this->form($materi->kelasKuliah) + ['materi' => $materi]);
    }
    public function update(MateriRequest $request, Materi $materi, KelolaMateri $aksi): RedirectResponse
    {
        $m = $aksi->ubah($request->user()->id, $materi, $request->validated());
        return redirect()->route('materi.show', $m)->with('info', 'Draf dan daftar lampiran diperbarui.');
    }
    public function terbitkan(MateriStatusRequest $r, Materi $materi, KelolaMateri $a): RedirectResponse
    {
        return $this->status('terbitkan', $r, $materi, $a);
    }
    public function tarik(MateriStatusRequest $r, Materi $materi, KelolaMateri $a): RedirectResponse
    {
        return $this->status('tarik', $r, $materi, $a);
    }
    public function arsipkan(MateriStatusRequest $r, Materi $materi, KelolaMateri $a): RedirectResponse
    {
        return $this->status('arsipkan', $r, $materi, $a);
    }
    public function pulihkan(MateriStatusRequest $r, Materi $materi, KelolaMateri $a): RedirectResponse
    {
        return $this->status('pulihkan', $r, $materi, $a);
    }

    public function tautan(Request $request, Materi $materi, MateriBerkas $lampiran): RedirectResponse
    {
        $b = $this->lampiran($materi, $lampiran);
        return redirect()->to(URL::temporarySignedRoute('materi.unduh', now()->addMinutes((int) config('materi.masa_tautan_menit', 2)), [
            'materi' => $materi->id,
            'lampiran' => $lampiran->id,
            'pemohon' => $request->user()->id,
            'versi_materi' => $materi->revisi,
            'versi_berkas' => $b->revisi,
        ]));
    }
    public function unduh(Request $request, Materi $materi, MateriBerkas $lampiran, PenyimpananBerkas $storage): StreamedResponse
    {
        $b = $this->lampiran($materi, $lampiran);
        $this->tokenUnduh($request, $materi, $b);
        try {
            $stream = $storage->buka($b);
        } catch (\Throwable $e) {
            Log::warning('Unduh lampiran materi gagal.', ['materi_id' => $materi->id, 'berkas_id' => $b->id, 'jenis' => $e::class]);
            abort(503, 'Lampiran belum dapat diunduh. Coba lagi atau hubungi pengelola.');
        }
        // Jangan menahan transaksi database saat membaca cloud. Periksa akses lagi setelah pembacaan.
        try {
            $materi->refresh();
            $lampiran->refresh();
            $b = $this->lampiran($materi, $lampiran);
            $this->tokenUnduh($request, $materi, $b);
        } catch (\Throwable $e) {
            fclose($stream);
            throw $e;
        }
        $nama = Str::slug(mb_substr($b->label, 0, 100)) ?: 'lampiran-' . $b->id;
        return response()->streamDownload(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, $nama . '.' . $b->ekstensi, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Content-Length' => (string) $b->ukuran_byte
        ]);
    }

    private function lampiran(Materi $m, MateriBerkas $p): Berkas
    {
        Gate::authorize('view', $m);
        abort_unless($p->materi_id === $m->id && $p->aktif, 404);
        $file = Berkas::query()->findOrFail($p->berkas_id);
        abort_unless($file->status === Berkas::TERSEDIA, 404);
        return $file;
    }
    private function tokenUnduh(Request $r, Materi $m, Berkas $b): void
    {
        abort_unless($r->query('pemohon') === (string) $r->user()->id
            && $r->query('versi_materi') === (string) $m->revisi
            && $r->query('versi_berkas') === (string) $b->revisi, 403, 'Tautan berubah. Klik Unduh kembali.');
    }
    private function form(KelasKuliah $kelas): array
    {
        return [
            'kelas' => $kelas,
            'pertemuan' => Pertemuan::query()->where('kelas_kuliah_id', $kelas->id)->where('status', '!=', 'batal')->orderBy('nomor')->get(),
            'hostTautan' => config('materi.host_tautan', [])
        ];
    }
    private function status(string $aksi, MateriStatusRequest $r, Materi $m, KelolaMateri $a): RedirectResponse
    {
        $m = $a->status($aksi, $r->user()->id, $m, $r->validated());
        return redirect()->route('materi.show', $m)->with('info', 'Status materi: ' . Materi::STATUS[$m->status] . '.');
    }
}
