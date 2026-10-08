@extends('layouts.admin')

@section('title', $item->judul)

@section('content')
    @php
        $zona = \App\Models\Pengumuman::ZONA;
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $kedaluwarsa = $item->status === 'terbit' && $item->kedaluwarsa();
    @endphp

    @include('pengumuman._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('pengumuman.index', ['mode' => $kelola ? 'kelola' : 'bacaan']) }}"
                    class="hover:text-siakad-active">Pengumuman</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Detail</span>
            </nav>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                @include('pengumuman._lencana', ['status' => $item->status])
                @if ($kedaluwarsa)
                    <span
                        class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Masa
                        tayang berakhir</span>
                @endif
            </div>
            <h1 class="break-words text-2xl font-bold text-slate-800">{{ $item->judul }}</h1>
            <p class="mt-1 text-sm text-slate-500">Oleh {{ $item->pembuat?->nama ?? 'Petugas' }}</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('pengumuman.index', ['mode' => $kelola ? 'kelola' : 'bacaan']) }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
                Kembali ke daftar</a>
            @can('update', $item)
                <a href="{{ route('pengumuman.edit', $item) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Edit
                    draf</a>
            @endcan
        </div>
    </div>

    @if ($kedaluwarsa)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            Masa tayang sudah berakhir. Hanya pengelola yang dapat membuka halaman ini.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Isi --}}
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Isi pengumuman</h2>
                </div>
                <div class="whitespace-pre-line break-words p-6 text-sm leading-relaxed text-slate-700">{{ $item->isi }}
                </div>
            </article>

            @if ($kelola)
                {{-- Sasaran --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Sasaran pembaca</h2>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($item->sasaran as $s)
                            <li class="flex flex-wrap items-center gap-2 px-6 py-3 text-sm">
                                <span
                                    class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ ucfirst($s->lingkup) }}</span>
                                @if ($s->lingkup === 'prodi')
                                    <span class="font-semibold text-slate-800">{{ $s->programStudi?->nama }}</span>
                                @endif
                                @if ($s->lingkup === 'kelas')
                                    <span class="font-semibold text-slate-800">{{ $s->kelasKuliah?->kode }}</span>
                                @endif
                                <span class="text-slate-500">&middot;
                                    {{ $s->role?->kode ?? 'Semua peran sesuai lingkup' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="border-t border-slate-100 bg-slate-50 px-6 py-3 text-xs text-slate-500">Keanggotaan
                        diperiksa saat halaman dibuka. Dosen harus masih mengampu; mahasiswa kelas harus memiliki KRS
                        disahkan, detail KRS aktif, registrasi dan riwayat studi aktif.</p>
                </section>

                {{-- Tindakan --}}
                @foreach (['terbit' => 'Terbitkan', 'arsip' => 'Arsipkan'] as $aksi => $label)
                    @can($aksi, $item)
                        @php $bahaya = $aksi === 'arsip'; @endphp
                        <form method="post" action="{{ route('pengumuman.tindakan', $item) }}"
                            class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $bahaya ? 'border-amber-200' : 'border-emerald-200' }}">
                            @csrf
                            <div
                                class="border-b px-6 py-4 {{ $bahaya ? 'border-amber-100 bg-amber-50' : 'border-emerald-100 bg-emerald-50' }}">
                                <h2
                                    class="text-sm font-bold uppercase tracking-wider {{ $bahaya ? 'text-amber-800' : 'text-siakad-dark' }}">
                                    {{ $label }} pengumuman</h2>
                            </div>
                            <div class="space-y-4 p-6">
                                <input type="hidden" name="aksi" value="{{ $aksi }}">
                                <input type="hidden" name="versi" value="{{ $item->versiForm() }}">
                                <div>
                                    <label for="alasan-{{ $aksi }}"
                                        class="mb-1.5 block text-sm font-semibold text-slate-700">Alasan (untuk audit
                                        pengelola) <span class="text-rose-500">*</span></label>
                                    <textarea id="alasan-{{ $aksi }}" name="alasan" required minlength="10" maxlength="1000" rows="3"
                                        class="{{ $input }}" placeholder="Tulis alasan, minimal 10 karakter"></textarea>
                                </div>
                                <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                                    <input type="checkbox" name="konfirmasi" value="1" required
                                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-active focus:ring-siakad-active">
                                    <span>Saya sudah memeriksa isi dan sasaran.
                                        {{ $aksi === 'terbit' ? 'Isi dan sasaran terkunci setelah terbit.' : 'Arsip tidak dapat diterbitkan ulang.' }}</span>
                                </label>
                                <button type="submit"
                                    class="rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition {{ $bahaya ? 'bg-amber-600 hover:bg-amber-700' : 'bg-siakad-dark hover:bg-emerald-800' }}">{{ $label }}</button>
                            </div>
                        </form>
                    @endcan
                @endforeach

                {{-- Audit --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Audit pengelola</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($audit as $log)
                            <details class="group px-6 py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm">
                                    <span class="min-w-0">
                                        <span class="font-semibold text-slate-800">Revisi
                                            {{ $log->versi_entitas }}</span>
                                        <span class="text-slate-500">&middot; {{ $log->aksi }} &middot;
                                            {{ $log->pelaku?->nama }} &middot;
                                            {{ \Carbon\CarbonImmutable::parse($log->waktu)->setTimezone($zona)->format('d-m-Y H:i:s') }}</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <p class="mt-3 text-xs text-slate-500">{{ $log->alasan }}</p>
                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div>
                                        <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">
                                            Sebelum</h3>
                                        <pre class="max-h-64 overflow-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode($log->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    <div>
                                        <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">
                                            Sesudah</h3>
                                        <pre class="max-h-64 overflow-auto rounded-lg bg-emerald-50 p-3 text-xs text-slate-700">{{ json_encode($log->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                </div>
                            </details>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-slate-500">Belum ada catatan audit.</p>
                        @endforelse
                    </div>
                    {{ $audit->links('pengumuman._pagination') }}
                </section>
            @endif
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Masa tayang</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-xs text-emerald-200">Terbit</p>
                        @if ($item->terbit_at)
                            <p class="text-lg font-bold">
                                {{ $item->terbit_at->setTimezone($zona)->locale('id')->translatedFormat('d F Y') }}</p>
                            <p class="text-sm text-emerald-100">{{ $item->terbit_at->setTimezone($zona)->format('H:i') }}
                                WITA</p>
                        @else
                            <p class="text-lg font-bold">Belum terbit</p>
                        @endif
                    </div>
                    <div class="border-t border-white/10 pt-4">
                        <p class="text-xs text-emerald-200">Batas tayang</p>
                        @if ($item->berakhir_at)
                            <p class="text-lg font-bold">
                                {{ $item->berakhir_at->setTimezone($zona)->locale('id')->translatedFormat('d F Y') }}
                            </p>
                            <p class="text-sm text-emerald-100">
                                {{ $item->berakhir_at->setTimezone($zona)->format('H:i') }} WITA</p>
                        @else
                            <p class="text-lg font-bold">Sampai diarsipkan</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Informasi</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Penulis</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $item->pembuat?->nama ?? 'Petugas' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Status</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ \App\Models\Pengumuman::STATUS[$item->status] }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
@endsection
