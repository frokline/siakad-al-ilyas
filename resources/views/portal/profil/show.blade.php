@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    <!-- Flash Messages (Info) -->
    @if (session('info'))
        @include('portal.profil._pesan', ['pesan' => session('info')])
    @endif

    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Profil Saya</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Profil Mahasiswa</h1>
            <p class="text-sm text-slate-500">Informasi identitas dan riwayat akademik mahasiswa.</p>
        </div>

        @if (Route::has('portal.profil.edit'))
            <a href="{{ route('portal.profil.edit') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/50 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Perbarui Data Pribadi
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kolom Kiri: Konten Utama -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Kartu: Identitas Akun -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Identitas Akun</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Lengkap</dt>
                            <dd class="text-sm text-slate-900">{{ $user->nama }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Username</dt>
                            <dd class="text-sm text-slate-900">{{ $user->username }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Email</dt>
                            <dd class="text-sm text-slate-900">{{ $user->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor Telepon</dt>
                            <dd class="text-sm text-slate-900">{{ $user->telepon ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Status Akun</dt>
                            <dd class="text-sm text-slate-900">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-700 ring-slate-600/20' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Kartu: Identitas Mahasiswa -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Identitas Mahasiswa</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">NIM</dt>
                            <dd class="text-sm font-medium text-siakad-dark">{{ $mahasiswa->nim }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis Kelamin</dt>
                            <dd class="text-sm text-slate-900">
                                @if ($mahasiswa->jenis_kelamin === 'L')
                                    Laki-laki
                                @elseif ($mahasiswa->jenis_kelamin === 'P')
                                    Perempuan
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Tempat Lahir</dt>
                            <dd class="text-sm text-slate-900">{{ $mahasiswa->tempat_lahir ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Tanggal Lahir</dt>
                            <dd class="text-sm text-slate-900">
                                {{ $mahasiswa->tanggal_lahir ? \Illuminate\Support\Carbon::parse($mahasiswa->tanggal_lahir)->format('d-m-Y') : '—' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat Lengkap</dt>
                            <dd class="text-sm text-slate-900">{{ $mahasiswa->alamat ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Kartu: Riwayat Studi -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Riwayat Studi</h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                        {{ number_format($jumlahRegistrasi, 0, ',', '.') }} Registrasi
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Angkatan</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Program Studi</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Periode</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @if ($mahasiswa->riwayatStudi && $mahasiswa->riwayatStudi->count() > 0)
                                @foreach ($mahasiswa->riwayatStudi as $riwayat)
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            {{ $riwayat->angkatan }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-700">
                                            <div class="font-medium text-slate-900">
                                                {{ $riwayat->kurikulum?->programStudi?->nama ?? '—' }}</div>
                                            <div class="text-xs text-slate-500">{{ $riwayat->kurikulum?->nama ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            <div class="text-slate-900">{{ $riwayat->periodeMulai?->kode ?? '—' }}</div>
                                            <div class="text-xs text-slate-500">s.d
                                                {{ $riwayat->periodeAkhir?->kode ?? '—' }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            <span
                                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $riwayat->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-700 ring-slate-600/20' }}">
                                                {{ ucfirst($riwayat->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Belum ada
                                        riwayat studi.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kartu: Registrasi Semester -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Registrasi Semester</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Periode</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Smt</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Rombel</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Status</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Revisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @php $registrasiDitemukan = false; @endphp
                            @foreach ($mahasiswa->riwayatStudi as $riwayat)
                                @foreach ($riwayat->registrasiSemester as $registrasi)
                                    @php $registrasiDitemukan = true; @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                            {{ $registrasi->periodeAkademik?->kode ?? '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            {{ $registrasi->semester_studi }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            {{ $registrasi->rombel?->kode ?? '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            {{ ucfirst($registrasi->status) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            {{ $registrasi->revisi }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach

                            @if (!$registrasiDitemukan)
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Belum ada
                                        registrasi semester.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Aside -->
        <aside class="space-y-6">

            <!-- Kartu: Status Studi Aktif (Panel Hijau) -->
            <div class="overflow-hidden rounded-2xl border border-siakad-dark bg-siakad-dark text-white shadow-sm">
                <div class="border-b border-emerald-800 px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-50">Status Studi Aktif</h2>
                </div>
                <div class="p-6">
                    @if ($riwayatAktif)
                        @php
                            $kurikulum = $riwayatAktif->kurikulum;
                            $programStudi = $kurikulum?->programStudi;
                            $dosenPa = $riwayatAktif->dosenPa;
                        @endphp
                        <dl class="space-y-4">
                            <div>
                                <dt class="mb-1 text-xs font-medium text-emerald-200">Program Studi</dt>
                                <dd class="text-sm font-semibold">
                                    {{ $programStudi ? $programStudi->kode . ' — ' . $programStudi->nama : '—' }}
                                </dd>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="mb-1 text-xs font-medium text-emerald-200">Jenjang</dt>
                                    <dd class="text-sm font-semibold">{{ $programStudi?->jenjang ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="mb-1 text-xs font-medium text-emerald-200">Angkatan</dt>
                                    <dd class="text-sm font-semibold">{{ $riwayatAktif->angkatan }}</dd>
                                </div>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs font-medium text-emerald-200">Kurikulum</dt>
                                <dd class="text-sm font-semibold">
                                    {{ $kurikulum ? $kurikulum->kode . ' — ' . $kurikulum->nama : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs font-medium text-emerald-200">Periode Mulai</dt>
                                <dd class="text-sm font-semibold">{{ $riwayatAktif->periodeMulai?->kode ?? '—' }}</dd>
                            </div>
                            <div class="border-t border-emerald-800 pt-4">
                                <dt class="mb-1 text-xs font-medium text-emerald-200">Dosen Pembimbing Akademik</dt>
                                <dd class="text-sm font-semibold">
                                    {{ $dosenPa?->user?->nama ?? '—' }}
                                    @if ($dosenPa?->kode_dosen)
                                        <span class="block text-xs text-emerald-300">{{ $dosenPa->kode_dosen }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="border-t border-emerald-800 pt-4">
                                <dt class="mb-1 text-xs font-medium text-emerald-200">Status Terkini</dt>
                                <dd class="text-base font-bold text-siakad-accent">{{ ucfirst($riwayatAktif->status) }}
                                </dd>
                            </div>
                        </dl>
                    @else
                        <div class="rounded-lg bg-emerald-800/50 p-4 text-center text-sm text-emerald-100">
                            Tidak ada riwayat studi aktif pada akun ini.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kartu: Keterangan Keamanan -->
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold">Informasi Akademik Terkunci</p>
                        <p class="mt-1 leading-relaxed">Data akademik seperti NIM, program studi, kurikulum, angkatan,
                            status studi, dan dosen pembimbing hanya dapat diubah oleh pengelola akademik.</p>
                    </div>
                </div>
            </div>

        </aside>
    </div>
@endsection
