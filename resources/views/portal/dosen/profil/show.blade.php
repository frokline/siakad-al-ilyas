@extends('layouts.admin')

@section('title', 'Profil Dosen')

@section('content')
    <div class="mx-auto w-full max-w-6xl space-y-5 pb-8">

        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">
                    Portal Dosen
                </p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Profil Saya
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    Informasi akun dan identitas dosen pada sistem SIAKAD.
                </p>
            </div>

            <a href="{{ route('portal.dosen.profil.edit') }}"
                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Edit Profil
            </a>
        </header>

        @if (session('info'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800"
                role="status">
                {{ session('info') }}
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,0.8fr)]">

            {{-- Informasi akun --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                aria-labelledby="akun-title">

                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h2 id="akun-title" class="text-base font-bold text-slate-900">
                        Informasi Akun
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Informasi akun yang digunakan untuk mengakses portal dosen.
                    </p>
                </div>

                <div class="p-5 sm:p-6">

                    <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">

                        <div class="relative mx-auto h-28 w-28 shrink-0 sm:mx-0">
                            <img
                                src="{{ route('portal.dosen.profil.foto') }}"
                                alt="Foto profil {{ $user->nama ?? 'Dosen' }}"
                                class="h-full w-full rounded-full border-2 border-white bg-white object-cover shadow-sm ring-1 ring-slate-200"
                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden')">

                            <div class="hidden h-full w-full items-center justify-center rounded-full bg-emerald-100 text-3xl font-bold text-emerald-900 ring-1 ring-emerald-200"
                                aria-hidden="true">
                                {{ mb_strtoupper(mb_substr(trim($user->nama ?? 'D'), 0, 1)) }}
                            </div>
                        </div>

                        <div class="min-w-0 flex-1 text-center sm:text-left">
                            <h2 class="break-words text-xl font-bold text-slate-900 sm:text-2xl">
                                {{ $user->nama ?: 'Nama belum diatur' }}
                            </h2>

                            <p class="mt-1 break-words text-sm text-slate-600">
                                {{ $user->username ? '@' . $user->username : 'Username belum tersedia' }}
                            </p>

                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $dosen->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    <span class="h-1.5 w-1.5 rounded-full
                                        {{ $dosen->status === 'aktif' ? 'bg-emerald-600' : 'bg-slate-500' }}"></span>
                                    {{ ucfirst($dosen->status ?? 'tidak diketahui') }}
                                </span>

                                @if (filled($dosen->kode_dosen))
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">
                                        Kode: {{ $dosen->kode_dosen }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <dl class="mt-5 divide-y divide-slate-100">

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Nama lengkap</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">
                                {{ $user->nama ?: '—' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Username</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">
                                {{ $user->username ?: '—' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Email akun</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">
                                {{ $user->email ?: '—' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Nomor telepon</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">
                                {{ $user->telepon ?: '—' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Kode dosen</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">
                                {{ $dosen->kode_dosen ?: '—' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">NIDN</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">
                                {{ $dosen->nidn ?: '—' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Gelar</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">
                                {{ $dosen->gelar ?: '—' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Alamat</dt>
                            <dd class="break-words whitespace-pre-line text-sm font-semibold text-slate-900">
                                {{ $dosen->alamat ?: 'Belum diisi' }}
                            </dd>
                        </div>

                    </dl>

                    <div class="mt-4 border-t border-slate-100 pt-5">
                        <a href="{{ route('portal.dosen.profil.edit') }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900">
                            Perbarui Informasi Profil
                        </a>
                    </div>

                </div>
            </section>

            {{-- Panel keamanan --}}
            <aside class="space-y-5">

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.4 15l.4 1.8-1.7 2.9-1.8-.5-1.5.9-.5 1.8h-3.4l-.5-1.8-1.5-.9-1.8.5-1.7-2.9.4-1.8-.9-1.5-1.8-.5v-3.4l1.8-.5.9-1.5-.4-1.8 1.7-2.9 1.8.5 1.5-.9.5-1.8h3.4l.5 1.8 1.5.9 1.8-.5 1.7 2.9-.4 1.8.9 1.5 1.8.5v3.4l-1.8.5-.9 1.5z" />
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-900">
                                Pengelolaan Akun
                            </h2>
                            <p class="mt-1.5 text-sm leading-6 text-slate-600">
                                Perbarui foto profil, nomor telepon, alamat, atau kata sandi melalui halaman Edit Profil.
                            </p>

                            <a href="{{ route('portal.dosen.profil.edit') }}"
                                class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-800 underline underline-offset-4 hover:text-emerald-600">
                                Buka pengaturan profil
                            </a>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-800"
                            fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4" />
                        </svg>

                        <div>
                            <h2 class="text-sm font-bold text-emerald-950">
                                Keamanan Akun
                            </h2>
                            <p class="mt-1.5 text-sm leading-6 text-emerald-900">
                                Jangan membagikan kata sandi kepada siapa pun. Gunakan kata sandi yang kuat dan selalu keluar setelah menggunakan perangkat umum.
                            </p>

                            <a href="{{ route('portal.dosen.profil.edit') }}#keamanan-akun"
                                class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-900 underline underline-offset-4 hover:text-emerald-700">
                                Buka pengaturan keamanan
                            </a>
                        </div>
                    </div>
                </section>

            </aside>
        </div>
    </div>
@endsection