@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    <div class="mx-auto w-full max-w-6xl space-y-5 pb-8">
        {{-- Header halaman --}}
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Portal Mahasiswa</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Profil Saya</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Informasi akun yang digunakan untuk mengakses portal mahasiswa.
                </p>
            </div>

            @if (Route::has('portal.profil.edit'))
                <a href="{{ route('portal.profil.edit') }}"
                    class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Edit profil
                </a>
            @endif
        </header>

        {{-- Pesan sistem --}}
        @if (session('info'))
            @include('portal.profil._pesan', ['pesan' => session('info')])
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900" role="alert">
                <p class="font-semibold">Ada data yang perlu diperiksa.</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.85fr)]">
            {{-- Informasi akun utama --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                aria-labelledby="akun-title">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h2 id="akun-title" class="text-base font-bold text-slate-900">Informasi akun</h2>
                    <p class="mt-1 text-sm text-slate-500">Detail akun yang terdaftar pada sistem SIAKAD.</p>
                </div>

                <div class="p-5 sm:p-6">
                    <div
                        class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">
                        <div class="relative mx-auto h-24 w-24 shrink-0 sm:mx-0 sm:h-28 sm:w-28">
                            <img src="{{ route('portal.profil.foto') }}" alt="Foto profil {{ $user->nama ?? 'mahasiswa' }}"
                                class="h-full w-full rounded-full border-2 border-white bg-white object-cover shadow-sm ring-1 ring-slate-200"
                                loading="lazy"
                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden')">
                            <div class="hidden h-full w-full items-center justify-center rounded-full bg-emerald-100 text-3xl font-bold text-emerald-900 ring-1 ring-emerald-200"
                                aria-hidden="true">
                                {{ mb_strtoupper(mb_substr(trim($user->nama ?? 'M'), 0, 1)) }}
                            </div>
                        </div>

                        <div class="min-w-0 flex-1 text-center sm:text-left">
                            <h2 class="break-words text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                                {{ $user->nama ?: 'Nama belum diatur' }}
                            </h2>
                            <p class="mt-1 break-words text-sm text-slate-600">
                                {{ $user->username ? '@' . $user->username : 'Username belum tersedia' }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ ($user->status ?? null) === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full {{ ($user->status ?? null) === 'aktif' ? 'bg-emerald-600' : 'bg-slate-500' }}"></span>
                                    {{ ucfirst($user->status ?? 'tidak diketahui') }}
                                </span>
                                @if (filled($mahasiswa->nim ?? null))
                                    <span
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">
                                        NIM: {{ $mahasiswa->nim }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <dl class="mt-5 divide-y divide-slate-100">
                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Nama lengkap</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $user->nama ?: '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Username</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $user->username ?: '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Email akun</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $user->email ?: '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-[150px_minmax(0,1fr)] sm:gap-4">
                            <dt class="text-sm text-slate-500">Nomor telepon</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $user->telepon ?: '—' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row">
                        @if (Route::has('portal.profil.edit'))
                            <a href="{{ route('portal.profil.edit') }}"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                                Perbarui informasi akun
                            </a>
                        @endif
                        @if (Route::has('portal.biodata.show'))
                            <a href="{{ route('portal.biodata.show') }}"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                                Lihat biodata
                            </a>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Panel samping ringkas --}}
            <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
                    aria-labelledby="pengelolaan-title">
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06-1.7 2.94-.08-.02a1.7 1.7 0 00-1.76.64l-.05.07h-3.4l-.04-.08a1.7 1.7 0 00-1.57-.98c-.3 0-.58.08-.84.22l-.08.04-2.94-1.7.02-.08a1.7 1.7 0 00-.64-1.76l-.07-.05v-3.4l.08-.04a1.7 1.7 0 00.76-2.41l-.04-.08 1.7-2.94.08.02a1.7 1.7 0 001.76-.64l.05-.07h3.4l.04.08a1.7 1.7 0 002.41.76l.08-.04 2.94 1.7-.02.08a1.7 1.7 0 00.64 1.76l.07.05v3.4l-.08.04z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 id="pengelolaan-title" class="text-sm font-bold text-slate-900">Pengelolaan akun</h2>
                            <p class="mt-1.5 text-sm leading-6 text-slate-600">
                                Perbarui nomor telepon, alamat, atau foto melalui halaman edit profil. Perubahan data
                                akademik dan identitas tertentu mengikuti kewenangan pengelola kampus.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 sm:p-6"
                    aria-labelledby="tips-title">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-800" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        </svg>
                        <div class="min-w-0">
                            <h2 id="tips-title" class="text-sm font-bold text-emerald-950">Jaga keamanan akun</h2>
                            <p class="mt-1.5 text-sm leading-6 text-emerald-900">
                                Jangan membagikan kata sandi kepada siapa pun. Jika ingin mengganti kata sandi, buka halaman
                                <span class="font-semibold">Edit Profil</span> pada bagian keamanan akun.
                            </p>
                            @if (Route::has('portal.profil.edit'))
                                <a href="{{ route('portal.profil.edit') }}#keamanan-akun"
                                    class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-900 underline decoration-emerald-400 underline-offset-4 hover:text-emerald-700">
                                    Buka pengaturan keamanan
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
