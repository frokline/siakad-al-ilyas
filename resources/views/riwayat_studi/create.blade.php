@extends('layouts.admin')

@section('title', 'Tambah Riwayat Studi')

@section('content')
    @php
        $siap = $daftarMahasiswa->isNotEmpty() && $daftarKurikulum->isNotEmpty() && $daftarPeriode->isNotEmpty();
        $tautanPeringatan = 'mt-1 inline-block font-semibold underline';
    @endphp

    {{-- Judul halaman --}}
    <div class="mb-6">
        <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('admin.riwayat-studi.index') }}" class="hover:text-siakad-dark">Riwayat Studi</a>
            <span aria-hidden="true">/</span>
            <span class="font-medium text-slate-700">Tambah</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Tambah Riwayat Studi</h1>
        <p class="mt-1 text-sm text-slate-500">Riwayat baru langsung berstatus aktif.</p>
    </div>

    {{-- Peringatan prasyarat data --}}
    <div class="space-y-3">
        @if ($daftarMahasiswa->isEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                <p class="font-semibold">Belum ada mahasiswa yang memenuhi syarat.</p>
                <p>Periksa akun, peran Mahasiswa, dan riwayat aktif sebelumnya.</p>
                @if (Route::has('admin.mahasiswa.index'))
                    <a href="{{ route('admin.mahasiswa.index') }}" class="{{ $tautanPeringatan }}">Data mahasiswa →</a>
                @endif
            </div>
        @endif
        @if ($daftarKurikulum->isEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                <p class="font-semibold">Belum ada kurikulum aktif pada program studi aktif.</p>
                @if (Route::has('admin.kurikulum.index'))
                    <a href="{{ route('admin.kurikulum.index') }}" class="{{ $tautanPeringatan }}">Data kurikulum →</a>
                @endif
            </div>
        @endif
        @if ($daftarPeriode->isEmpty())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                <p class="font-semibold">Buat periode akademik terlebih dahulu.</p>
                @if (Route::has('admin.periode-akademik.index'))
                    <a href="{{ route('admin.periode-akademik.index') }}" class="{{ $tautanPeringatan }}">Data periode
                        akademik →</a>
                @endif
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.riwayat-studi.store') }}" class="mt-6 grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="space-y-6 lg:col-span-2">
            @include('riwayat_studi._form')

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.riwayat-studi.index') }}"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-50">Batal</a>
                <button type="submit" @disabled(!$siap)
                    class="rounded-xl bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-siakad-active disabled:cursor-not-allowed disabled:opacity-50">
                    Simpan Riwayat
                </button>
            </div>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Sebelum menyimpan</h2>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-emerald-50">
                    <li class="flex gap-2"><span aria-hidden="true">•</span>Satu mahasiswa hanya boleh punya satu riwayat
                        aktif.</li>
                    <li class="flex gap-2"><span aria-hidden="true">•</span>Mahasiswa, kurikulum, angkatan, dan periode
                        mulai tidak dapat diubah setelah disimpan.</li>
                    <li class="flex gap-2"><span aria-hidden="true">•</span>Riwayat tidak dapat dihapus; gunakan
                        penutupan (selesai, pindah, atau keluar).</li>
                    <li class="flex gap-2"><span aria-hidden="true">•</span>Pindah program dicatat sebagai riwayat baru
                        setelah riwayat lama ditutup.</li>
                </ul>
            </section>
        </aside>
    </form>
@endsection
