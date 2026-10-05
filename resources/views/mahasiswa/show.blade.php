@extends('layouts.admin')

@section('title', 'Detail Mahasiswa')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $mahasiswa->user?->nama ?? 'Detail Mahasiswa' }}</h1>
            <p class="text-sm text-slate-500 mt-1">NIM {{ $mahasiswa->nim }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Biodata
            </a>

            <a href="{{ route('admin.mahasiswa.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <!-- IDENTITAS CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Identitas Mahasiswa</h2>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">NIM</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $mahasiswa->nim }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</dt>
                    <dd class="mt-1 font-bold text-slate-800 text-base">{{ $mahasiswa->user?->nama ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email</dt>
                    <dd class="mt-1 text-slate-700">{{ $mahasiswa->user?->email ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nomor Telepon</dt>
                    <dd class="mt-1 text-slate-700">{{ $mahasiswa->user?->telepon ?? 'Belum diisi' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tempat Lahir</dt>
                    <dd class="mt-1 text-slate-700">{{ $mahasiswa->tempat_lahir ?? 'Belum diisi' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Lahir</dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $mahasiswa->tanggal_lahir?->format('d-m-Y') ?? 'Belum diisi' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</dt>
                    <dd class="mt-1 font-semibold text-slate-700">{{ $mahasiswa->labelJenisKelamin() }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Akun</dt>
                    <dd class="mt-1">
                        @php
                            $isAktif = $mahasiswa->user?->status === 'aktif';
                            $badgeClass = $isAktif
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                : 'bg-rose-50 text-rose-700 border-rose-100';
                            $dotClass = $isAktif ? 'bg-emerald-500' : 'bg-rose-500';
                        @endphp
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                            {{ $isAktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>

                <div class="sm:col-span-2 lg:col-span-4 border-t border-slate-100 pt-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat</dt>
                    <dd class="mt-1 text-slate-700 leading-relaxed">{{ $mahasiswa->alamat ?? 'Belum diisi' }}</dd>
                </div>
            </dl>

            @can('kelola-pengguna')
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-end">
                    <a href="{{ route('admin.users.show', $mahasiswa->user_id) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                        Lihat Akun Pengguna
                    </a>
                </div>
            @endcan
        </div>
    </div>

    <!-- RIWAYAT STUDI CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Studi Mahasiswa</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ $riwayatDaftar->total() }} riwayat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Angkatan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Program Studi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kurikulum</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status Studi</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($riwayatDaftar as $riwayat)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-800 text-xs">{{ $riwayat->angkatan }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $riwayat->kurikulum?->programStudi?->nama ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $riwayat->kurikulum?->nama ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $rStatus = strtolower($riwayat->status);
                                    $rBadge =
                                        $rStatus === 'aktif'
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                            : 'bg-slate-100 text-slate-600 border-slate-200';
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $rBadge }}">
                                    {{ ucfirst($riwayat->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @can('kelola-riwayat-studi')
                                    <a href="{{ route('admin.riwayat-studi.show', $riwayat) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                                        Detail Riwayat
                                    </a>
                                @else
                                    —
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Mahasiswa ini belum mempunyai riwayat studi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            @include('mahasiswa._pagination', ['paginator' => $riwayatDaftar])
        </div>
    </div>
@endsection
