<?php

namespace App\Http\Controllers;

use App\Actions\KelolaKegiatan;
use App\Http\Requests\KegiatanRequest;
use App\Http\Requests\KegiatanStatusRequest;
use App\Models\Berkas;
use App\Models\KelasKuliah;
use App\Models\Kegiatan;
use App\Models\KegiatanBerkas;
use App\Models\Pertemuan;
use App\Services\AksesKegiatan;
use App\Services\PenyimpananBerkas;
use App\Services\WaktuKegiatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KegiatanController extends Controller
{
    public function index(Request $request, AksesKegiatan $akses): View
    {
        Gate::authorize('viewAny', Kegiatan::class);
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kelas' => ['nullable', 'integer', 'min:1'],
            'jenis' => ['nullable', 'string', Rule::in(array_keys(Kegiatan::JENIS))],
            'status' => ['nullable', 'string', Rule::in(array_keys(Kegiatan::STATUS))],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $q = $akses->batasi(Kegiatan::query(), $request->user())->with('kelasKuliah')
            ->select(['kegiatan.id', 'kelas_kuliah_id', 'judul', 'jenis', 'status', 'buka_at', 'tenggat_at', 'terbit_at']);
        if (! empty($filter['q'])) {
            $q->where('judul', 'like', '%' . $filter['q'] . '%');
        }
        if (! empty($filter['kelas'])) {
            $q->where('kelas_kuliah_id', $filter['kelas']);
        }
        if (! empty($filter['status'])) {
            $q->where('status', $filter['status']);
        }
        if (! empty($filter['jenis'])) {
            $q->where('jenis', $filter['jenis']);
        }
        return view('kegiatan.index', [
            'daftar' => $q->orderByDesc('id')->paginate(20)->withQueryString(),
            'filter' => $filter,
            'pengelola' => $akses->pengelola($request->user()),
            'zona' => WaktuKegiatan::zona()
        ]);
    }
    public function kelas(Request $request, AksesKegiatan $akses): View
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
        return view('kegiatan.kelas', ['daftar' => $q->orderByDesc('id')->paginate(20)->withQueryString(), 'filter' => $filter]);
    }
    public function create(Request $request): View
    {
        $data = $request->validate(['kelas' => ['required', 'integer', 'min:1']]);
        $kelas = KelasKuliah::query()->findOrFail($data['kelas']);
        Gate::authorize('create', [Kegiatan::class, $kelas]);
        return view('kegiatan.create', $this->form($kelas) + ['kegiatan' => new Kegiatan(), 'token' => (string) Str::uuid()]);
    }
    public function store(KegiatanRequest $request, KelolaKegiatan $aksi): RedirectResponse
    {
        $m = $aksi->buat($request->user()->id, $request->validated());
        return redirect()->route('kegiatan.show', $m)->with('info', 'Draf kegiatan tersimpan. Periksa isinya sebelum diterbitkan.');
    }
    public function show(Request $request, Kegiatan $kegiatan): View
    {
        Gate::authorize('view', $kegiatan);
        $request->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $bacaIsi = Gate::allows('bacaIsi', $kegiatan);
        $kegiatan->load(['kelasKuliah', 'pertemuan', 'pembuat']);
        if ($bacaIsi) {
            $kegiatan->load('lampiran.berkas');
        }
        return view('kegiatan.show', [
            'kegiatan' => $kegiatan,
            'audit' => Gate::allows('manage', $kegiatan) ? $kegiatan->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10) : null,
            'zona' => WaktuKegiatan::zona(),
            'bacaIsi' => $bacaIsi
        ]);
    }
    public function edit(Kegiatan $kegiatan): View
    {
        Gate::authorize('update', $kegiatan);
        $kegiatan->load(['kelasKuliah', 'lampiran.berkas']);
        return view('kegiatan.edit', $this->form($kegiatan->kelasKuliah) + ['kegiatan' => $kegiatan]);
    }
    public function update(KegiatanRequest $request, Kegiatan $kegiatan, KelolaKegiatan $aksi): RedirectResponse
    {
        $m = $aksi->ubah($request->user()->id, $kegiatan, $request->validated());
        return redirect()->route('kegiatan.show', $m)->with('info', 'Draf dan daftar lampiran diperbarui.');
    }
    public function terbitkan(KegiatanStatusRequest $r, Kegiatan $kegiatan, KelolaKegiatan $a): RedirectResponse
    {
        return $this->status('terbitkan', $r, $kegiatan, $a);
    }
    public function tutup(KegiatanStatusRequest $r, Kegiatan $kegiatan, KelolaKegiatan $a): RedirectResponse
    {
        return $this->status('tutup', $r, $kegiatan, $a);
    }
    public function bukaKembali(KegiatanStatusRequest $r, Kegiatan $kegiatan, KelolaKegiatan $a): RedirectResponse
    {
        return $this->status('bukaKembali', $r, $kegiatan, $a);
    }
    public function perpanjang(KegiatanStatusRequest $r, Kegiatan $kegiatan, KelolaKegiatan $a): RedirectResponse
    {
        return $this->status('perpanjang', $r, $kegiatan, $a);
    }
    public function arsipkan(KegiatanStatusRequest $r, Kegiatan $kegiatan, KelolaKegiatan $a): RedirectResponse
    {
        return $this->status('arsipkan', $r, $kegiatan, $a);
    }
    public function pulihkan(KegiatanStatusRequest $r, Kegiatan $kegiatan, KelolaKegiatan $a): RedirectResponse
    {
        return $this->status('pulihkan', $r, $kegiatan, $a);
    }

    public function tautan(Request $request, Kegiatan $kegiatan, KegiatanBerkas $lampiran): RedirectResponse
    {
        $b = $this->lampiran($kegiatan, $lampiran);
        return redirect()->to(URL::temporarySignedRoute('kegiatan.unduh', now()->addMinutes((int) config('kegiatan.masa_tautan_menit', 2)), [
            'kegiatan' => $kegiatan->id,
            'lampiran' => $lampiran->id,
            'pemohon' => $request->user()->id,
            'versi_kegiatan' => $kegiatan->revisi,
            'versi_berkas' => $b->revisi,
        ]));
    }
    public function unduh(Request $request, Kegiatan $kegiatan, KegiatanBerkas $lampiran, PenyimpananBerkas $storage): StreamedResponse
    {
        $b = $this->lampiran($kegiatan, $lampiran);
        $this->tokenUnduh($request, $kegiatan, $b);
        try {
            $stream = $storage->buka($b);
        } catch (\Throwable $e) {
            Log::warning('Unduh lampiran kegiatan gagal.', ['kegiatan_id' => $kegiatan->id, 'berkas_id' => $b->id, 'jenis' => $e::class]);
            abort(503, 'Lampiran belum dapat diunduh. Coba lagi atau hubungi pengelola.');
        }
        // Jangan menahan transaksi database saat membaca cloud. Periksa akses lagi setelah pembacaan.
        try {
            $kegiatan->refresh();
            $lampiran->refresh();
            $b = $this->lampiran($kegiatan, $lampiran);
            $this->tokenUnduh($request, $kegiatan, $b);
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

    private function lampiran(Kegiatan $m, KegiatanBerkas $p): Berkas
    {
        Gate::authorize('bacaIsi', $m);
        abort_unless($p->kegiatan_id === $m->id && $p->aktif, 404);
        $file = Berkas::query()->findOrFail($p->berkas_id);
        abort_unless($file->status === Berkas::TERSEDIA, 404);
        return $file;
    }
    private function tokenUnduh(Request $r, Kegiatan $m, Berkas $b): void
    {
        abort_unless($r->query('pemohon') === (string) $r->user()->id
            && $r->query('versi_kegiatan') === (string) $m->revisi
            && $r->query('versi_berkas') === (string) $b->revisi, 403, 'Tautan berubah. Klik Unduh kembali.');
    }
    private function form(KelasKuliah $kelas): array
    {
        return [
            'kelas' => $kelas,
            'pertemuan' => Pertemuan::query()->where('kelas_kuliah_id', $kelas->id)->where('status', '!=', 'batal')->orderBy('nomor')->get(),
            'zona' => WaktuKegiatan::zona()
        ];
    }
    private function status(string $aksi, KegiatanStatusRequest $r, Kegiatan $m, KelolaKegiatan $a): RedirectResponse
    {
        $m = $a->status($aksi, $r->user()->id, $m, $r->validated());
        return redirect()->route('kegiatan.show', $m)->with('info', 'Status kegiatan: ' . Kegiatan::STATUS[$m->status] . '.');
    }
}
