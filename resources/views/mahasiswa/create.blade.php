@extends('layouts.admin')

@section('title', 'Tambah Mahasiswa')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Tambah Mahasiswa</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih akun pengguna, kemudian lengkapi biodatanya.</p>
        </div>

        <a href="{{ route('admin.mahasiswa.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors self-start shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    @if ($akun === null)
        @if (!empty($filter['user_id']))
            <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
                Akun yang dipilih sudah tidak tersedia untuk pendaftaran mahasiswa. Silakan pilih akun lain.
            </div>
        @endif

        @error('user_id')
            <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
                {{ $message }}
            </div>
        @enderror

        <!-- FILTER CARD -->
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6">
            <p class="text-xs text-slate-500 mb-4">
                Daftar ini menampilkan akun aktif berperan mahasiswa yang belum mempunyai profil mahasiswa.
            </p>

            <form method="GET" action="{{ route('admin.mahasiswa.create') }}"
                class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-9 w-full">
                    <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Cari
                        Akun Pengguna</label>
                    <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Nama, email, atau username"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                </div>

                <div class="sm:col-span-3 flex gap-2 w-full">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center">
                        Cari
                    </button>
                    <a href="{{ route('admin.mahasiswa.create') }}"
                        class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                        Reset
                    </a>
                </div>
            </form>

            @can('kelola-pengguna')
                <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
                    Belum ada akun? <a href="{{ route('admin.users.create') }}"
                        class="text-siakad-dark font-semibold hover:underline">Buat pengguna dengan peran mahasiswa.</a>
                </div>
            @endcan
        </div>

        <!-- ACCOUNT SELECTION TABLE -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Pilih Akun Pengguna</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Nama</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Username</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($akunDaftar as $pilihan)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $pilihan->nama }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ $pilihan->username }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $pilihan->email }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.mahasiswa.create', ['user_id' => $pilihan->id]) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-siakad-dark px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm"
                                        aria-label="Pilih akun {{ $pilihan->nama }}">
                                        Pilih Akun
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    Tidak ada akun yang memenuhi pilihan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                @include('mahasiswa._pagination', ['paginator' => $akunDaftar])
            </div>
        </div>
    @else
        <!-- FORM CARD -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800">Biodata Mahasiswa</h2>
                <a href="{{ route('admin.mahasiswa.create') }}"
                    class="text-xs font-semibold text-siakad-dark hover:underline">
                    Ganti pilihan akun
                </a>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.mahasiswa.store') }}">
                    @csrf
                    @include('mahasiswa._form')
                </form>
            </div>
        </div>
    @endif
@endsection
