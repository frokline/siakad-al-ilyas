<?php

namespace App\Http\Controllers;

use App\Actions\KelolaPengumpulan;
use App\Http\Requests\PengumpulanDrafRequest;
use App\Http\Requests\PengumpulanKirimRequest;
use App\Http\Requests\PengumpulanRequest;
use App\Models\Berkas;
use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Models\PengumpulanBerkas;
use App\Services\AksesPengumpulan;
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

class PengumpulanController extends Controller
{
    public function index(Request $r, AksesPengumpulan $akses): View
    {
        Gate::authorize('viewAny', Pengumpulan::class);
        $filter = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kegiatan' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['draf', 'dikirim', 'berlaku'])],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $q = $akses->batasi(Pengumpulan::query(), $r->user())->select([
            'pengumpulan.id',
            'kegiatan_id',
            'detail_krs_id',
            'pemilik_id',
            'versi',
            'status',
            'dikirim_at',
            'updated_at'
        ])->with(['pemilik:id,nama', 'kegiatan:id,kelas_kuliah_id,judul', 'kegiatan.kelasKuliah']);
        if (! empty($filter['q'])) {
            $q->whereHas('kegiatan', fn($k) => $k->where('judul', 'like', '%' . $filter['q'] . '%'));
        }
        if (! empty($filter['kegiatan'])) {
            $q->where('kegiatan_id', $filter['kegiatan']);
        }
        if (($filter['status'] ?? null) === 'berlaku') {
            $q->berlaku();
        } elseif (! empty($filter['status'])) {
            $q->where('pengumpulan.status', $filter['status']);
        }
        $daftar = $q->orderByDesc('id')->paginate(20)->withQueryString();
        return view('pengumpulan.index', [
            'daftar' => $daftar,
            'filter' => $filter,
            'zona' => WaktuKegiatan::zona(),
            'berlakuIds' => Pengumpulan::query()->berlaku()->whereKey($daftar->getCollection()->modelKeys())->pluck('id')->map(fn($id) => (int) $id)->all()
        ]);
    }
    public function saya(Request $r, Kegiatan $kegiatan, AksesPengumpulan $akses): View
    {
        Gate::authorize('ruangSaya', [Pengumpulan::class, $kegiatan]);
        $r->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $kegiatan->load('kelasKuliah');
        $detailId = $akses->detail($r->user(), $kegiatan);
        $q = Pengumpulan::query()->where('kegiatan_id', $kegiatan->id)->where('pemilik_id', $r->user()->id);
        $daftar = (clone $q)->select(['id', 'kegiatan_id', 'detail_krs_id', 'versi', 'status', 'dikirim_at', 'updated_at'])
            ->orderByDesc('id')->paginate(15)->withQueryString();
        return view('pengumpulan.saya', [
            'kegiatan' => $kegiatan,
            'daftar' => $daftar,
            'berlaku' => (clone $q)->berlaku()->get(['id', 'versi', 'detail_krs_id', 'dikirim_at']),
            'draf' => $detailId === null ? null : (clone $q)->where('detail_krs_id', $detailId)->where('status', Pengumpulan::DRAF)->first(),
            'dasarVersi' => $detailId === null ? 0 : (int) (clone $q)->where('detail_krs_id', $detailId)->max('versi'),
            'token' => (string) Str::uuid(),
            'bolehTulis' => $akses->bolehTulis($r->user(), $kegiatan),
            'zona' => WaktuKegiatan::zona()
        ]);
    }
    public function rekap(Request $r, Kegiatan $kegiatan, AksesPengumpulan $akses): View
    {
        Gate::authorize('rekap', [Pengumpulan::class, $kegiatan]);
        $filter = $r->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['sudah', 'belum'])],
            'peserta_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'kiriman_page' => ['nullable', 'integer', 'min:1', 'max:100000']
        ]);
        $kegiatan->load('kelasKuliah');
        $efektif = Pengumpulan::query()->berlaku()->where('kegiatan_id', $kegiatan->id)
            ->select(['pengumpulan.id', 'detail_krs_id', 'versi', 'dikirim_at']);
        $peserta = $akses->peserta($kegiatan->kelas_kuliah_id)->leftJoinSub($efektif, 'jawaban', fn($join) => $join->on('jawaban.detail_krs_id', '=', 'd.id'));
        $total = (clone $peserta)->count('d.id');
        $sudah = (clone $peserta)->whereNotNull('jawaban.id')->count('d.id');
        if (! empty($filter['q'])) {
            $peserta->where(fn($q) => $q->where('u.nama', 'like', '%' . $filter['q'] . '%')->orWhere('m.nim', 'like', '%' . $filter['q'] . '%'));
        }
        if (($filter['status'] ?? null) === 'sudah') {
            $peserta->whereNotNull('jawaban.id');
        }
        if (($filter['status'] ?? null) === 'belum') {
            $peserta->whereNull('jawaban.id');
        }
        // Riwayat ini hanya kiriman final; tetap ada ketika peserta kemudian nonaktif/KRS dicabut.
        $historis = Pengumpulan::query()->where('kegiatan_id', $kegiatan->id)->where('status', Pengumpulan::DIKIRIM)
            ->select(['id', 'kegiatan_id', 'detail_krs_id', 'pemilik_id', 'versi', 'dikirim_at'])->with('pemilik:id,nama');
        return view('pengumpulan.rekap', [
            'kegiatan' => $kegiatan,
            'filter' => $filter,
            'total' => $total,
            'sudah' => $sudah,
            'peserta' => $peserta->orderBy('m.nim')->orderBy('d.id')->paginate(
                20,
                ['d.id as detail_id', 'm.nim', 'u.nama', 'jawaban.id as pengumpulan_id', 'jawaban.versi', 'jawaban.dikirim_at'],
                'peserta_page'
            )->withQueryString(),
            'historis' => $historis->orderByDesc('id')->paginate(20, ['*'], 'kiriman_page')->withQueryString(),
            'zona' => WaktuKegiatan::zona()
        ]);
    }
    public function store(PengumpulanDrafRequest $r, Kegiatan $kegiatan, KelolaPengumpulan $aksi): RedirectResponse
    {
        $p = $aksi->buat($r->user()->id, $kegiatan, $r->validated());
        return redirect()->route('pengumpulan.show', $p)->with('info', 'Versi jawaban tersedia. Draf belum dianggap terkumpul.');
    }
    public function show(Request $r, Pengumpulan $pengumpulan, AksesPengumpulan $akses): View
    {
        Gate::authorize('view', $pengumpulan);
        $r->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $pengumpulan->load(['pemilik', 'kegiatan.kelasKuliah', 'lampiran.berkas']);
        return view('pengumpulan.show', [
            'pengumpulan' => $pengumpulan,
            'berlaku' => Pengumpulan::query()->berlaku()->whereKey($pengumpulan->id)->exists(),
            'pemilik' => $akses->pemilik($r->user(), $pengumpulan),
            'bolehTulis' => $akses->pemilik($r->user(), $pengumpulan) && $pengumpulan->status === Pengumpulan::DRAF
                && $akses->bolehTulis($r->user(), $pengumpulan->kegiatan),
            'audit' => $pengumpulan->audits()->with('pelaku')->orderByDesc('versi_entitas')->paginate(10),
            'zona' => WaktuKegiatan::zona()
        ]);
    }
    public function edit(Pengumpulan $pengumpulan): View
    {
        Gate::authorize('update', $pengumpulan);
        $pengumpulan->load(['kegiatan.kelasKuliah', 'lampiran']);
        return view('pengumpulan.edit', ['pengumpulan' => $pengumpulan, 'zona' => WaktuKegiatan::zona()]);
    }
    public function update(PengumpulanRequest $r, Pengumpulan $pengumpulan, KelolaPengumpulan $aksi): RedirectResponse
    {
        $p = $aksi->ubah($r->user()->id, $pengumpulan, $r->validated());
        return redirect()->route('pengumpulan.show', $p)->with('info', 'Draf disimpan. Periksa jawaban, lalu tekan Kirim jawaban final.');
    }
    public function kirim(PengumpulanKirimRequest $r, Pengumpulan $pengumpulan, KelolaPengumpulan $aksi): RedirectResponse
    {
        $p = $aksi->kirim($r->user()->id, $pengumpulan, $r->validated());
        return redirect()->route('pengumpulan.show', $p)->with('info', 'Jawaban versi ' . $p->versi . ' sudah tercatat sebagai kiriman final.');
    }
    public function tautan(Request $r, Pengumpulan $pengumpulan, PengumpulanBerkas $lampiran): RedirectResponse
    {
        $b = $this->lampiran($pengumpulan, $lampiran);
        return redirect()->to(URL::temporarySignedRoute('pengumpulan.unduh', now()->addMinutes(max(1, min(5, (int) config('pengumpulan.masa_tautan_menit', 2)))), [
            'pengumpulan' => $pengumpulan->id,
            'lampiran' => $lampiran->id,
            'pemohon' => $r->user()->id,
            'versi_pengumpulan' => $pengumpulan->revisi,
            'versi_berkas' => $b->revisi,
        ]));
    }
    public function unduh(Request $r, Pengumpulan $pengumpulan, PengumpulanBerkas $lampiran, PenyimpananBerkas $storage): StreamedResponse
    {
        $b = $this->lampiran($pengumpulan, $lampiran);
        $this->tokenUnduh($r, $pengumpulan, $b);
        try {
            $stream = $storage->buka($b);
        } catch (\Throwable $e) {
            Log::warning('Unduh jawaban gagal.', ['pengumpulan_id' => $pengumpulan->id, 'berkas_id' => $b->id, 'jenis' => $e::class]);
            abort(503, 'Berkas belum dapat diunduh. Coba lagi atau hubungi pengelola.');
        }
        // Tidak menahan transaksi ketika membaca cloud. Otorisasi diperiksa lagi sebelum respons dikirim.
        try {
            $pengumpulan->refresh();
            $lampiran->refresh();
            $b = $this->lampiran($pengumpulan, $lampiran);
            $this->tokenUnduh($r, $pengumpulan, $b);
        } catch (\Throwable $e) {
            fclose($stream);
            throw $e;
        }
        $nama = Str::slug(mb_substr(pathinfo($lampiran->nama_asli, PATHINFO_FILENAME), 0, 100)) ?: 'jawaban-' . $lampiran->id;
        return response()->streamDownload(function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, $nama . '.' . $lampiran->ekstensi, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
            'Content-Length' => (string) $lampiran->ukuran_byte
        ]);
    }
    private function lampiran(Pengumpulan $p, PengumpulanBerkas $l): Berkas
    {
        Gate::authorize('view', $p);
        abort_unless($l->pengumpulan_id === $p->id && $l->aktif, 404);
        $b = Berkas::query()->findOrFail($l->berkas_id);
        abort_unless($b->status === Berkas::TERSEDIA && $b->diunggah_oleh === $p->pemilik_id && $l->cocok($b), 404);
        return $b;
    }
    private function tokenUnduh(Request $r, Pengumpulan $p, Berkas $b): void
    {
        abort_unless(
            $r->query('pemohon') === (string) $r->user()->id
                && $r->query('versi_pengumpulan') === (string) $p->revisi && $r->query('versi_berkas') === (string) $b->revisi,
            403,
            'Tautan berubah. Klik Unduh kembali.'
        );
    }
}
