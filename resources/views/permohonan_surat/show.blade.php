@extends('layouts.admin')

@section('title', $p->nomor_pengajuan)

@section('content')
    @php
        $zona = (string) config('siakad.timezone', 'Asia/Makassar');
        $labelZona = $zona === 'Asia/Makassar' ? 'WITA' : $zona;
        $tgl = fn($waktu) => $waktu ? $waktu->setTimezone($zona)->format('d-m-Y H:i') . ' ' . $labelZona : '—';
        $akademik = $p->akademik_snapshot ?? [];
        $jenisSnap = $p->jenis_snapshot ?? [];
        $milikSendiri = (int) $p->pemohon_id === (int) auth()->id();

        // Hak unduh dihitung sekali (policy menjalankan beberapa query).
        $bisaLampiran = \Illuminate\Support\Facades\Gate::allows('download', [$p, 'lampiran']);
        $bisaHasil = \Illuminate\Support\Facades\Gate::allows('download', [$p, 'hasil']);

        // Mahasiswa pemilik hanya dapat membatalkan; petugas dapat memilih beberapa tindakan.
        $hanyaBatal = !empty($pilihan) && count($pilihan) === 1 && in_array('dibatalkan', $pilihan, true);

        $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
        $kepalaKartu = 'border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
        $judulKartu = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
        $labelKecil = 'text-[10px] font-bold uppercase tracking-widest text-slate-400';
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1.5 block text-sm font-semibold text-slate-700';

        $infoStatus = match ($p->status) {
            'diajukan' => 'Permohonan Anda menunggu diproses petugas akademik.',
            'diproses' => 'Permohonan Anda sedang diproses petugas akademik.',
            'terbit' => 'Surat sudah terbit. Anda dapat mengunduhnya di bawah ini.',
            'ditolak' => 'Permohonan ditolak. Lihat catatan petugas pada riwayat proses.',
            'dibatalkan' => 'Permohonan ini sudah dibatalkan.',
            default => null,
        };
    @endphp

    @include('permohonan_surat._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="text-xs text-slate-500">Layanan &rsaquo; Permohonan surat &rsaquo; Detail</p>
            <h1 class="mt-1 break-all font-mono text-2xl font-bold text-slate-800">{{ $p->nomor_pengajuan }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $jenisSnap['nama'] ?? 'Permohonan surat' }}</p>
        </div>
        <a href="{{ route('surat.index') }}"
            class="inline-flex shrink-0 items-center justify-center self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">&larr;
            Kembali ke daftar</a>
    </div>

    @if ($p->status === 'dibatalkan' && $p->terbit_at)
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
            Surat ini telah dibatalkan. Jangan gunakan salinan yang pernah diunduh. Hubungi bagian akademik untuk
            penggantian.
        </div>
    @endif

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        {{-- Kolom utama --}}
        <div class="space-y-6 lg:col-span-2">

            <section class="{{ $kartu }}">
                <div class="{{ $kepalaKartu }}">
                    <h2 class="{{ $judulKartu }}">Ringkasan permohonan</h2>
                </div>
                <dl class="grid grid-cols-1 gap-x-8 gap-y-5 p-6 sm:grid-cols-2">
                    <div>
                        <dt class="{{ $labelKecil }}">Mahasiswa</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $akademik['nim'] ?? '—' }} &mdash;
                            {{ $akademik['nama'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelKecil }}">Periode / semester studi</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            #{{ $akademik['periode_akademik_id'] ?? '—' }} /
                            {{ $akademik['semester_studi'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $labelKecil }}">Diajukan</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $tgl($p->diajukan_at) }}</dd>
                    </div>
                    @if ($p->nomor_surat)
                        <div>
                            <dt class="{{ $labelKecil }}">Nomor surat</dt>
                            <dd class="mt-1 text-sm font-semibold text-emerald-700">{{ $p->nomor_surat }}</dd>
                        </div>
                    @endif
                    <div class="sm:col-span-2">
                        <dt class="{{ $labelKecil }}">Keperluan</dt>
                        <dd class="mt-1 whitespace-pre-line break-words text-sm leading-relaxed text-slate-700">
                            {{ $p->keperluan }}</dd>
                    </div>
                </dl>
            </section>

            <section class="{{ $kartu }}">
                <div class="{{ $kepalaKartu }}">
                    <h2 class="{{ $judulKartu }}">Persyaratan saat diajukan</h2>
                </div>
                <div class="whitespace-pre-line break-words p-6 text-sm leading-relaxed text-slate-700">
                    {{ $jenisSnap['syarat'] ?? 'Tidak ada persyaratan tambahan.' }}</div>
            </section>

            <section class="{{ $kartu }}">
                <div class="{{ $kepalaKartu }}">
                    <h2 class="{{ $judulKartu }}">Dokumen</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Lampiran pendukung</p>
                            <p class="text-xs text-slate-500">
                                {{ $bisaLampiran ? 'Dilampirkan saat pengajuan.' : 'Tidak ada lampiran.' }}</p>
                        </div>
                        @if ($bisaLampiran)
                            <a href="{{ route('surat.tautan', [$p, 'lampiran']) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">Unduh
                                lampiran</a>
                        @endif
                    </div>
                    <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Surat final</p>
                            <p class="text-xs text-slate-500">
                                @if ($p->nomor_surat)
                                    Diterbitkan {{ $tgl($p->terbit_at) }}.
                                @else
                                    Belum diterbitkan.
                                @endif
                            </p>
                        </div>
                        @if ($bisaHasil)
                            <a href="{{ route('surat.tautan', [$p, 'hasil']) }}"
                                class="inline-flex items-center justify-center rounded-lg bg-siakad-dark px-4 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">Unduh
                                surat final</a>
                        @endif
                    </div>
                </div>
            </section>

            @if ($pilihan)
                @if ($berkas)
                    <form method="get" action="{{ route('surat.show', $p) }}" class="{{ $kartu }} p-6">
                        <h2 class="{{ $judulKartu }} mb-1">Siapkan PDF final</h2>
                        <p class="mb-4 text-xs text-slate-500">Siapkan PDF yang sudah diperiksa dan ditandatangani sesuai
                            prosedur kampus. Pilih berkas sebelum mengisi keputusan.</p>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="flex-1">
                                <label for="q_berkas" class="{{ $label }}">Cari PDF final menurut label</label>
                                <input id="q_berkas" name="q_berkas" maxlength="100"
                                    value="{{ $filter['q_berkas'] ?? '' }}" class="{{ $input }}">
                            </div>
                            <button type="submit"
                                class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-700">Cari</button>
                            <a href="{{ route('berkas.create') }}" target="_blank" rel="noopener noreferrer"
                                class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">Unggah
                                PDF final</a>
                        </div>
                    </form>
                @endif

                <form method="post" action="{{ route('surat.tindakan', $p) }}" class="{{ $kartu }} p-6"
                    @if ($hanyaBatal) onsubmit="return confirm('Batalkan pengajuan surat ini? Tindakan tidak dapat diurungkan.')" @endif>
                    @csrf
                    <input type="hidden" name="versi" value="{{ old('versi', $p->versiForm()) }}">

                    <h2 class="{{ $judulKartu }} mb-5">{{ $hanyaBatal ? 'Batalkan pengajuan' : 'Tindakan petugas' }}
                    </h2>

                    <div class="space-y-5">
                        <div>
                            <label for="tujuan" class="{{ $label }}">Tindakan <span
                                    class="text-rose-500">*</span></label>
                            <select id="tujuan" name="tujuan" required class="{{ $input }}">
                                <option value="">Pilih tindakan</option>
                                @foreach ($pilihan as $tujuan)
                                    <option value="{{ $tujuan }}" @selected(old('tujuan', $hanyaBatal ? $tujuan : null) === $tujuan)>
                                        {{ $tujuan === 'terbit' ? 'Terbitkan surat' : \App\Models\PermohonanSurat::STATUS[$tujuan] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tujuan')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($berkas)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-5">
                                <h3 class="mb-3 text-sm font-bold text-slate-800">Khusus penerbitan</h3>
                                <label for="nomor_surat" class="{{ $label }}">Nomor surat resmi</label>
                                <input id="nomor_surat" name="nomor_surat" maxlength="100" value="{{ old('nomor_surat') }}"
                                    autocomplete="off" class="{{ $input }}">
                                <p class="mb-4 mt-1 text-xs text-slate-500">Gunakan nomor resmi dari pencatatan kampus.
                                    Huruf,
                                    angka, titik, garis miring, tanda minus, dan garis bawah; tanpa spasi. Nomor tidak
                                    dibuat
                                    otomatis.</p>
                                @include('permohonan_surat._berkas', ['field' => 'hasil_berkas_id'])
                            </div>
                        @endif

                        <div>
                            <label for="catatan" class="{{ $label }}">Catatan / alasan <span
                                    class="text-rose-500">*</span></label>
                            <textarea id="catatan" name="catatan" minlength="{{ $p->status === 'terbit' ? 20 : 10 }}" maxlength="1000"
                                rows="4" required class="{{ $input }}">{{ old('catatan') }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">Catatan terlihat oleh mahasiswa. Jangan menuliskan
                                informasi internal yang tidak boleh dibagikan.</p>
                            @error('catatan')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            <input type="checkbox" name="konfirmasi" value="1" required
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark">
                            <span>Saya sudah memeriksa tindakan dan dokumen yang dipilih.</span>
                        </label>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <button type="submit"
                            class="rounded-xl px-6 py-2.5 text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $hanyaBatal ? 'border border-rose-200 bg-white text-rose-700 hover:bg-rose-50 focus:ring-rose-300' : 'bg-siakad-dark text-white hover:bg-emerald-800 focus:ring-siakad-dark' }}">
                            {{ $hanyaBatal ? 'Batalkan pengajuan' : 'Simpan tindakan' }}
                        </button>
                    </div>
                </form>
            @endif

            <section class="{{ $kartu }}">
                <div class="{{ $kepalaKartu }}">
                    <h2 class="{{ $judulKartu }}">Riwayat proses</h2>
                </div>
                <ol class="divide-y divide-slate-100">
                    @forelse ($p->riwayat as $r)
                        <li class="px-6 py-4">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                @include('permohonan_surat._lencana', ['status' => $r->status_baru])
                                <span class="font-mono text-xs text-slate-400">{{ $tgl($r->waktu) }}</span>
                                <span class="text-xs text-slate-600">{{ $r->pelaku?->nama ?? 'Petugas' }}</span>
                            </div>
                            @if (filled($r->catatan))
                                <p class="mt-2 whitespace-pre-line break-words text-sm text-slate-700">{{ $r->catatan }}
                                </p>
                            @endif
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm italic text-slate-500">Belum ada riwayat proses.</li>
                    @endforelse
                </ol>
            </section>
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-6">
            <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-200">Status permohonan</p>
                <div class="mt-2">
                    @include('permohonan_surat._lencana', ['status' => $p->status, 'gelap' => true])
                </div>
                @if ($milikSendiri && $infoStatus)
                    <p class="mt-4 text-sm text-emerald-50">{{ $infoStatus }}</p>
                @endif
                @if ($bisaHasil)
                    <a href="{{ route('surat.tautan', [$p, 'hasil']) }}"
                        class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-siakad-dark shadow-sm transition-colors hover:bg-emerald-50">Unduh
                        surat final</a>
                @endif
            </section>

            <section class="{{ $kartu }} p-6 text-xs text-slate-600">
                <h2 class="mb-2 text-sm font-bold text-slate-800">Catatan</h2>
                <ul class="list-inside list-disc space-y-1.5">
                    <li>Waktu ditampilkan dalam {{ $labelZona }}.</li>
                    <li>Persyaratan di atas adalah yang berlaku saat permohonan diajukan.</li>
                    @if ($milikSendiri)
                        <li>Catatan petugas akan tampil di riwayat proses.</li>
                    @endif
                </ul>
            </section>
        </aside>
    </div>
@endsection
