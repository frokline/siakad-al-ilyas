@extends('layouts.admin')

@section('title', 'Dashboard Admin Akademik')

@section('content')
    <!-- HEADING TITLE -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Dashboard Admin Akademik</h1>
        </div>
    </div>

    <!-- 4 STATS CARDS -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">

        <!-- Card 1: Total Mahasiswa -->
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between h-32 relative">
            <div class="flex justify-between items-start">
                <p class="text-sm font-medium text-slate-600">Total Mahasiswa</p>
                <svg class="h-6 w-6 text-siakad-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="mt-4 flex items-end gap-2">
                <p class="text-3xl font-bold text-slate-900">{{ $ringkasan['mahasiswa'] }}</p>
                <p class="text-xs text-slate-500 mb-1">Akun Terdaftar</p>
            </div>
        </div>

        <!-- Card 2: KRS Menunggu Pengesahan -->
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between h-32 relative">
            <div class="flex justify-between items-start">
                <p class="text-sm font-medium text-slate-600">KRS Diajukan</p>
                @if ($ringkasan['krs_diajukan'] > 0)
                    <span class="absolute top-4 right-4 flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                @else
                    <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>
            <div class="mt-4 flex items-end gap-2">
                <p class="text-3xl font-bold {{ $ringkasan['krs_diajukan'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $ringkasan['krs_diajukan'] }}</p>
                <p class="text-xs text-slate-500 mb-1">Menunggu Sah</p>
            </div>
        </div>

        <!-- Card 3: Kelas Aktif -->
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between h-32 relative">
            <div class="flex justify-between items-start">
                <p class="text-sm font-medium text-slate-600">Kelas Aktif</p>
                <svg class="h-6 w-6 text-siakad-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
            </div>
            <div class="mt-4 flex items-end justify-between gap-2">
                <div class="flex items-end gap-2">
                    <p class="text-3xl font-bold text-slate-900">{{ $ringkasan['kelas_aktif'] }}</p>
                    <p class="text-xs text-slate-500 mb-1">Kelas Berjalan</p>
                </div>
                @can('kelola-kelas-kuliah')
                    <a href="{{ route('admin.kelas-kuliah.index') }}"
                        class="text-xs font-semibold text-siakad-dark hover:underline mb-1">Kelola</a>
                @endcan
            </div>
        </div>

        <!-- Card 4: Pertemuan Berlangsung -->
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between h-32 relative">
            <div class="flex justify-between items-start">
                <p class="text-sm font-medium text-slate-600">Pertemuan Kelas</p>
                <svg class="h-6 w-6 text-siakad-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="mt-4 flex items-end gap-2">
                <p class="text-3xl font-bold text-slate-900">{{ $ringkasan['pertemuan_berlangsung'] }}</p>
                <p class="text-xs text-slate-500 mb-1">Sesi Aktif</p>
            </div>
        </div>
    </div>

    <!-- WIDGET AREA (Grid 2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Widget Kiri: Rekap Data Master -->
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            <h2 class="text-base font-bold text-slate-800 mb-4">Master Data Akademik</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Modul Data</th>
                            <th scope="col" class="px-4 py-3 font-medium text-center">Jumlah</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">Program Studi</td>
                            <td class="px-4 py-3 text-center">{{ $ringkasan['program_studi'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('kelola-program-studi')
                                    <a href="{{ route('admin.program-studi.index') }}"
                                        class="text-siakad-dark hover:underline font-semibold">Kelola</a>
                                @endcan
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">Periode Akademik Aktif</td>
                            <td class="px-4 py-3 text-center">{{ $ringkasan['periode_aktif'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('kelola-periode-akademik')
                                    <a href="{{ route('admin.periode-akademik.index') }}"
                                        class="text-siakad-dark hover:underline font-semibold">Kelola</a>
                                @endcan
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">Kurikulum Aktif</td>
                            <td class="px-4 py-3 text-center">{{ $ringkasan['kurikulum_aktif'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('kelola-kurikulum')
                                    <a href="{{ route('admin.kurikulum.index') }}"
                                        class="text-siakad-dark hover:underline font-semibold">Kelola</a>
                                @endcan
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">Mata Kuliah</td>
                            <td class="px-4 py-3 text-center">{{ $ringkasan['mata_kuliah_aktif'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('kelola-mata-kuliah')
                                    <a href="{{ route('admin.mata-kuliah.index') }}"
                                        class="text-siakad-dark hover:underline font-semibold">Kelola</a>
                                @endcan
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">Dosen Aktif</td>
                            <td class="px-4 py-3 text-center">{{ $ringkasan['dosen_aktif'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('kelola-dosen')
                                    <a href="{{ route('admin.dosen.index') }}"
                                        class="text-siakad-dark hover:underline font-semibold">Kelola</a>
                                @endcan
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">Registrasi Semester Aktif</td>
                            <td class="px-4 py-3 text-center">{{ $ringkasan['registrasi_aktif'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('kelola-registrasi-semester')
                                    <a href="{{ route('admin.registrasi-semester.index') }}"
                                        class="text-siakad-dark hover:underline font-semibold">Kelola</a>
                                @endcan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Widget Kanan: Layanan & Pengumuman -->
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            <h2 class="text-base font-bold text-slate-800 mb-4">Layanan Akademik Penting</h2>
            <div class="space-y-4">

                @can('kelola-krs')
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-rose-50 border border-rose-100">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-rose-800">Verifikasi KRS Mahasiswa</p>
                            <p class="text-xs text-rose-600 mt-0.5">Terdapat {{ $ringkasan['krs_diajukan'] }} ajuan KRS yang
                                belum disahkan.</p>
                            <a href="{{ route('admin.krs.index', ['status' => 'diajukan']) }}"
                                class="inline-block mt-2 text-xs font-bold bg-rose-100 text-rose-700 px-3 py-1.5 rounded hover:bg-rose-200 transition-colors">Periksa
                                Sekarang</a>
                        </div>
                    </div>
                @endcan

                @can('kelola-jadwal-kuliah')
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">Jadwal Perkuliahan</p>
                            <p class="text-xs text-emerald-600 mt-0.5">Kelola hari, jam, dan ruang kelas perkuliahan aktif.</p>
                            <a href="{{ route('admin.jadwal-kuliah.index') }}"
                                class="inline-block mt-2 text-xs font-bold bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded hover:bg-emerald-200 transition-colors">Kelola
                                Jadwal</a>
                        </div>
                    </div>
                @endcan

                <div class="text-xs text-slate-400 mt-4 text-center">
                    <p>Dashboard hanya menampilkan ringkasan data resmi dari database.</p>
                </div>
            </div>
        </div>

    </div>
@endsection
