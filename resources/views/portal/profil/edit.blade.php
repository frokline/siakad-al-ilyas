@extends('layouts.admin')

@section('title', 'Perbarui Profil Mahasiswa')

@section('content')
    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li><a href="{{ route('portal.profil.show') }}" class="hover:text-siakad-dark hover:underline">Profil
                            Saya</a></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Perbarui Data</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Perbarui Data Pribadi</h1>
            <p class="text-sm text-slate-500">Anda hanya dapat memperbarui nomor telepon dan alamat.</p>
        </div>

        <a href="{{ route('portal.profil.show') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Profil
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kolom Kiri: Form -->
        <div class="space-y-6 lg:col-span-2">

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir Pembaruan Data</h2>
                </div>

                <form method="post" action="{{ route('portal.profil.update') }}" class="p-6">
                    @csrf
                    @method('patch')

                    <div class="space-y-5">
                        <!-- Input: Nomor Telepon -->
                        <div>
                            <label for="telepon" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Nomor Telepon
                            </label>
                            <input id="telepon" type="tel" name="telepon" maxlength="25" autocomplete="tel"
                                inputmode="tel" value="{{ old('telepon', $user->telepon) }}"
                                aria-describedby="bantuan-telepon"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition-colors focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20 @error('telepon') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">

                            @error('telepon')
                                <p class="mt-1.5 text-xs text-rose-600" role="alert">{{ $message }}</p>
                            @else
                                <p id="bantuan-telepon" class="mt-1.5 text-xs text-slate-500">
                                    Contoh: 0812-3456-7890 atau +62 812 3456 7890.
                                </p>
                            @enderror
                        </div>

                        <!-- Input: Alamat -->
                        <div>
                            <label for="alamat" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Alamat Lengkap
                            </label>
                            <textarea id="alamat" name="alamat" rows="5" maxlength="1000" autocomplete="street-address"
                                aria-describedby="bantuan-alamat"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 transition-colors focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20 @error('alamat') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">{{ old('alamat', $mahasiswa->alamat) }}</textarea>

                            @error('alamat')
                                <p class="mt-1.5 text-xs text-rose-600" role="alert">{{ $message }}</p>
                            @else
                                <p id="bantuan-alamat" class="mt-1.5 text-xs text-slate-500">
                                    Masukkan alamat tempat tinggal yang dapat digunakan untuk kebutuhan administrasi kampus.
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 flex items-center gap-4 border-t border-slate-100 pt-5">
                        <button type="submit"
                            class="rounded-xl bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/50 focus:ring-offset-2">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('portal.profil.show') }}"
                            class="text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Aside -->
        <aside class="space-y-6">

            <!-- Kartu: Info Readonly -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4 bg-slate-100">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Identitas Terkunci</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="mb-1 text-xs font-semibold text-slate-500">Nama Lengkap</dt>
                            <dd class="text-sm font-medium text-slate-900">{{ $user->nama }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs font-semibold text-slate-500">NIM</dt>
                            <dd class="text-sm font-medium text-slate-900">{{ $mahasiswa->nim }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs font-semibold text-slate-500">Email Akun</dt>
                            <dd class="text-sm font-medium text-slate-900">{{ $user->email ?: '—' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-5 rounded-lg bg-white p-3 text-xs text-slate-500 border border-slate-200">
                        Nama, NIM, email, dan data akademik lain tidak dapat diperbarui melalui formulir ini.
                    </div>
                </div>
            </div>

            <!-- Kartu: Keterangan Keamanan -->
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold">Keamanan Data</p>
                        <p class="mt-1 leading-relaxed">Sistem hanya menerima pembaruan untuk form di samping. Modifikasi
                            atau manipulasi kode formulir secara paksa untuk mengubah data akademik akan ditolak secara
                            otomatis oleh sistem.</p>
                    </div>
                </div>
            </div>

        </aside>
    </div>
@endsection
