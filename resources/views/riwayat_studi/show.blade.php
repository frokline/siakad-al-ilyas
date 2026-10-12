@extends('layouts.admin')

@section('title', 'Detail Riwayat Studi')

@section('content')
    @php
        $mhs = $riwayatStudi->mahasiswa;
        $pa = $riwayatStudi->dosenPa;
        $akunAktif = $mhs->user->isAktif();
        $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
        $kepala = 'rounded-t-2xl border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
        $judul = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
        $label = 'text-xs font-bold uppercase tracking-wider text-slate-500';
        $tautan = 'font-medium text-siakad-active underline-offset-2 hover:underline';
    @endphp

    {{-- Judul halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('admin.riwayat-studi.index') }}" class="hover:text-siakad-dark">Riwayat Studi</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-700">Detail</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900">Detail Riwayat Studi</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $mhs->nim }} — {{ $mhs->user->nama }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.riwayat-studi.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Kembali</a>
            @if ($riwayatStudi->isAktif())
                <a href="{{ route('admin.riwayat-studi.edit', $riwayatStudi) }}"
                    class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-siakad-active">Edit
                    Riwayat</a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            {{-- Identitas studi --}}
            <section class="{{ $kartu }}">
                <div class="{{ $kepala }}">
                    <h2 class="{{ $judul }}">Identitas studi</h2>
                </div>
                <dl class="grid gap-x-8 gap-y-5 p-6 sm:grid-cols-2">
                    <div>
                        <dt class="{{ $label }}">NIM</dt>
                        <dd class="mt-1 text-sm">
                            <a href="{{ route('admin.mahasiswa.show', $mhs) }}"
                                class="{{ $tautan }}">{{ $mhs->nim }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Nama mahasiswa</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $mhs->user->nama }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Program studi</dt>
                        <dd class="mt-1 text-sm">
                            <a href="{{ route('admin.program-studi.show', $riwayatStudi->kurikulum->programStudi) }}"
                                class="{{ $tautan }}">{{ $riwayatStudi->kurikulum->programStudi->nama }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Kurikulum</dt>
                        <dd class="mt-1 text-sm">
                            <a href="{{ route('admin.kurikulum.show', $riwayatStudi->kurikulum) }}"
                                class="{{ $tautan }}">{{ $riwayatStudi->kurikulum->kode }} —
                                {{ $riwayatStudi->kurikulum->nama }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Angkatan</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $riwayatStudi->angkatan }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Perjalanan studi --}}
            <section class="{{ $kartu }}">
                <div class="{{ $kepala }}">
                    <h2 class="{{ $judul }}">Perjalanan studi</h2>
                </div>
                <div class="p-6">
                    <ol class="relative space-y-6 border-l-2 border-emerald-100 pl-6">
                        <li class="relative">
                            <span
                                class="absolute -left-[31px] top-1 h-4 w-4 rounded-full border-2 border-white bg-siakad-dark ring-2 ring-emerald-200"></span>
                            <p class="{{ $label }}">Periode mulai</p>
                            <p class="mt-1 text-sm">
                                <a href="{{ route('admin.periode-akademik.show', $riwayatStudi->periodeMulai) }}"
                                    class="{{ $tautan }}">{{ $riwayatStudi->periodeMulai->kode }}</a>
                                <span class="text-slate-500">·
                                    {{ $riwayatStudi->periodeMulai->mulai?->format('d-m-Y') }}</span>
                            </p>
                        </li>
                        <li class="relative">
                            <span
                                class="absolute -left-[31px] top-1 h-4 w-4 rounded-full border-2 border-white ring-2 {{ $riwayatStudi->periodeAkhir ? 'bg-slate-500 ring-slate-200' : 'bg-white ring-slate-300' }}"></span>
                            <p class="{{ $label }}">Periode akhir</p>
                            <p class="mt-1 text-sm">
                                @if ($riwayatStudi->periodeAkhir)
                                    <a href="{{ route('admin.periode-akademik.show', $riwayatStudi->periodeAkhir) }}"
                                        class="{{ $tautan }}">{{ $riwayatStudi->periodeAkhir->kode }}</a>
                                    <span class="text-slate-500">·
                                        {{ $riwayatStudi->periodeAkhir->mulai?->format('d-m-Y') }}</span>
                                @else
                                    <span class="text-slate-500">Belum berakhir — studi masih berjalan.</span>
                                @endif
                            </p>
                        </li>
                    </ol>
                </div>
            </section>

            {{-- Pembimbing akademik --}}
            <section class="{{ $kartu }}">
                <div class="{{ $kepala }}">
                    <h2 class="{{ $judul }}">Dosen pembimbing akademik</h2>
                </div>
                <div class="p-6">
                    @if ($pa)
                        <div class="flex items-center gap-4">
                            <span
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-siakad-dark">
                                {{ strtoupper(mb_substr($pa->user->nama ?? '?', 0, 1)) }}
                            </span>
                            <div>
                                <a href="{{ route('admin.dosen.show', $pa) }}"
                                    class="text-base font-semibold text-slate-900 hover:text-siakad-dark">{{ $pa->user->nama }}</a>
                                <p class="text-xs text-slate-500">Kode dosen: {{ $pa->kode_dosen }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Belum ditentukan.
                            @if ($riwayatStudi->isAktif())
                                <a href="{{ route('admin.riwayat-studi.edit', $riwayatStudi) }}"
                                    class="{{ $tautan }}">Tetapkan
                                    sekarang</a>
                            @endif
                        </p>
                    @endif
                </div>
            </section>
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-6">
            <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Status studi</p>
                <div class="mt-3">
                    @include('riwayat_studi._lencana', ['status' => $riwayatStudi->status])
                </div>
                <p class="mt-4 text-sm leading-6 text-emerald-50">
                    @if ($riwayatStudi->isAktif())
                        Riwayat ini masih berjalan dan dapat diedit: dosen PA dapat diganti, atau riwayat ditutup bila
                        studi berakhir.
                    @else
                        Riwayat ini sudah ditutup dan dipertahankan sebagai catatan akademik. Data tidak dapat diubah lagi.
                    @endif
                </p>
            </section>

            <section class="{{ $kartu }} p-6">
                <p class="{{ $label }}">Akun mahasiswa</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-sm text-slate-700">{{ $mhs->user->nama }}</span>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $akunAktif ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20' }}">
                        {{ $akunAktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <a href="{{ route('admin.mahasiswa.show', $mhs) }}"
                    class="mt-4 inline-flex text-sm {{ $tautan }}">Lihat data mahasiswa →</a>
            </section>
        </aside>
    </div>
@endsection
