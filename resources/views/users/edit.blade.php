@extends('layouts.admin')

@section('title', 'Edit Pengguna')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Pengguna: {{ $user->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi akun, data login, dan hak akses pengguna.</p>
        </div>
        <a href="{{ route('admin.users.show', $user) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
            &larr; Kembali ke Detail
        </a>
    </div>

    <!-- FORM CONTAINER CARD -->
    <div class="rounded-xl bg-white p-6 sm:p-8 shadow-sm border border-slate-200">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PATCH')

            @include('users._form', ['version' => $version])

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-200">
                <a href="{{ route('admin.users.show', $user) }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
