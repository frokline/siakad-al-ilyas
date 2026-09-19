<?php

namespace App\Http\Controllers;

use App\Actions\KelolaPermohonanSurat;
use App\Http\Requests\PermohonanSuratRequest;
use App\Models\Berkas;
use App\Models\JenisSurat;
use App\Models\PermohonanSurat;
use App\Services\AksesSurat;
use App\Services\BerkasSurat;
use App\Services\KonteksSurat;
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

class PermohonanSuratController extends Controller
{
    public function index(Request $r, AksesSurat $akses): View
    {
        Gate::authorize('viewAny', PermohonanSurat::class);
        $f = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(PermohonanSurat::STATUS))],
            'page' => ['nullable', 'integer', 'between:1,100000']
        ]);
        $q = $akses->batasi(PermohonanSurat::query(), $r->user());
        if (! empty($f['q'])) {
            $q->where(fn($b) => $b->where('nomor_pengajuan', 'like', '%' . $f['q'] . '%')->orWhere('nomor_surat', 'like', '%' . $f['q'] . '%'));
        }
        if (! empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        return view('permohonan_surat.index', ['daftar' => $q->orderByDesc('id')->paginate(20)->withQueryString(), 'filter' => $f]);
    }
    public function create(Request $r, KonteksSurat $k): View
    {
        Gate::authorize('create', PermohonanSurat::class);
        $filter = $this->filterBerkas($r);
        return view('permohonan_surat.create', [
            'registrasi' => $k->pilihan($r->user()->id)->limit(100)->get(),
            'jenis' => JenisSurat::query()->where('kode', JenisSurat::AKTIF_KULIAH)->where('aktif', true)->first(),
            'berkas' => $this->berkas($r, $filter, false),
            'filter' => $filter,
            'token' => (string) Str::uuid()
        ]);
    }
    public function store(PermohonanSuratRequest $r, KelolaPermohonanSurat $aksi): RedirectResponse
    {
        $p = $aksi->ajukan($r->user()->id, $r->dataSurat());
        return redirect()->route('surat.show', $p)->with('info', 'Permohonan tercatat. Pantau proses pada halaman ini.');
    }
    public function show(Request $r, PermohonanSurat $permohonanSurat): View
    {
        Gate::authorize('view', $permohonanSurat);
        $filter = $this->filterBerkas($r);
        $permohonanSurat->load(['riwayat' => fn($q) => $q->orderBy('revisi_permohonan'), 'riwayat.pelaku']);
        $pilihan = array_filter(PermohonanSurat::TRANSISI[$permohonanSurat->status], fn($tujuan) => Gate::allows('tindakan', [$permohonanSurat, $tujuan]));
        return view('permohonan_surat.show', [
            'p' => $permohonanSurat,
            'pilihan' => $pilihan,
            'filter' => $filter,
            'berkas' => in_array('terbit', $pilihan, true) ? $this->berkas($r, $filter, true) : null
        ]);
    }
    public function tindakan(PermohonanSuratRequest $r, PermohonanSurat $permohonanSurat, KelolaPermohonanSurat $aksi): RedirectResponse
    {
        $v = $r->dataSurat();
        Gate::authorize('tindakan', [$permohonanSurat, $v['tujuan']]);
        $p = $aksi->tindakan($r->user()->id, $permohonanSurat, $v);
        return redirect()->route('surat.show', $p)->with('info', 'Status berubah menjadi ' . PermohonanSurat::STATUS[$p->status] . '.');
    }
    public function tautan(Request $r, PermohonanSurat $permohonanSurat, string $bagian): RedirectResponse
    {
        $b = $this->dokumen($permohonanSurat, $bagian);
        return redirect()->to(URL::temporarySignedRoute('surat.unduh', now()->addMinutes(2), [
            'permohonanSurat' => $permohonanSurat->id,
            'bagian' => $bagian,
            'pemohon' => $r->user()->id,
            'versi' => $permohonanSurat->revisi,
            'versi_berkas' => $b->revisi
        ]));
    }
    public function unduh(Request $r, PermohonanSurat $permohonanSurat, string $bagian, PenyimpananBerkas $storage): StreamedResponse
    {
        $b = $this->dokumen($permohonanSurat, $bagian);
        $this->token($r, $permohonanSurat, $b);
        try {
            $stream = $storage->buka($b);
        } catch (\Throwable $e) {
            Log::warning('Unduh surat gagal.', ['permohonan_id' => $permohonanSurat->id, 'bagian' => $bagian, 'jenis' => $e::class]);
            abort(503, 'Berkas tidak dapat diunduh. Hubungi bagian akademik.');
        }
        try {
            $permohonanSurat->refresh();
            $b = $this->dokumen($permohonanSurat, $bagian);
            $this->token($r, $permohonanSurat, $b);
        } catch (\Throwable $e) {
            fclose($stream);
            throw $e;
        }
        return response()->streamDownload(
            function () use ($stream): void {
                try {
                    fpassthru($stream);
                } finally {
                    fclose($stream);
                }
            },
            $bagian . '-' . $permohonanSurat->nomor_pengajuan . '.' . $b->ekstensi,
            [
                'Content-Type' => 'application/octet-stream',
                'Content-Length' => (string) $b->ukuran_byte,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
                'Referrer-Policy' => 'no-referrer'
            ]
        );
    }
    private function dokumen(PermohonanSurat $p, string $bagian): Berkas
    {
        Gate::authorize('download', [$p, $bagian]);
        $id = $bagian === 'lampiran' ? $p->lampiran_berkas_id : $p->hasil_berkas_id;
        $snapshot = $bagian === 'lampiran' ? $p->lampiran_snapshot : $p->hasil_snapshot;
        $b = Berkas::query()->findOrFail($id);
        abort_unless(BerkasSurat::cocok($b, $snapshot), 404);
        return $b;
    }
    private function token(Request $r, PermohonanSurat $p, Berkas $b): void
    {
        abort_unless($r->query('pemohon') === (string) $r->user()->id && $r->query('versi') === (string) $p->revisi
            && $r->query('versi_berkas') === (string) $b->revisi, 403, 'Tautan tidak berlaku. Klik Unduh kembali.');
    }
    private function filterBerkas(Request $r): array
    {
        return $r->validate(['q_berkas' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'between:1,100000']]);
    }
    private function berkas(Request $r, array $f, bool $hasil): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $q = Berkas::query()->where('diunggah_oleh', $r->user()->id)->where('status', Berkas::TERSEDIA)
            ->whereIn('ekstensi', $hasil ? ['pdf'] : ['pdf', 'jpg', 'png']);
        if (! empty($f['q_berkas'])) {
            $q->where('label', 'like', '%' . $f['q_berkas'] . '%');
        }
        return $q->orderByDesc('id')->paginate(15)->withQueryString();
    }
}
