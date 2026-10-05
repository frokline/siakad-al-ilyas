@extends('layouts.admin')

@section('title', 'Detail Pengguna')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $user->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">Detail informasi akun dan hak akses pengguna sistem.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.edit', $user) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors">
                Edit Pengguna
            </a>
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Akun</h2>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</p>
                <p class="font-semibold text-slate-800 text-base">{{ $user->nama }}</p>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Username Sistem</p>
                <p class="font-mono text-slate-800 bg-slate-100 inline-block px-2 py-0.5 rounded border border-slate-200">
                    {{ $user->username }}</p>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat Email</p>
                <p class="text-slate-800">{{ $user->email }}</p>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Telepon</p>
                <p class="text-slate-800">{{ $user->telepon ?: '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Status Akun</p>
                <div class="mt-1">
                    @if ($user->isAktif())
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Nonaktif
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Peran Akses (Roles)</p>
                <div class="flex flex-wrap gap-1.5 mt-1">
                    @forelse($user->roles as $role)
                        <span
                            class="inline-flex items-center rounded bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            {{ $role->nama }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 italic">Belum ditetapkan</span>
                    @endforelse
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Dibuat</p>
                <p class="text-slate-600">{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Terakhir Diperbarui</p>
                <p class="text-slate-600">{{ $user->updated_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>

    <!-- NONAKTIFKAN AKUN PANEL (Jika aktif dan bukan akun sendiri) -->
    @if ($user->isAktif() && !$user->is(auth('web')->user()))
        <div class="rounded-xl bg-white shadow-sm border border-rose-200 overflow-hidden">
            <div class="border-b border-rose-100 bg-rose-50 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-rose-900">Zona Berbahaya: Nonaktifkan Akun</h2>
                    <p class="text-xs text-rose-600 mt-0.5">Akses login akun akan dicabut, namun data riwayat tetap
                        tersimpan aman di database.</p>
                </div>
            </div>

            <div class="p-6">
                @error('version')
                    <div class="mb-4 p-4 rounded-lg bg-rose-50 border border-rose-200 text-sm text-rose-700">
                        {{ $message }}
                        <a href="{{ route('admin.users.show', $user) }}" class="underline font-bold ml-1">Muat ulang data
                            terbaru</a>
                    </div>
                @enderror

                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="max-w-xl">
                    @csrf
                    @method('DELETE')

                    <input type="hidden" name="version" value="{{ $version }}">

                    <div class="mb-4">
                        <label for="current_password"
                            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Konfirmasi Kata Sandi Admin Anda <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password" maxlength="72"
                            autocomplete="current-password" required placeholder="Masukkan kata sandi akun admin Anda..."
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:bg-white @error('current_password') border-rose-500 bg-rose-50/50 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors">
                        Nonaktifkan Akun Ini
                    </button>
                </form>
            </div>
        </div>
    @endif
@endsection
