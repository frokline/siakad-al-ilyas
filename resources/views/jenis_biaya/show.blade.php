@extends('layouts.admin')

@section('title', $jenisBiaya->nama)

@section('content')
    @php
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $aksiDaftar = [
            'nonaktifkan' => [
                'Nonaktifkan jenis biaya',
                'rose',
                'Jenis biaya akan berhenti tersedia untuk tagihan baru. Tagihan lama tetap disimpan.',
            ],
            'aktifkan' => [
                'Aktifkan kembali',
                'emerald',
                'Kode lama digunakan kembali; tidak membuat jenis biaya baru.',
            ],
        ];
    @endphp

    @include('jenis_biaya._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('keuangan.jenis-biaya.index') }}" class="hover:text-siakad-active">Jenis biaya</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Detail</span>
            </nav>
            <div class="mb-2">@include('jenis_biaya._lencana', ['aktif' => $jenisBiaya->aktif])</div>
            <h1 class="break-words text-2xl font-bold text-slate-800">{{ $jenisBiaya->nama }}</h1>
            <p class="mt-1 font-mono text-sm text-slate-500">{{ $jenisBiaya->kode }}</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('keuangan.jenis-biaya.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
                Daftar jenis biaya</a>
            @can('update', $jenisBiaya)
                <a href="{{ route('keuangan.jenis-biaya.edit', $jenisBiaya) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Edit
                    nama / keterangan</a>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Keterangan --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Keterangan</h2>
                </div>
                <div class="space-y-4 p-6">
                    <div class="whitespace-pre-line break-words text-sm leading-relaxed text-slate-700">
                        {{ $jenisBiaya->keterangan ?? 'Tidak ada keterangan tambahan.' }}</div>
                    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
                        Keterangan adalah informasi katalog. Nominal diatur pada modul Tagihan, bukan di sini.
                    </div>
                </div>
            </section>

            {{-- Tindakan status --}}
            @foreach ($aksiDaftar as $aksi => [$judulAksi, $warna, $penjelasan])
                @can($aksi, $jenisBiaya)
                    @php $bahaya = $warna === 'rose'; @endphp
                    <form method="post" action="{{ route('keuangan.jenis-biaya.' . $aksi, $jenisBiaya) }}"
                        class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $bahaya ? 'border-rose-200' : 'border-emerald-200' }}">
                        @csrf
                        <input type="hidden" name="versi" value="{{ old('versi', $jenisBiaya->versiForm()) }}">
                        <div
                            class="border-b px-6 py-4 {{ $bahaya ? 'border-rose-100 bg-rose-50' : 'border-emerald-100 bg-emerald-50' }}">
                            <h2
                                class="text-sm font-bold uppercase tracking-wider {{ $bahaya ? 'text-rose-700' : 'text-siakad-dark' }}">
                                {{ $judulAksi }}</h2>
                            <p class="mt-1 text-xs {{ $bahaya ? 'text-rose-800' : 'text-emerald-900' }}">
                                {{ $penjelasan }}</p>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <label for="alasan-{{ $aksi }}"
                                    class="mb-1.5 block text-sm font-semibold text-slate-700">Alasan <span
                                        class="text-rose-500">*</span></label>
                                <textarea id="alasan-{{ $aksi }}" name="alasan" rows="3" minlength="10" maxlength="1000" required
                                    class="{{ $input }}" placeholder="Minimal 10 karakter">{{ old('alasan') }}</textarea>
                            </div>
                            <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-active focus:ring-siakad-active">
                                Saya memahami perubahan status ini.
                            </label>
                            <button type="submit"
                                class="rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition {{ $bahaya ? 'bg-rose-600 hover:bg-rose-700' : 'bg-siakad-dark hover:bg-emerald-800' }}">{{ $judulAksi }}</button>
                        </div>
                    </form>
                @endcan
            @endforeach

            {{-- Riwayat --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Riwayat perubahan</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($audit as $a)
                        <details class="group px-6 py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm">
                                <span class="min-w-0">
                                    <span class="font-semibold text-slate-800">Revisi {{ $a->versi_entitas }}</span>
                                    <span class="text-slate-500">&middot; {{ $a->aksi }} &middot;
                                        {{ $a->pelaku?->nama ?? 'Sistem' }}</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <p class="mt-3 text-xs text-slate-500">
                                {{ $a->waktu->setTimezone($zona)->format('d-m-Y H:i:s') }} &middot;
                                {{ $a->alasan ?? 'Pembuatan awal' }}</p>
                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                <div>
                                    <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Sebelum
                                    </h3>
                                    <pre class="max-h-64 overflow-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode($a->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                                <div>
                                    <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Sesudah
                                    </h3>
                                    <pre class="max-h-64 overflow-auto rounded-lg bg-emerald-50 p-3 text-xs text-slate-700">{{ json_encode($a->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </div>
                            </div>
                        </details>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-slate-500">Belum ada catatan perubahan.</p>
                    @endforelse
                </div>
                {{ $audit->links('jenis_biaya._pagination') }}
            </section>
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Status layanan</h2>
                <p class="mt-3 text-2xl font-bold">{{ $jenisBiaya->aktif ? 'Aktif' : 'Nonaktif' }}</p>
                <p class="mt-1 text-sm text-emerald-100">
                    {{ $jenisBiaya->aktif ? 'Tersedia untuk tagihan baru.' : 'Tidak tersedia untuk tagihan baru.' }}
                </p>
                @if (!$jenisBiaya->aktif && $jenisBiaya->dinonaktifkan_at)
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="text-xs text-emerald-200">Dinonaktifkan</p>
                        <p class="text-sm font-semibold">
                            {{ $jenisBiaya->dinonaktifkan_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Informasi jenis biaya</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">ID</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $jenisBiaya->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Revisi</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $jenisBiaya->revisi }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Pembuat</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $jenisBiaya->pembuat?->nama ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Dibuat</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ $jenisBiaya->created_at->setTimezone($zona)->format('d-m-Y H:i:s') }}
                            <span class="font-normal text-slate-500">({{ $zona }})</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Terakhir diperbarui</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ $jenisBiaya->updated_at?->setTimezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
@endsection
