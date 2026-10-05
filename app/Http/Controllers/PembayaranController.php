<?php

namespace App\Http\Controllers;

use App\Actions\KelolaPembayaran;
use App\Actions\ProsesVerifikasiPembayaran;
use App\Http\Requests\VerifikasiPembayaranRequest;
use App\Http\Requests\PembayaranRequest;
use App\Http\Requests\PembayaranBatalRequest;
use App\Models\Berkas;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\AksesPembayaran;
use App\Services\BuktiPembayaran;
use App\Services\PenyimpananBerkas;
use App\Services\TujuanPembayaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembayaranController extends Controller
{
    public function index(Request $r, AksesPembayaran $akses): View
    {
        Gate::authorize('viewAny', Pembayaran::class);
        $f = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(Pembayaran::STATUS))],
            'tagihan' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'between:1,100000']
        ]);
        $q = $akses->batasi(Pembayaran::query(), $r->user());
        if (! empty($f['q'])) {
            $q->where('nomor_pengajuan', 'like', '%' . $f['q'] . '%');
        }
        if (! empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        if (! empty($f['tagihan'])) {
            $q->where('tagihan_id', $f['tagihan']);
        }
        return view('pembayaran.index', ['daftar' => $q->orderByDesc('id')->paginate(20)->withQueryString(), 'filter' => $f]);
    }
    public function create(Request $r, Tagihan $tagihan): View
    {
        Gate::authorize('create', [Pembayaran::class, $tagihan]);
        $f = $r->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'between:1,100000']]);
        $q = Berkas::query()->where('diunggah_oleh', $r->user()->id)->where('status', Berkas::TERSEDIA);
        if (! empty($f['q'])) {
            $q->where('label', 'like', '%' . $f['q'] . '%');
        }
        $tujuan = null;
        $versiTujuan = null;
        try {
            $tujuan = TujuanPembayaran::teks();
            $versiTujuan = TujuanPembayaran::versi();
        } catch (ValidationException) { /* Ditampilkan sebagai pemberitahuan. */
        }
        $tagihan->load('mahasiswa.user');
        return view('pembayaran.create', [
            'tagihan' => $tagihan,
            'filter' => $f,
            'berkas' => $q->orderByDesc('id')->paginate(15)->withQueryString(),
            'token' => (string) Str::uuid(),
            'aktif' => Pembayaran::query()->where('tagihan_id', $tagihan->id)->whereIn('status', [Pembayaran::MENUNGGU, Pembayaran::DITERIMA])->first(),
            'tujuan' => $tujuan,
            'versiTujuan' => $versiTujuan
        ]);
    }
    public function store(PembayaranRequest $r, Tagihan $tagihan, KelolaPembayaran $aksi): RedirectResponse
    {
        $p = $aksi->ajukan($r->user()->id, $tagihan, $r->validated());
        return redirect()->route('pembayaran.show', $p)->with('info', 'Pengajuan tercatat. Unggah bukti tidak otomatis melunasi tagihan; periksa status pengajuan.');
    }
    public function show(
        Request $request,
        Pembayaran $pembayaran
    ): View {
        Gate::authorize('view', $pembayaran);

        $request->validate([
            'page' => [
                'nullable',
                'integer',
                'between:1,100000',
            ],
        ]);

        $pembayaran->load([
            'pengunggah',
            'tagihan',
        ]);

        $bolehMelihatAudit = Gate::allows(
            'audit',
            $pembayaran
        );

        $bolehVerifikasi = Gate::allows(
            'verify',
            $pembayaran
        );

        $audit = $bolehMelihatAudit
            ? $pembayaran->audits()
            ->with('pelaku')
            ->orderByDesc('versi_entitas')
            ->paginate(10)
            : null;

        $riwayatVerifikasi = $bolehMelihatAudit
            ? $pembayaran->verifikasi()
            ->with('petugas')
            ->orderByDesc('waktu')
            ->get()
            : collect();

        return view('pembayaran.show', [
            'pembayaran' => $pembayaran,
            'audit' => $audit,
            'riwayatVerifikasi' => $riwayatVerifikasi,
            'bolehVerifikasi' => $bolehVerifikasi,
        ]);
    }
    public function batalkan(PembayaranBatalRequest $r, Pembayaran $pembayaran, KelolaPembayaran $aksi): RedirectResponse
    {
        $p = $aksi->batalkan($r->user()->id, $pembayaran, $r->validated());
        return redirect()->route('pembayaran.show', $p)->with('info', 'Pengajuan dibatalkan. Bukti dan riwayat tetap disimpan; dana bank tidak dikembalikan oleh tindakan ini.');
    }

    public function terima(
        VerifikasiPembayaranRequest $request,
        Pembayaran $pembayaran,
        ProsesVerifikasiPembayaran $aksi
    ): RedirectResponse {
        Gate::authorize('verify', $pembayaran);

        $hasil = $aksi->terima(
            (int) $request->user()->id,
            $pembayaran,
            $request->validated()
        );

        return redirect()
            ->route('pembayaran.show', $hasil)
            ->with(
                'info',
                'Pembayaran berhasil diterima dan riwayat verifikasi telah dicatat.'
            );
    }

    public function tolak(
        VerifikasiPembayaranRequest $request,
        Pembayaran $pembayaran,
        ProsesVerifikasiPembayaran $aksi
    ): RedirectResponse {
        Gate::authorize('verify', $pembayaran);

        $hasil = $aksi->tolak(
            (int) $request->user()->id,
            $pembayaran,
            $request->validated()
        );

        return redirect()
            ->route('pembayaran.show', $hasil)
            ->with(
                'info',
                'Pembayaran berhasil ditolak. Mahasiswa dapat mengajukan bukti baru.'
            );
    }
    public function tautan(Request $r, Pembayaran $pembayaran): RedirectResponse
    {
        $b = $this->bukti($pembayaran);
        return redirect()->to(URL::temporarySignedRoute('pembayaran.unduh', now()->addMinutes(2), [
            'pembayaran' => $pembayaran->id,
            'pemohon' => $r->user()->id,
            'versi_pembayaran' => $pembayaran->revisi,
            'versi_berkas' => $b->revisi
        ]));
    }
    public function unduh(Request $r, Pembayaran $pembayaran, PenyimpananBerkas $storage): StreamedResponse
    {
        $b = $this->bukti($pembayaran);
        $this->token($r, $pembayaran, $b);
        try {
            $stream = $storage->buka($b);
        } catch (\Throwable $e) {
            Log::warning('Unduh bukti pembayaran gagal.', ['pembayaran_id' => $pembayaran->id, 'jenis' => $e::class]);
            abort(503, 'Bukti tidak dapat diunduh. Coba lagi atau hubungi pengelola.');
        }
        try {
            $pembayaran->refresh();
            $b = $this->bukti($pembayaran);
            $this->token($r, $pembayaran, $b);
        } catch (\Throwable $e) {
            fclose($stream);
            throw $e;
        }
        return response()->streamDownload(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 'bukti-' . $pembayaran->nomor_pengajuan . '.' . $b->ekstensi, [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => (string) $b->ukuran_byte,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer'
        ]);
    }
    private function bukti(Pembayaran $p): Berkas
    {
        Gate::authorize('download', $p);
        $b = Berkas::query()->findOrFail($p->bukti_berkas_id);
        abort_unless(BuktiPembayaran::cocok($p, $b), 404);
        return $b;
    }
    private function token(Request $r, Pembayaran $p, Berkas $b): void
    {
        abort_unless($r->query('pemohon') === (string) $r->user()->id && $r->query('versi_pembayaran') === (string) $p->revisi
            && $r->query('versi_berkas') === (string) $b->revisi, 403, 'Tautan berubah. Klik Unduh bukti kembali.');
    }
}
