@extends('layouts.admin')

@section('title', 'Perbarui Profil Mahasiswa')

@section('content')
    <div class="mx-auto w-full max-w-6xl space-y-5 pb-6">
        {{-- Header ringkas --}}
        <header class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <nav aria-label="Breadcrumb" class="mb-1.5">
                    <ol class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <li>Portal</li>
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ route('portal.profil.show') }}"
                                class="font-medium hover:text-emerald-800 hover:underline">Profil Saya</a></li>
                        <li aria-hidden="true">/</li>
                        <li class="font-semibold text-emerald-800" aria-current="page">Edit Profil</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Perbarui Profil</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola foto, kontak, alamat, dan kata sandi akun.</p>
            </div>
            <a href="{{ route('portal.profil.show') }}"
                class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Profil
            </a>
        </header>

        @if (session('info'))
            @include('portal.profil._pesan', ['pesan' => session('info')])
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert"
                aria-labelledby="error-title">
                <p id="error-title" class="font-semibold">Ada data yang perlu diperiksa.</p>
                <ul class="mt-1.5 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Identitas singkat + foto: satu baris di laptop, tertumpuk di HP --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            aria-labelledby="account-heading">
            <div class="h-1 bg-emerald-800"></div>
            <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                <div class="flex shrink-0 items-center gap-3 sm:w-64">
                    <div
                        class="relative h-20 w-20 shrink-0 rounded-full bg-slate-100 p-1 ring-1 ring-slate-200 sm:h-24 sm:w-24">
                        <img id="preview-foto-profil" src="{{ route('portal.profil.foto') }}"
                            alt="Foto profil {{ $user->nama ?? 'mahasiswa' }}"
                            class="h-full w-full rounded-full object-cover object-top" onerror="this.style.display='none'">
                        <span
                            class="pointer-events-none absolute inset-1 hidden items-center justify-center rounded-full bg-slate-100 text-2xl font-bold text-emerald-800"
                            id="fallback-foto-profil" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr(trim($user->nama ?? 'M'), 0, 1)) }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p id="account-heading" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Akun
                            mahasiswa</p>
                        <h2 class="mt-1 break-words text-base font-bold text-slate-900">{{ $user->nama ?: 'Mahasiswa' }}
                        </h2>
                        <p class="mt-0.5 break-words text-xs text-slate-500">NIM: {{ $mahasiswa->nim ?: '—' }}</p>
                        <span
                            class="mt-2 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                            <span
                                class="h-1.5 w-1.5 rounded-full {{ $user->status === 'aktif' ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                            {{ ucfirst($user->status ?? 'tidak diketahui') }}
                        </span>
                    </div>
                </div>

                <div class="min-w-0 flex-1 sm:border-l sm:border-slate-100 sm:pl-5">
                    <form method="POST" action="{{ route('portal.profil.foto.update') }}" enctype="multipart/form-data"
                        class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        @csrf
                        <div class="min-w-0 flex-1">
                            <label for="foto" class="mb-1.5 block text-sm font-semibold text-slate-800">Ganti foto
                                profil</label>
                            <input id="foto" name="foto" type="file" required
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                aria-describedby="bantuan-foto"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-emerald-800 hover:file:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700">
                            <p id="bantuan-foto" class="mt-1.5 text-xs text-slate-500">JPG, PNG, atau WebP · Maks. 2 MB</p>
                            @error('foto')
                                <p class="mt-1 text-xs text-rose-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Foto
                        </button>
                    </form>
                </div>
            </div>
        </section>

        {{-- Dua kolom pada desktop mengurangi tinggi halaman --}}
        <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-2">
            {{-- Informasi kontak --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                aria-labelledby="kontak-title">
                <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3.5 sm:px-5">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 5a2 2 0 012-2h3.2a1 1 0 01.95.68l1.1 3.3a1 1 0 01-.5 1.2l-2.1 1.05a11 11 0 005.1 5.1l1.05-2.1a1 1 0 011.2-.5l3.3 1.1a1 1 0 01.68.95V18a2 2 0 01-2 2h-1C8.4 20 4 15.6 4 10V9a4 4 0 01-1-4z" />
                        </svg>
                    </span>
                    <div>
                        <h2 id="kontak-title" class="text-base font-bold text-slate-900">Informasi Kontak</h2>
                        <p class="text-xs text-slate-500">Perbarui nomor telepon dan alamat.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('portal.profil.update') }}" class="space-y-4 p-4 sm:p-5">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="telepon" class="mb-1.5 block text-sm font-semibold text-slate-800">Nomor
                            telepon</label>
                        <input id="telepon" type="tel" name="telepon" maxlength="25" autocomplete="tel"
                            inputmode="tel" value="{{ old('telepon', $user->telepon) }}"
                            placeholder="Contoh: 0812-3456-7890 atau +62 812 3456 7890"
                            class="block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100 @error('telepon') border-rose-500 @else border-slate-300 @enderror">
                        @error('telepon')
                            <p class="mt-1.5 text-xs text-rose-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="alamat" class="mb-1.5 block text-sm font-semibold text-slate-800">Alamat
                            lengkap</label>
                        <textarea id="alamat" name="alamat" rows="4" maxlength="1000" autocomplete="street-address"
                            placeholder="Contoh: Jl. Melati No. 10, RT 02/RW 03, Kelurahan ..., Kecamatan ..."
                            class="block w-full resize-y rounded-lg border bg-white px-3.5 py-2.5 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100 @error('alamat') border-rose-500 @else border-slate-300 @enderror">{{ old('alamat', $mahasiswa->alamat) }}</textarea>
                        @error('alamat')
                            <p class="mt-1.5 text-xs text-rose-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                        <a href="{{ route('portal.profil.show') }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
                        <button type="submit"
                            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">Simpan
                            kontak</button>
                    </div>
                </form>
            </section>

            {{-- Keamanan akun --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                aria-labelledby="password-title">
                <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3.5 sm:px-5">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10V7a4 4 0 118 0v3M12 14v3" />
                        </svg>
                    </span>
                    <div>
                        <h2 id="password-title" class="text-base font-bold text-slate-900">Ubah Kata Sandi</h2>
                        <p class="text-xs text-slate-500">Gunakan kata sandi yang kuat dan unik.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('portal.profil.password.update') }}" class="space-y-4 p-4 sm:p-5">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="current_password" class="mb-1.5 block text-sm font-semibold text-slate-800">Kata sandi
                            saat ini</label>
                        <div class="relative">
                            <input id="current_password" name="current_password" type="password" required
                                autocomplete="current-password" placeholder="Masukkan kata sandi saat ini"
                                class="block w-full rounded-lg border bg-white px-3.5 py-2.5 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100 @error('current_password') border-rose-500 @else border-slate-300 @enderror">
                            <button type="button" data-toggle-password="current_password"
                                aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-500 transition hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-700">
                                <svg data-icon="eye" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg data-icon="eye-off" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1.5 text-xs text-rose-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-800">Kata sandi
                            baru</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required minlength="8"
                                autocomplete="new-password" placeholder="Min. 8 karakter, gabungkan huruf, angka & simbol"
                                aria-describedby="bantuan-password"
                                class="block w-full rounded-lg border bg-white px-3.5 py-2.5 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100 @error('password') border-rose-500 @else border-slate-300 @enderror">
                            <button type="button" data-toggle-password="password" aria-label="Tampilkan kata sandi"
                                title="Tampilkan kata sandi"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-500 transition hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-700">
                                <svg data-icon="eye" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg data-icon="eye-off" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p id="bantuan-password" class="mt-1.5 text-xs leading-5 text-slate-500">Contoh format: <span
                                class="font-semibold text-slate-700">Kampus#2026</span>. Itu hanya contoh—buat kombinasi
                            sendiri dan jangan gunakan contoh tersebut persis.</p>
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation"
                            class="mb-1.5 block text-sm font-semibold text-slate-800">Konfirmasi kata sandi baru</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                minlength="8" autocomplete="new-password" placeholder="Ketik ulang kata sandi baru"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                            <button type="button" data-toggle-password="password_confirmation"
                                aria-label="Tampilkan kata sandi" title="Tampilkan kata sandi"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-500 transition hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-emerald-700">
                                <svg data-icon="eye" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg data-icon="eye-off" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div
                        class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-slate-500">Kata sandi saat ini diverifikasi sebelum perubahan
                            disimpan.</p>
                        <button type="submit"
                            class="inline-flex min-h-10 w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2 sm:w-auto">Perbarui
                            kata sandi</button>
                    </div>
                </form>
            </section>
        </div>

        <p class="text-center text-xs text-slate-500">Jangan bagikan kata sandi kepada siapa pun. Selalu keluar setelah
            menggunakan perangkat umum.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('foto');
            const preview = document.getElementById('preview-foto-profil');
            const fallback = document.getElementById('fallback-foto-profil');
            if (!input || !preview) return;

            let previewUrl = null;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const maxBytes = 2 * 1024 * 1024;

            preview.addEventListener('error', function() {
                preview.classList.add('hidden');
                if (fallback) fallback.classList.remove('hidden');
            });

            input.addEventListener('change', function() {
                const file = input.files && input.files[0];
                if (!file) return;

                if (!allowedTypes.includes(file.type) || file.size > maxBytes) {
                    input.value = '';
                    alert('Pilih gambar JPG, PNG, atau WebP dengan ukuran maksimal 2 MB.');
                    return;
                }

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                if (fallback) fallback.classList.add('hidden');
                preview.src = previewUrl;
            });

            window.addEventListener('pagehide', function() {
                if (previewUrl) URL.revokeObjectURL(previewUrl);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[data-toggle-password]').forEach(function(button) {
                const input = document.getElementById(button.dataset.togglePassword);
                if (!input) return;

                const iconEye = button.querySelector('[data-icon="eye"]');
                const iconEyeOff = button.querySelector('[data-icon="eye-off"]');

                button.addEventListener('click', function() {
                    const isVisible = input.type === 'text';

                    input.type = isVisible ? 'password' : 'text';
                    button.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
                    button.setAttribute('title', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
                    if (iconEye) iconEye.classList.toggle('hidden', !isVisible);
                    if (iconEyeOff) iconEyeOff.classList.toggle('hidden', isVisible);
                });
            });
        });
    </script>
@endsection
