@extends('layouts.admin')

@section('title', 'Detail Dosen')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $dosen->user->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">Kode dosen {{ $dosen->kode_dosen }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dosen.edit', $dosen) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Dosen
            </a>

            <a href="{{ route('admin.dosen.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    @if (!$memilikiPeranDosen)
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center justify-between"
            role="status">
            <span>Akun terhubung tidak memiliki peran Dosen.</span>
            <a href="{{ route('admin.users.edit', $dosen->user) }}" class="font-semibold underline hover:text-amber-900">
                Kelola peran akun
            </a>
        </div>
    @endif

    <!-- DETAIL CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Detail Dosen</h2>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Dosen</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $dosen->kode_dosen }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4 sm:col-span-2 lg:col-span-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Dosen</dt>
                    <dd class="mt-1 font-bold text-slate-800 text-base">{{ $dosen->user->nama }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">NIDN</dt>
                    <dd class="mt-1 font-mono text-slate-700">{{ $dosen->nidn ?? 'Belum diisi' }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gelar</dt>
                    <dd class="mt-1 text-slate-700">{{ $dosen->gelar ?? 'Belum diisi' }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Akun Terhubung</dt>
                    <dd class="mt-1">
                        <a href="{{ route('admin.users.show', $dosen->user) }}"
                            class="font-mono font-semibold text-siakad-dark hover:underline">
                            {{ $dosen->user->username }}
                        </a>
                    </dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email</dt>
                    <dd class="mt-1 text-slate-700">{{ $dosen->user->email }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telepon</dt>
                    <dd class="mt-1 text-slate-700">{{ $dosen->user->telepon ?? 'Belum diisi' }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Dosen</dt>
                    <dd class="mt-1">
                        @php
                            $isAktif = $dosen->isAktif();
                            $badgeClass = $isAktif
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                : 'bg-rose-50 text-rose-700 border-rose-100';
                            $dotClass = $isAktif ? 'bg-emerald-500' : 'bg-rose-500';
                        @endphp
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                            {{ $statusOptions[$dosen->status] }}
                        </span>
                    </dd>
                </div>

                <div class="sm:col-span-2 lg:col-span-3 pt-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Akun</dt>
                    <dd class="mt-1">
                        @php
                            $akunAktif = $dosen->user->isAktif();
                            $akunBadge = $akunAktif
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                : 'bg-slate-100 text-slate-600 border-slate-200';
                            $akunDot = $akunAktif ? 'bg-emerald-500' : 'bg-slate-400';
                        @endphp
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $akunBadge }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $akunDot }}"></span>
                            {{ $akunAktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
