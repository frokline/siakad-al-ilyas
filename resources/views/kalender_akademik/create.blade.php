@extends('layouts.admin')

@section('title', 'Tambah Agenda')

@section('content')
    @include('kalender_akademik._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('kalender.index') }}" class="hover:text-siakad-active">Kalender akademik</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Tambah agenda</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Tambah agenda akademik</h1>
            <p class="mt-1 text-sm text-slate-500">Agenda baru tersimpan sebagai draf dan baru terlihat pengguna setelah
                diterbitkan.</p>
        </div>
        <a href="{{ route('kalender.index') }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Kembali</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir agenda</h2>
            </div>
            <form class="p-6" method="post" action="{{ route('kalender.store') }}">
                @include('kalender_akademik._form', ['baru' => true])
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Alur agenda</h2>
                <ol class="mt-4 space-y-3 text-sm">
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-siakad-accent text-xs font-bold text-siakad-dark">1</span>Simpan
                        sebagai <strong>draf</strong></li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-siakad-accent text-xs font-bold text-siakad-dark">2</span>Periksa
                        tanggal dan jam</li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-siakad-accent text-xs font-bold text-siakad-dark">3</span><strong>Terbitkan</strong>
                        agar terlihat dosen dan mahasiswa</li>
                </ol>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider">Perlu diketahui</h2>
                <p class="mt-2">Agenda berjenis <strong>KRS</strong> hanya informasi kalender. Batas operasional KRS
                    dikelola pada Periode Akademik.</p>
            </div>
        </aside>
    </div>
@endsection
