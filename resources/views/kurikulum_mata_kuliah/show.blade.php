@extends('layouts.admin')

@section('title', 'Detail Mata Kuliah Kurikulum')

@section('content')
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.kurikulum.index') }}" class="transition-colors hover:text-siakad-dark">Kurikulum</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.kurikulum.show', $kurikulum) }}"
            class="transition-colors hover:text-siakad-dark">{{ $kurikulum->kode }}</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
            class="transition-colors hover:text-siakad-dark">Susunan Mata Kuliah</a>
        <span aria-hidden="true">/</span>
        <span class="font-bold text-slate-800">Detail</span>
    </nav>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Detail Mata Kuliah</p>
            <h1 class="text-xl font-bold text-slate-800">{{ $detail->mataKuliah->nama }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $detail->mataKuliah->kode }} · {{ $kurikulum->programStudi->nama }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($bisaUbah)
                <a href="{{ route('admin.kurikulum.mata-kuliah.edit', [$kurikulum, $detail]) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Rincian
                </a>
            @endif

            <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    @if (!$bisaUbah)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm"
            role="status">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/80 text-lg font-bold"
                aria-hidden="true">!</span>
            <div>
                <p class="font-semibold">Rincian kurikulum terkunci.</p>
                <p class="mt-0.5 text-amber-800">
                    Kurikulum sudah aktif atau diarsipkan. Rincian mata kuliah tidak dapat diubah.
                </p>
            </div>
        </div>
    @endif

    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Informasi Mata Kuliah</h2>
                <p class="mt-1 text-xs text-slate-500">Rincian penempatan mata kuliah pada kurikulum ini.</p>
            </div>

            <span
                class="inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-bold
                {{ $detail->mataKuliah->aktif
                    ? 'border-emerald-100 bg-emerald-50 text-emerald-800'
                    : 'border-rose-100 bg-rose-50 text-rose-700' }}">
                {{ $detail->mataKuliah->aktif ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        <dl class="grid grid-cols-1 gap-px bg-slate-200 sm:grid-cols-2 lg:grid-cols-3">
            <div class="bg-white p-5">
                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Mata Kuliah</dt>
                <dd class="mt-2 text-sm font-bold leading-6 text-slate-800">
                    <a href="{{ route('admin.mata-kuliah.show', $detail->mataKuliah) }}"
                        class="transition-colors hover:text-siakad-dark">
                        {{ $detail->mataKuliah->kode }} — {{ $detail->mataKuliah->nama }}
                    </a>
                </dd>
            </div>

            <div class="bg-white p-5">
                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Program Studi</dt>
                <dd class="mt-2 text-sm font-semibold leading-6 text-slate-800">
                    {{ $kurikulum->programStudi->nama }}
                </dd>
            </div>

            <div class="bg-white p-5">
                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Bobot SKS</dt>
                <dd class="mt-2 text-2xl font-bold text-slate-800">
                    {{ str_replace('.', ',', $detail->sks) }}
                    <span class="text-sm font-semibold text-slate-500">SKS</span>
                </dd>
            </div>

            <div class="bg-white p-5">
                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Semester Rekomendasi</dt>
                <dd class="mt-2">
                    <span
                        class="inline-flex min-w-9 items-center justify-center rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-bold text-slate-700">
                        {{ $detail->semester_rekomendasi }}
                    </span>
                </dd>
            </div>

            <div class="bg-white p-5">
                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Sifat Mata Kuliah</dt>
                <dd class="mt-2 text-sm font-semibold text-slate-800">
                    {{ $sifatOptions[$detail->sifat] ?? $detail->sifat }}
                </dd>
            </div>

            <div class="bg-white p-5">
                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Katalog</dt>
                <dd class="mt-2">
                    <span
                        class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold
                        {{ $detail->mataKuliah->aktif
                            ? 'border-emerald-100 bg-emerald-50 text-emerald-800'
                            : 'border-rose-100 bg-rose-50 text-rose-700' }}">
                        {{ $detail->mataKuliah->aktif ? 'Aktif di katalog' : 'Nonaktif di katalog' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    @if ($bisaUbah)
        <div class="overflow-hidden rounded-xl border border-rose-200 bg-white shadow-sm">
            <div class="border-b border-rose-100 bg-rose-50/70 px-5 py-4 sm:px-6">
                <h2 class="text-sm font-bold text-rose-800">Keluarkan dari Kurikulum</h2>
                <p class="mt-1 text-xs leading-5 text-rose-700">
                    Tindakan ini menghapus penempatan mata kuliah dari draf kurikulum ini, bukan menghapus data mata kuliah
                    pada katalog.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.kurikulum.mata-kuliah.destroy', [$kurikulum, $detail]) }}"
                class="p-5 sm:p-6">
                @csrf
                @method('DELETE')

                @php
                    $formVersion = old('version', $version ?? '');
                @endphp

                <input type="hidden" name="version" value="{{ is_string($formVersion) ? $formVersion : '' }}">

                @error('version')
                    <p class="mb-3 text-sm font-medium text-rose-600" role="alert">{{ $message }}</p>
                    <p class="mb-4 text-xs text-slate-500">
                        <a class="font-semibold underline underline-offset-2 hover:text-siakad-dark"
                            href="{{ route('admin.kurikulum.mata-kuliah.show', [$kurikulum, $detail]) }}">
                            Muat ulang data terbaru
                        </a>
                    </p>
                @enderror

                <div class="mb-5">
                    <label class="flex items-start gap-2.5 text-sm leading-6 text-slate-700">
                        <input type="checkbox" name="konfirmasi" value="1" required
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-rose-700 focus:ring-rose-600">
                        <span>Saya memahami dan ingin mengeluarkan mata kuliah ini dari kurikulum.</span>
                    </label>

                    @error('konfirmasi')
                        <p class="mt-1.5 text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-rose-200 bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                    </svg>
                    Keluarkan Mata Kuliah
                </button>
            </form>
        </div>
    @endif
@endsection
