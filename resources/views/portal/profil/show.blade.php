@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    {{-- Pesan informasi --}}
    @if (session('info'))
        @include('portal.profil._pesan', ['pesan' => session('info')])
    @endif

    <div class="space-y-6 pb-8">
        {{-- Header halaman --}}
        <header
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-800 px-6 py-8 text-white shadow-lg sm:px-8 lg:px-10">
            <div class="pointer-events-none absolute -right-12 -top-16 h-64 w-64 rounded-full border-[36px] border-white/5">
            </div>
            <div class="pointer-events-none absolute -bottom-24 right-1/3 h-52 w-52 rounded-full bg-emerald-400/10 blur-2xl">
            </div>

            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div
                        class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Portal Mahasiswa
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Profil Saya</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-emerald-100 sm:text-base">
                        Ringkasan identitas akun, data pribadi, dan perjalanan akademik Anda.
                    </p>
                </div>

                @if (Route::has('portal.profil.edit'))
                    <a href="{{ route('portal.profil.edit') }}"
                        class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-950 shadow-md transition hover:-translate-y-0.5 hover:bg-emerald-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Perbarui Data Pribadi
                    </a>
                @endif
            </div>
        </header>

        {{-- Ringkasan identitas --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 20a8 8 0 0116 0" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Mahasiswa</p>
                <p class="mt-2 truncate text-base font-bold text-slate-900" title="{{ $user->nama }}">{{ $user->nama }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 7h16M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2zM8 11h3m-3 4h8" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor Induk Mahasiswa</p>
                <p class="mt-2 text-base font-bold text-slate-900">{{ $mahasiswa->nim }}</p>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18v14H3zM3 7l9 6 9-6" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email Akun</p>
                <p class="mt-2 truncate text-base font-bold text-slate-900" title="{{ $user->email ?: '—' }}">
                    {{ $user->email ?: '—' }}</p>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Akun</p>
                <div class="mt-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-bold ring-1 ring-inset {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-700 ring-slate-500/20' }}">
                        <span
                            class="h-2 w-2 rounded-full {{ $user->status === 'aktif' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
            {{-- Kolom utama --}}
            <div class="space-y-6 xl:col-span-2">
                {{-- Identitas akun --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-5 sm:px-7">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 20a8 8 0 0116 0" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Identitas Akun</h2>
                            <p class="mt-1 text-sm text-slate-500">Informasi akun yang terdaftar pada sistem.</p>
                        </div>
                    </div>
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-6 p-6 sm:grid-cols-2 sm:p-7">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Lengkap</dt>
                            <dd class="mt-2 break-words text-sm font-semibold text-slate-900">{{ $user->nama }}</dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Username</dt>
                            <dd class="mt-2 break-words text-sm font-semibold text-slate-900">{{ $user->username }}</dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</dt>
                            <dd class="mt-2 break-words text-sm font-semibold text-slate-900">{{ $user->email ?: '—' }}
                            </dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor Telepon</dt>
                            <dd class="mt-2 break-words text-sm font-semibold text-slate-900">{{ $user->telepon ?: '—' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Akun</dt>
                            <dd class="mt-2"><span
                                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}"><span
                                        class="h-2 w-2 rounded-full {{ $user->status === 'aktif' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>{{ ucfirst($user->status) }}</span>
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Identitas mahasiswa --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-5 sm:px-7">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 14l9-5-9-5-9 5 9 5zM5 12v5c4 3 10 3 14 0v-5" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Identitas Mahasiswa</h2>
                            <p class="mt-1 text-sm text-slate-500">Data pribadi dan identitas akademik Anda.</p>
                        </div>
                    </div>
                    <dl class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 sm:p-7">
                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">NIM</dt>
                            <dd class="mt-2 text-sm font-bold text-emerald-800">
                                {{ $mahasiswa->nim ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Mahasiswa</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $user->nama ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Program Studi</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif?->kurikulum?->programStudi?->nama ?? '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Konsentrasi</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->konsentrasi ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tahun Kurikulum</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif?->kurikulum?->tahun_berlaku ?? '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sistem Kuliah</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->sistem_kuliah ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Periode Masuk</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif?->periodeMulai?->kode ?? '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Mahasiswa</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif ? ucfirst($riwayatAktif->status) : 'Belum ada studi aktif' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis Pendaftaran
                            </dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->jenis_pendaftaran ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jalur Pendaftaran
                            </dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->jalur_pendaftaran ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Gelombang</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->gelombang ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal Awal Masuk
                            </dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->tanggal_awal_masuk
                                    ? \Illuminate\Support\Carbon::parse($mahasiswa->tanggal_awal_masuk)->format('d-m-Y')
                                    : '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal Daftar Ulang
                            </dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->tanggal_daftar_ulang
                                    ? \Illuminate\Support\Carbon::parse($mahasiswa->tanggal_daftar_ulang)->format('d-m-Y')
                                    : '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis Kelamin</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                @if ($mahasiswa->jenis_kelamin === 'L')
                                    Laki-laki
                                @elseif ($mahasiswa->jenis_kelamin === 'P')
                                    Perempuan
                                @else
                                    —
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tempat Lahir</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->tempat_lahir ?: '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal Lahir</dt>
                            <dd class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->tanggal_lahir
                                    ? \Illuminate\Support\Carbon::parse($mahasiswa->tanggal_lahir)->format('d-m-Y')
                                    : '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-100 p-4 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat Lengkap</dt>
                            <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-900">
                                {{ $mahasiswa->alamat ?: '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Riwayat studi --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div
                        class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-800">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Riwayat Studi</h2>
                                <p class="mt-1 text-sm text-slate-500">Riwayat program studi yang tercatat.</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ number_format($jumlahRegistrasi, 0, ',', '.') }}
                            Registrasi</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-7">
                                        Angkatan</th>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Program Studi</th>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Periode</th>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @if ($mahasiswa->riwayatStudi && $mahasiswa->riwayatStudi->count() > 0)
                                    @foreach ($mahasiswa->riwayatStudi as $riwayat)
                                        <tr class="transition hover:bg-emerald-50/40">
                                            <td
                                                class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700 sm:px-7">
                                                {{ $riwayat->angkatan }}</td>
                                            <td class="px-5 py-4 text-sm text-slate-700">
                                                <div class="font-semibold text-slate-900">
                                                    {{ $riwayat->kurikulum?->programStudi?->nama ?? '—' }}</div>
                                                <div class="mt-1 text-xs text-slate-500">
                                                    {{ $riwayat->kurikulum?->nama ?? '—' }}</div>
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                                <div class="font-medium text-slate-900">
                                                    {{ $riwayat->periodeMulai?->kode ?? '—' }}</div>
                                                <div class="mt-1 text-xs text-slate-500">s.d.
                                                    {{ $riwayat->periodeAkhir?->kode ?? '—' }}</div>
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm"><span
                                                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $riwayat->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}"><span
                                                        class="h-1.5 w-1.5 rounded-full {{ $riwayat->status === 'aktif' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>{{ ucfirst($riwayat->status) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div
                                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                                </svg>
                                            </div>
                                            <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada riwayat studi
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">Riwayat studi akan tampil jika sudah
                                                tercatat di sistem.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Registrasi semester --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-5 sm:px-7">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 3v3m10-3v3M4 9h16M5 5h14a1 1 0 011 1v13H4V6a1 1 0 011-1z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Registrasi Semester</h2>
                            <p class="mt-1 text-sm text-slate-500">Daftar registrasi akademik setiap semester.</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 sm:px-7">
                                        Periode</th>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Semester</th>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Rombel</th>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Status</th>
                                    <th scope="col"
                                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Revisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php $registrasiDitemukan = false; @endphp
                                @foreach ($mahasiswa->riwayatStudi as $riwayat)
                                    @foreach ($riwayat->registrasiSemester as $registrasi)
                                        @php $registrasiDitemukan = true; @endphp
                                        <tr class="transition hover:bg-emerald-50/40">
                                            <td
                                                class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-900 sm:px-7">
                                                {{ $registrasi->periodeAkademik?->kode ?? '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                                {{ $registrasi->semester_studi }}</td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                                {{ $registrasi->rombel?->kode ?? '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm"><span
                                                    class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($registrasi->status) }}</span>
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                                {{ $registrasi->revisi }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @if (!$registrasiDitemukan)
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <p class="text-sm font-semibold text-slate-700">Belum ada registrasi semester
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">Data registrasi akan tampil di bagian
                                                ini.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Kolom informasi samping --}}
            <aside class="space-y-6 xl:sticky xl:top-6">
                <section
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-800 text-white shadow-lg">
                    <div
                        class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full border-[24px] border-white/5">
                    </div>
                    <div class="relative border-b border-white/10 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-emerald-100 ring-1 ring-white/10">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold">Status Studi Aktif</h2>
                                <p class="mt-1 text-xs text-emerald-200">Informasi akademik terkini</p>
                            </div>
                        </div>
                    </div>
                    <div class="relative p-6">
                        @if ($riwayatAktif)
                            @php
                                $kurikulum = $riwayatAktif->kurikulum;
                                $programStudi = $kurikulum?->programStudi;
                                $dosenPa = $riwayatAktif->dosenPa;
                            @endphp
                            <dl class="space-y-5">
                                <div>
                                    <dt class="text-xs font-medium text-emerald-200">Program Studi</dt>
                                    <dd class="mt-1.5 text-sm font-bold leading-6">
                                        {{ $programStudi ? $programStudi->kode . ' — ' . $programStudi->nama : '—' }}</dd>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10">
                                        <dt class="text-xs text-emerald-200">Jenjang</dt>
                                        <dd class="mt-1.5 text-sm font-bold">{{ $programStudi?->jenjang ?? '—' }}</dd>
                                    </div>
                                    <div class="rounded-xl bg-white/5 p-3 ring-1 ring-white/10">
                                        <dt class="text-xs text-emerald-200">Angkatan</dt>
                                        <dd class="mt-1.5 text-sm font-bold">{{ $riwayatAktif->angkatan }}</dd>
                                    </div>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-emerald-200">Kurikulum</dt>
                                    <dd class="mt-1.5 text-sm font-semibold leading-6">
                                        {{ $kurikulum ? $kurikulum->kode . ' — ' . $kurikulum->nama : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-emerald-200">Periode Mulai</dt>
                                    <dd class="mt-1.5 text-sm font-semibold">
                                        {{ $riwayatAktif->periodeMulai?->kode ?? '—' }}</dd>
                                </div>
                                <div class="border-t border-white/10 pt-5">
                                    <dt class="text-xs font-medium text-emerald-200">Dosen Pembimbing Akademik</dt>
                                    <dd class="mt-1.5 text-sm font-semibold">{{ $dosenPa?->user?->nama ?? '—' }}
                                        @if ($dosenPa?->kode_dosen)
                                            <span
                                                class="mt-1 block text-xs font-medium text-emerald-300">{{ $dosenPa->kode_dosen }}</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="border-t border-white/10 pt-5">
                                    <dt class="text-xs font-medium text-emerald-200">Status Terkini</dt>
                                    <dd
                                        class="mt-2 inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1.5 text-sm font-bold text-emerald-200 ring-1 ring-inset ring-emerald-300/20">
                                        <span
                                            class="h-2 w-2 rounded-full bg-emerald-300"></span>{{ ucfirst($riwayatAktif->status) }}
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <div class="rounded-xl border border-white/10 bg-white/5 p-5 text-center">
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-emerald-100">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="mt-3 text-sm font-semibold">Belum Ada Studi Aktif</p>
                                <p class="mt-1 text-xs leading-5 text-emerald-200">Tidak ada riwayat studi aktif pada akun
                                    ini.</p>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Informasi keamanan --}}
                <section class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Informasi Akademik Terkunci</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Data akademik seperti NIM, program studi,
                                kurikulum, angkatan, status studi, dan dosen pembimbing hanya dapat diubah oleh pengelola
                                akademik.</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h2 class="text-sm font-bold text-emerald-950">Pastikan Data Tetap Akurat</h2>
                            <p class="mt-1.5 text-sm leading-6 text-emerald-800">Periksa informasi kontak Anda secara
                                berkala. Jika terdapat data akademik yang tidak sesuai, silakan hubungi pengelola akademik.
                            </p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
