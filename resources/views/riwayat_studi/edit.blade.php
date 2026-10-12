@extends('layouts.admin')

@section('title', 'Edit Riwayat Studi')

@section('content')
    {{-- Judul halaman --}}
    <div class="mb-6">
        <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('admin.riwayat-studi.index') }}" class="hover:text-siakad-dark">Riwayat Studi</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('admin.riwayat-studi.show', $riwayatStudi) }}" class="hover:text-siakad-dark">Detail</a>
            <span aria-hidden="true">/</span>
            <span class="font-medium text-slate-700">Edit</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Edit Riwayat Studi</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $riwayatStudi->mahasiswa->nim }} —
            {{ $riwayatStudi->mahasiswa->user->nama }}</p>
    </div>

    <form method="POST" action="{{ route('admin.riwayat-studi.update', $riwayatStudi) }}"
        class="grid gap-6 lg:grid-cols-3">
        @csrf
        @method('PATCH')

        <div class="space-y-6 lg:col-span-2">
            @include('riwayat_studi._form')

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.riwayat-studi.show', $riwayatStudi) }}"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-50">Batal</a>
                <button type="submit"
                    class="rounded-xl bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-siakad-active">
                    Simpan Perubahan
                </button>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Yang bisa diubah</h2>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-emerald-50">
                    <li class="flex gap-2"><span aria-hidden="true">•</span>Dosen pembimbing akademik.</li>
                    <li class="flex gap-2"><span aria-hidden="true">•</span>Status dan periode akhir, untuk menutup
                        riwayat.</li>
                </ul>
                <p class="mt-4 border-t border-white/15 pt-4 text-xs leading-5 text-emerald-100">
                    Riwayat yang sudah ditutup bersifat final dan tidak dapat dibuka atau diedit kembali.
                </p>
            </section>
        </aside>
    </form>
@endsection
