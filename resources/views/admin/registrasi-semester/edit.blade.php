@extends('layouts.admin')

@section('title', 'Edit Registrasi Semester')

@section('content')
    @php
        $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
        $kepala = 'rounded-t-2xl border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
        $judul = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
        $kapasitas = $registrasi->rombel->kapasitas;
        $terpakai = (int) $registrasi->rombel->kursi_terpakai;
    @endphp

    <div class="mb-6">
        <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('admin.registrasi-semester.index') }}" class="hover:text-siakad-dark">Registrasi Semester</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('admin.registrasi-semester.show', $registrasi) }}" class="hover:text-siakad-dark">Detail</a>
            <span aria-hidden="true">/</span>
            <span class="font-medium text-slate-700">Edit</span>
        </nav>
        <h1 class="text-2xl font-bold text-slate-900">Edit Registrasi Semester</h1>
        <p class="mt-1 text-sm text-slate-500">Periode {{ $registrasi->periodeAkademik->kode }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="{{ $kartu }}">
                <div class="{{ $kepala }}">
                    <h2 class="{{ $judul }}">Mahasiswa</h2>
                </div>
                <div class="p-6">@include('admin.registrasi-semester._identitas')</div>
            </section>

            <section class="{{ $kartu }}">
                <div class="{{ $kepala }}">
                    <h2 class="{{ $judul }}">Penempatan dan status</h2>
                </div>
                <form method="POST" action="{{ route('admin.registrasi-semester.update', $registrasi) }}" class="p-6">
                    @include('admin.registrasi-semester._form')
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Kondisi saat ini</p>
                <div class="mt-3">@include('admin.registrasi-semester._lencana', ['status' => $registrasi->status])</div>
                <dl class="mt-4 space-y-2 text-sm text-emerald-50">
                    <div class="flex justify-between gap-3">
                        <dt>Rombel</dt>
                        <dd class="font-semibold">{{ $registrasi->rombel->kode }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt>Semester</dt>
                        <dd class="font-semibold">{{ $registrasi->semester_studi }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt>Kursi</dt>
                        <dd class="font-semibold">{{ $terpakai }} / {{ $kapasitas ?? '∞' }}</dd>
                    </div>
                </dl>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-600 shadow-sm">
                <p class="font-semibold text-slate-800">Aturan singkat</p>
                <ul class="mt-2 space-y-1.5">
                    <li>• Mahasiswa dan periode tidak dapat diganti.</li>
                    <li>• Registrasi tidak dihapus; gunakan status Batal.</li>
                    <li>• Cuti dan Batal melepas kursi rombel.</li>
                </ul>
            </section>
        </aside>
    </div>
@endsection
