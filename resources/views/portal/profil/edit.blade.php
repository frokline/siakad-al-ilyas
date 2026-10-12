@extends('layouts.admin')

@section('title', 'Perbarui Profil Mahasiswa')

@section('content')
    <!-- Kepala Halaman -->
    <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-2">
                <ol class="flex items-center gap-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="text-slate-300">/</span></li>
                    <li><a href="{{ route('portal.profil.show') }}"
                            class="transition hover:text-siakad-dark hover:underline">Profil Saya</a></li>
                    <li><span class="text-slate-300">/</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Perbarui Data</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Perbarui Data Pribadi</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Perbarui nomor telepon dan alamat tempat tinggal
                Anda. Data identitas utama tetap terlindungi.</p>
        </div>

        <a href="{{ route('portal.profil.show') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-siakad-dark/30 focus:ring-offset-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Profil
        </a>
    </div>

    <!-- Kartu Foto Profil -->
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div
            class="flex flex-col gap-1 border-b border-slate-100 bg-gradient-to-r from-emerald-50 via-white to-white px-5 py-5 sm:px-7">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 7a3 3 0 11-6 0 3 3 0 016 0zM4 20a8 8 0 0116 0" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Foto Profil</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Gunakan foto yang jelas agar mudah dikenali.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-6 p-5 sm:flex-row sm:items-center sm:p-7">
            <div class="flex shrink-0 flex-col items-center gap-3 sm:items-start">
                <img id="preview-foto-profil" src="{{ route('portal.profil.foto') }}" alt="Foto profil mahasiswa"
                    class="h-28 w-28 rounded-2xl border border-slate-200 bg-slate-100 object-cover shadow-sm sm:h-32 sm:w-32">
                <span class="text-xs font-medium text-slate-500">Pratinjau foto</span>
            </div>

            <div class="min-w-0 flex-1">
                <form method="POST" action="{{ route('portal.profil.foto.update') }}" enctype="multipart/form-data">
                    @csrf

                    <label for="foto" class="mb-2 block text-sm font-semibold text-slate-800">Pilih foto baru</label>
                    <input id="foto" name="foto" type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required
                        aria-describedby="bantuan-foto"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-600 transition file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-800 hover:file:bg-emerald-200">
                    <p id="bantuan-foto" class="mt-2 text-xs leading-5 text-slate-500">Format JPG, PNG, atau WebP. Ukuran
                        maksimal 2 MB.</p>

                    @error('foto')
                        <p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                        class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/40 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Foto
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('foto');
            const preview = document.getElementById('preview-foto-profil');

            if (!input || !preview) return;

            let previewUrl = null;

            input.addEventListener('change', function() {
                const file = input.files && input.files[0];

                if (!file) return;

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (
                    !allowedTypes.includes(file.type) ||
                    file.size > 2 * 1024 * 1024
                ) {
                    input.value = '';
                    alert('Pilih gambar JPG, PNG, atau WebP dengan ukuran maksimal 2 MB.');
                    return;
                }

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }

                previewUrl = URL.createObjectURL(file);
                preview.src = previewUrl;
            });
        });
    </script>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Kolom Kiri: Form -->
        <div class="space-y-6 xl:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-white px-5 py-5 sm:px-7">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Formulir Pembaruan Data</h2>
                            <p class="mt-0.5 text-sm text-slate-500">Lengkapi informasi kontak terbaru Anda.</p>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('portal.profil.update') }}" class="p-5 sm:p-7">
                    @csrf
                    @method('patch')

                    <div class="space-y-6">
                        <!-- Input: Nomor Telepon -->
                        <div>
                            <label for="telepon" class="mb-2 block text-sm font-semibold text-slate-800">Nomor
                                Telepon</label>
                            <input id="telepon" type="tel" name="telepon" maxlength="25" autocomplete="tel"
                                inputmode="tel" value="{{ old('telepon', $user->telepon) }}"
                                aria-describedby="bantuan-telepon"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10 @error('telepon') border-rose-500 focus:border-rose-500 focus:ring-rose-500/10 @enderror">

                            @error('telepon')
                                <p class="mt-2 text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                            @else
                                <p id="bantuan-telepon" class="mt-2 text-xs leading-5 text-slate-500">Contoh: 0812-3456-7890
                                    atau +62 812 3456 7890.</p>
                            @enderror
                        </div>

                        <!-- Input: Alamat -->
                        <div>
                            <label for="alamat" class="mb-2 block text-sm font-semibold text-slate-800">Alamat
                                Lengkap</label>
                            <textarea id="alamat" name="alamat" rows="5" maxlength="1000" autocomplete="street-address"
                                aria-describedby="bantuan-alamat"
                                class="w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-700 focus:bg-white focus:ring-4 focus:ring-emerald-700/10 @error('alamat') border-rose-500 focus:border-rose-500 focus:ring-rose-500/10 @enderror">{{ old('alamat', $mahasiswa->alamat) }}</textarea>

                            @error('alamat')
                                <p class="mt-2 text-xs font-medium text-rose-600" role="alert">{{ $message }}</p>
                            @else
                                <p id="bantuan-alamat" class="mt-2 text-xs leading-5 text-slate-500">Masukkan alamat tempat
                                    tinggal yang dapat digunakan untuk kebutuhan administrasi kampus.</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center">
                        <a href="{{ route('portal.profil.show') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/40 focus:ring-offset-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Aside -->
        <aside class="space-y-6">
            <!-- Kartu: Info Readonly -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 11a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-slate-800">Identitas Terkunci</h2>
                    </div>
                </div>
                <div class="p-5 sm:p-6">
                    <dl class="space-y-5">
                        <div>
                            <dt class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Lengkap
                            </dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $user->nama }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500">NIM</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $mahasiswa->nim }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500">Email Akun</dt>
                            <dd class="break-words text-sm font-semibold text-slate-900">{{ $user->email ?: '—' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs leading-5 text-slate-600">
                        Nama, NIM, email, dan data akademik lain tidak dapat diperbarui melalui formulir ini.
                    </div>
                </div>
            </div>

            <!-- Kartu: Keterangan Keamanan -->
            <div class="rounded-2xl border border-amber-200/80 bg-amber-50 p-5 shadow-sm">
                <div class="flex gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0 text-sm text-amber-900">
                        <p class="font-bold">Keamanan Data</p>
                        <p class="mt-1.5 leading-6">Sistem hanya menerima pembaruan untuk form di samping. Modifikasi atau
                            manipulasi kode formulir secara paksa untuk mengubah data akademik akan ditolak secara otomatis
                            oleh sistem.</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
