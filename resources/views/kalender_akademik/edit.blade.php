@extends('layouts.admin')

@section('title', 'Edit Agenda')

@section('content')
    @include('kalender_akademik._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('kalender.index') }}" class="hover:text-siakad-active">Kalender akademik</a>
                <span>/</span>
                <a href="{{ route('kalender.show', $agenda) }}"
                    class="max-w-[14rem] truncate hover:text-siakad-active">{{ $agenda->judul }}</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Edit</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Edit agenda</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @include('kalender_akademik._lencana', ['tipe' => 'status', 'nilai' => $agenda->status])
                @include('kalender_akademik._lencana', ['tipe' => 'jenis', 'nilai' => $agenda->jenis])
            </div>
        </div>
        <a href="{{ route('kalender.show', $agenda) }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Kembali</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir agenda</h2>
            </div>
            <form class="p-6" method="post" action="{{ route('kalender.update', $agenda) }}">
                @include('kalender_akademik._form', ['baru' => false])
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider">Periksa sebelum menyimpan</h2>
                <p class="mt-2">Periksa tanggal dan jam. Agenda kalender tidak otomatis mengubah jadwal kelas,
                    presensi, atau batas KRS.</p>
            </div>
            @if ($agenda->diterbitkan_at)
                <div class="rounded-2xl bg-siakad-dark p-6 text-sm text-white shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Agenda sudah terbit</h2>
                    <p class="mt-2">Perubahan judul, isi, atau waktu langsung terlihat oleh pengguna.</p>
                </div>
            @endif
        </aside>
    </div>
@endsection
