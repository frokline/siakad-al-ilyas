@extends('layouts.admin')
@section('title', 'Detail Penugasan Dosen')

@section('content')
    @php
        $zona = config('siakad.timezone', 'Asia/Makassar');
        $profilSiap =
            $penugasan->dosen->status === \App\Models\Dosen::AKTIF &&
            $penugasan->dosen->user->isAktif() &&
            $penugasan->dosen->user->roles->contains('kode', \App\Models\Role::DOSEN);
    @endphp
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Penugasan #{{ $penugasan->id }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $penugasan->dosen->kode_dosen }} — {{ $penugasan->dosen->user->nama }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if ($penugasan->dapatDiubah())
                <a class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                    href="{{ route('admin.pengajar-kelas.edit', $penugasan) }}">Edit penugasan</a>
                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                    href="{{ route('admin.pengajar-kelas.create', ['kelas_id' => $kelas->id]) }}">Tambah dosen kelas ini</a>
            @endif
            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                href="{{ route('admin.pengajar-kelas.index') }}">Daftar penugasan</a>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Identitas Kelas</h2>
        </div>
        <div class="p-6">@include('admin.pengajar-kelas._kelas')</div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Penugasan Dosen</h2>
            <span
                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $penugasan->aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $penugasan->aktif ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                {{ $penugasan->aktif ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm mb-6">
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dosen</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        <a href="{{ route('admin.dosen.show', $penugasan->dosen) }}"
                            class="text-siakad-dark hover:underline">{{ $penugasan->dosen->user->nama }}</a>{{ $penugasan->dosen->gelar ? ', ' . $penugasan->dosen->gelar : '' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Peran</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ \App\Models\PengajarKelas::PERAN[$penugasan->peran] }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Revisi</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-800">{{ $penugasan->revisi }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Profil Dosen</dt>
                    <dd
                        class="mt-1 font-semibold {{ $penugasan->dosen->status === \App\Models\Dosen::AKTIF ? 'text-emerald-700' : 'text-rose-600' }}">
                        {{ $penugasan->dosen->status === \App\Models\Dosen::AKTIF ? 'Aktif' : 'Nonaktif' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aktivasi Terakhir</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        {{ $penugasan->diaktifkan_at->timezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Penonaktifan Terakhir</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        {{ $penugasan->dinonaktifkan_at?->timezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dibuat</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        {{ $penugasan->created_at->timezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Diperbarui</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        {{ $penugasan->updated_at->timezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
            </dl>
            @unless ($profilSiap)
                <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
                    Profil/akun/role dosen belum memenuhi syarat mengajar. Periksa data dosen atau tetapkan pengganti sesuai
                    kebutuhan kelas.
                </div>
            @endunless
            <p class="text-xs text-slate-500">
                Akses pembelajaran mengikuti penugasan aktif, kelayakan akun dosen, serta kelas dan periode yang aktif.
                Riwayat penugasan tetap tersedia setelah kelas selesai.
            </p>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Seluruh Tim Kelas Ini</h2>
        </div>
        @include('admin.pengajar-kelas._tim')
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Penugasan</h2>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-500 mb-4">Perubahan terbaru ditampilkan terlebih dahulu. Waktu menggunakan
                {{ $zona }}.</p>
            @include('admin.pengajar-kelas._audit')
        </div>
    </div>
@endsection
