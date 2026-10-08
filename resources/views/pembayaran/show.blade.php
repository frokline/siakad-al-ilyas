@extends('layouts.admin')

@section('title', 'Detail Pengajuan Pembayaran')

@section('content')
    @php
        $zona = 'Asia/Makassar';
        $snap = $pembayaran->tagihan_snapshot;
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $cek = 'mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-active focus:ring-siakad-active';
    @endphp

    @include('pembayaran._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('pembayaran.index') }}" class="hover:text-siakad-active">Riwayat pembayaran</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Detail</span>
            </nav>
            <div class="mb-2">@include('pembayaran._lencana', ['status' => $pembayaran->status])</div>
            <h1 class="break-words text-2xl font-bold text-slate-800">{{ $pembayaran->nomor_pengajuan }}</h1>
            <p class="mt-1 text-sm text-slate-500">Tagihan {{ $snap['nomor'] ?? '—' }}</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('pembayaran.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
                Riwayat pembayaran</a>
            <a href="{{ route('tagihan.show', $pembayaran->tagihan_id) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Lihat
                tagihan</a>
        </div>
    </div>

    {{-- Pesan status --}}
    @if ($pembayaran->status === \App\Models\Pembayaran::MENUNGGU)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            Pembayaran masih menunggu pemeriksaan Admin Keuangan.
        </div>
    @elseif ($pembayaran->status === \App\Models\Pembayaran::DITERIMA)
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
            Bukti pembayaran telah diterima oleh Admin Keuangan.
        </div>
    @elseif ($pembayaran->status === \App\Models\Pembayaran::DITOLAK)
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
            Bukti pembayaran ditolak. Mahasiswa dapat mengajukan bukti baru.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Rincian --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Detail pembayaran</h2>
                </div>
                <dl class="grid gap-x-6 gap-y-5 p-6 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500">Tagihan</dt>
                        <dd class="mt-0.5 font-semibold">
                            <a href="{{ route('tagihan.show', $pembayaran->tagihan_id) }}"
                                class="text-siakad-active underline">{{ $snap['nomor'] ?? '—' }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Mahasiswa</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $snap['snapshot']['nama'] ?? '—' }}</dd>
                        <dd class="text-xs text-slate-500">{{ $snap['snapshot']['nim'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Tanggal transfer</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ $pembayaran->tanggal_transfer->format('d-m-Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Referensi bank</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $pembayaran->referensi_bank ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-500">Tujuan saat pengajuan</dt>
                        <dd class="mt-0.5 break-words font-semibold text-slate-800">
                            {{ $pembayaran->tujuan_transfer }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Diajukan oleh</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $pembayaran->pengunggah->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Waktu pengajuan</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ $pembayaran->diajukan_at->setTimezone($zona)->format('d-m-Y H:i') }} WITA</dd>
                    </div>
                    @if ($pembayaran->alasan_batal)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-slate-500">Alasan pembatalan</dt>
                            <dd class="mt-0.5 whitespace-pre-line break-words text-slate-700">
                                {{ $pembayaran->alasan_batal }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Bukti --}}
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-siakad-active">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs text-slate-500">Bukti pembayaran</p>
                            <p class="break-all text-sm font-semibold text-slate-800">
                                {{ $pembayaran->bukti_snapshot['nama_asli'] ?? 'Bukti pembayaran' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('pembayaran.tautan', $pembayaran) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Unduh
                        bukti privat</a>
                </div>
            </section>

            {{-- Verifikasi Admin Keuangan --}}
            @if ($bolehVerifikasi)
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Verifikasi Admin
                            Keuangan</h2>
                        <p class="mt-1 text-xs text-slate-500">Periksa nominal, tanggal transfer, tujuan rekening,
                            serta berkas bukti sebelum mengambil keputusan.</p>
                    </div>
                    <div class="grid gap-0 divide-y divide-slate-100 md:grid-cols-2 md:divide-x md:divide-y-0">
                        {{-- Terima --}}
                        <form method="post" action="{{ route('pembayaran.terima', $pembayaran) }}" class="space-y-4 p-6">
                            @csrf
                            <input type="hidden" name="versi" value="{{ $pembayaran->versiForm() }}">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Terima pembayaran
                            </h3>
                            <div>
                                <label for="catatan-terima"
                                    class="mb-1.5 block text-sm font-semibold text-slate-700">Catatan penerimaan <span
                                        class="text-rose-500">*</span></label>
                                <textarea id="catatan-terima" name="catatan" minlength="10" maxlength="2000" rows="4" required
                                    class="{{ $input }}" placeholder="Minimal 10 karakter">{{ old('catatan') }}</textarea>
                            </div>
                            <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="{{ $cek }}">
                                Saya sudah memeriksa bukti dan menyatakan pembayaran ini valid.
                            </label>
                            <button type="submit"
                                class="rounded-xl bg-siakad-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Terima
                                pembayaran</button>
                        </form>

                        {{-- Tolak --}}
                        <form method="post" action="{{ route('pembayaran.tolak', $pembayaran) }}" class="space-y-4 p-6">
                            @csrf
                            <input type="hidden" name="versi" value="{{ $pembayaran->versiForm() }}">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-rose-700">Tolak pembayaran</h3>
                            <div>
                                <label for="catatan-tolak" class="mb-1.5 block text-sm font-semibold text-slate-700">Alasan
                                    penolakan <span class="text-rose-500">*</span></label>
                                <textarea id="catatan-tolak" name="catatan" minlength="10" maxlength="2000" rows="4" required
                                    class="{{ $input }}" placeholder="Minimal 10 karakter">{{ old('catatan') }}</textarea>
                            </div>
                            <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="{{ $cek }}">
                                Saya memahami bahwa penolakan membuka kesempatan pengajuan baru.
                            </label>
                            <button type="submit"
                                class="rounded-xl bg-rose-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">Tolak
                                pembayaran</button>
                        </form>
                    </div>
                </section>
            @endif

            {{-- Batalkan pengajuan --}}
            @can('cancel', $pembayaran)
                <form method="post" action="{{ route('pembayaran.batalkan', $pembayaran) }}"
                    class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                    @csrf
                    <input type="hidden" name="versi" value="{{ $pembayaran->versiForm() }}">
                    <div class="border-b border-amber-100 bg-amber-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-amber-800">Batalkan pengajuan</h2>
                        <p class="mt-1 text-xs text-amber-900">Histori dan bukti tetap disimpan. Tindakan ini tidak
                            mengembalikan uang di rekening bank.</p>
                    </div>
                    <div class="space-y-4 p-6">
                        <div>
                            <label for="alasan-pembatalan" class="mb-1.5 block text-sm font-semibold text-slate-700">Alasan
                                pembatalan <span class="text-rose-500">*</span></label>
                            <textarea id="alasan-pembatalan" name="alasan" minlength="10" maxlength="1000" rows="3" required
                                class="{{ $input }}" placeholder="Minimal 10 karakter">{{ old('alasan') }}</textarea>
                        </div>
                        <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                            <input type="checkbox" name="konfirmasi" value="1" required class="{{ $cek }}">
                            Saya memahami dampak pembatalan pengajuan.
                        </label>
                        <button type="submit"
                            class="rounded-xl bg-amber-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">Batalkan
                            pengajuan</button>
                    </div>
                </form>
            @endcan

            {{-- Riwayat dan audit (petugas) --}}
            @can('audit', $pembayaran)
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Riwayat verifikasi</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($riwayatVerifikasi as $verifikasi)
                            <details class="group px-6 py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm">
                                    <span class="min-w-0">
                                        <span class="font-semibold text-slate-800">Revisi
                                            {{ $verifikasi->revisi_pembayaran }}</span>
                                        <span class="text-slate-500">&middot;
                                            {{ \App\Models\VerifikasiPembayaran::TINDAKAN[$verifikasi->tindakan] ?? $verifikasi->tindakan }}
                                            &middot; {{ $verifikasi->petugas?->nama ?? '—' }}</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $verifikasi->catatan }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $verifikasi->waktu->setTimezone($zona)->format('d-m-Y H:i') }} WITA</p>
                            </details>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-slate-500">Belum ada riwayat verifikasi.</p>
                        @endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Audit internal</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($audit as $item)
                            <details class="group px-6 py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm">
                                    <span class="min-w-0">
                                        <span class="font-semibold text-slate-800">Revisi
                                            {{ $item->versi_entitas }}</span>
                                        <span class="text-slate-500">&middot; {{ $item->aksi }} &middot;
                                            {{ $item->pelaku?->nama ?? '—' }}</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <p class="mt-3 text-xs text-slate-500">{{ $item->alasan }}</p>
                                <pre class="mt-3 max-h-72 overflow-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode(['sebelum' => $item->sebelum, 'sesudah' => $item->sesudah], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-slate-500">Belum ada riwayat audit.</p>
                        @endforelse
                    </div>
                    {{ $audit->links('pembayaran._pagination') }}
                </section>
            @endcan
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Nominal diajukan</h2>
                <p class="mt-3 break-words text-3xl font-bold">
                    {{ \App\Services\UangTagihan::rupiah($pembayaran->nominal_diajukan) }}</p>
                <div class="mt-5 border-t border-white/10 pt-4">
                    <p class="text-xs text-emerald-200">Tanggal transfer</p>
                    <p class="text-lg font-bold">
                        {{ $pembayaran->tanggal_transfer->locale('id')->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Informasi</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Status</dt>
                        <dd class="mt-1">@include('pembayaran._lencana', ['status' => $pembayaran->status])</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Revisi</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $pembayaran->revisi }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
@endsection
