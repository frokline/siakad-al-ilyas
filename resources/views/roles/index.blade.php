@extends('layouts.admin')

@section('title', 'Peran & Hak Akses')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Peran & Hak Akses</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar peran inti sistem SIAKAD. Pemberian akses akun dilakukan di menu
                Pengguna.</p>
        </div>
    </div>

    <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 flex gap-3">
        <svg class="h-5 w-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm text-amber-800">
            <strong>Penting:</strong> Kode peran adalah identitas tetap sistem. Gunakan menu <a
                href="{{ route('admin.users.index') }}" class="font-bold underline hover:text-amber-900">Pengguna Sistem</a>
            untuk memberikan peran kepada akun.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($roles as $role)
            <div
                class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:border-siakad-dark transition-colors">
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="text-base font-bold text-slate-800">{{ $role->nama }}</h2>
                        <span
                            class="inline-block px-2 py-1 text-[10px] font-mono text-slate-500 bg-slate-100 rounded border border-slate-200">
                            {{ $role->kode }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-600 mb-4 line-clamp-3">
                        @switch($role->kode)
                            @case('mahasiswa')
                                Mengakses KRS, jadwal, pembelajaran, tugas, dan rekap presensi mahasiswa.
                            @break

                            @case('dosen')
                                Mengelola kelas ajar, materi, tugas, dan presensi pertemuan mahasiswa.
                            @break

                            @case('admin_akademik')
                                Mengelola akun, kurikulum, kelas, jadwal, dan operasional akademik.
                            @break

                            @case('admin_keuangan')
                                Mengelola tagihan, jenis biaya, dan memverifikasi pembayaran.
                            @break

                            @default
                                Peran dasar pengguna terdaftar di dalam sistem.
                        @endswitch
                    </p>

                    <div class="flex items-center gap-2 mt-auto">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-xs font-bold text-slate-700">{{ $role->users_count ?? '0' }} Akun</span>
                    </div>
                </div>

                <div class="bg-slate-50 border-t border-slate-100 p-4">
                    <a href="{{ route('admin.users.index', ['role' => $role->kode]) }}"
                        class="w-full flex justify-center items-center gap-2 rounded-lg bg-white border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                        Lihat Pengguna
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection
