@extends('layouts.admin')
@section('title', 'Edit Catatan KRS')

@section('content')
    @php
        $bolehSimpan =
            $registrasi->status === \App\Models\RegistrasiSemester::AKTIF &&
            $registrasi->riwayatStudi->status === \App\Models\RiwayatStudi::AKTIF &&
            $registrasi->periodeAkademik->isKrsOpen();
    @endphp
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Catatan KRS #{{ $krs->id }}</h1>
            <p class="text-sm text-slate-500 mt-1">Draf versi {{ $krs->versi }}. Paket dan rombel mengikuti registrasi.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.krs.show', $krs) }}">Batal / Detail KRS</a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Identitas & Rincian Mata Kuliah</h2>
        </div>
        <div class="p-6">@include('admin.krs._identitas')</div>
        @include('admin.krs._mata_kuliah')
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Formulir Catatan Draf KRS</h2>
        </div>
        <div class="p-6">
            @unless ($bolehSimpan)
                <div
                    class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-start gap-3">
                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>Pengubahan catatan memerlukan registrasi mahasiswa berstatus aktif, riwayat studi aktif, serta jadwal
                        masa pengisian KRS pada periode ini harus sedang terbuka.</div>
                </div>
            @endunless
            <form method="POST" action="{{ route('admin.krs.update', $krs) }}">
                @include('admin.krs._form')
            </form>
        </div>
    </div>
@endsection
