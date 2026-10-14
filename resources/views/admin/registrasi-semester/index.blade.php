@extends('layouts.admin')

@section('title', 'Registrasi Semester')

@section('content')
    @php
        $adaFilter = collect($filter)->filter(fn($nilai) => $nilai !== null && $nilai !== '')->isNotEmpty();
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500';
    @endphp

    {{-- Header halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
                @if (Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-siakad-dark">Dashboard</a>
                    <span aria-hidden="true">/</span>
                @endif
                <span class="font-medium text-slate-700">Registrasi Semester</span>
            </nav>

            <h1 class="text-2xl font-bold text-slate-900">
                Registrasi Semester
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Penempatan mahasiswa pada rombel dan periode akademik.
            </p>
        </div>

        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <a href="{{ route(
                'admin.registrasi-semester.export',
                request()->only(['q', 'periode_akademik_id', 'program_studi_id', 'semester_studi', 'status']),
            ) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                    border border-emerald-700 bg-white px-5 py-2.5 text-sm
                    font-semibold text-emerald-800 transition hover:bg-emerald-50
                    focus:outline-none focus:ring-2 focus:ring-emerald-700/30
                    sm:w-auto">

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12-4-4m4 4 4-4M5 20h14" />
                </svg>

                Ekspor Excel
            </a>

            <a href="{{ route('admin.registrasi-semester.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                    bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white
                    shadow-sm transition hover:bg-siakad-active
                    focus:outline-none focus:ring-2 focus:ring-siakad-dark/40
                    sm:w-auto">

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>

                Tambah Registrasi
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.registrasi-semester.index') }}"
        class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <label for="q" class="{{ $label }}">Cari mahasiswa / rombel</label>
                <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    placeholder="NIM, nama, kode rombel" class="{{ $input }}">
                @error('q')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="lg:col-span-2">
                <label for="periode_akademik_id" class="{{ $label }}">Periode</label>
                <select id="periode_akademik_id" name="periode_akademik_id" class="{{ $input }}">
                    <option value="">Semua periode</option>
                    @foreach ($periodePilihan as $periode)
                        <option value="{{ $periode->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $periode->id)>
                            {{ $periode->kode }}</option>
                    @endforeach
                </select>
                @error('periode_akademik_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="lg:col-span-3">
                <label for="program_studi_id" class="{{ $label }}">Program studi</label>
                <select id="program_studi_id" name="program_studi_id" class="{{ $input }}">
                    <option value="">Semua program studi</option>
                    @foreach ($prodiPilihan as $prodi)
                        <option value="{{ $prodi->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $prodi->id)>
                            {{ $prodi->nama }}</option>
                    @endforeach
                </select>
                @error('program_studi_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="lg:col-span-2">
                <label for="semester_studi" class="{{ $label }}">Semester</label>
                <input type="number" id="semester_studi" name="semester_studi" min="1" max="32767"
                    value="{{ $filter['semester_studi'] ?? '' }}" placeholder="Semua" class="{{ $input }}">
                @error('semester_studi')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="lg:col-span-2">
                <label for="status" class="{{ $label }}">Status</label>
                <select id="status" name="status" class="{{ $input }}">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\RegistrasiSemester::STATUS as $kode => $labelStatus)
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $labelStatus }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2 text-sm font-semibold text-white transition hover:bg-siakad-active">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Tampilkan
            </button>
            @if ($adaFilter)
                <a href="{{ route('admin.registrasi-semester.index') }}"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Reset</a>
                <span class="text-xs text-slate-500">Filter sedang aktif.</span>
            @endif
        </div>
    </form>

    {{-- Tabel --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div
            class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Daftar registrasi</h2>
            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                {{ number_format($registrasiDaftar->total(), 0, ',', '.') }} data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-3">Mahasiswa</th>
                        <th scope="col" class="px-4 py-3">Program studi</th>
                        <th scope="col" class="px-4 py-3">Periode</th>
                        <th scope="col" class="px-4 py-3">Rombel</th>
                        <th scope="col" class="px-4 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($registrasiDaftar as $registrasi)
                        <tr class="transition hover:bg-emerald-50/40">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-siakad-dark">
                                        {{ strtoupper(mb_substr($registrasi->riwayatStudi->mahasiswa->user->nama ?? '?', 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900">
                                            {{ $registrasi->riwayatStudi->mahasiswa->user->nama }}</p>
                                        <p class="text-xs text-slate-500">{{ $registrasi->riwayatStudi->mahasiswa->nim }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-800">
                                {{ $registrasi->riwayatStudi->kurikulum->programStudi->nama }}</td>
                            <td class="whitespace-nowrap px-4 py-4 font-medium text-slate-800">
                                {{ $registrasi->periodeAkademik->kode }}</td>
                            <td class="px-4 py-4">
                                <p class="font-medium text-slate-800">{{ $registrasi->rombel->kode }}</p>
                                <p class="text-xs text-slate-500">Semester {{ $registrasi->semester_studi }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @include('admin.registrasi-semester._lencana', [
                                    'status' => $registrasi->status,
                                ])
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.registrasi-semester.show', $registrasi) }}"
                                        aria-label="Detail registrasi {{ $registrasi->riwayatStudi->mahasiswa->nim }}"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-siakad-dark hover:text-siakad-dark">Detail</a>
                                    @if ($registrasi->dapatDiubah())
                                        <a href="{{ route('admin.registrasi-semester.edit', $registrasi) }}"
                                            aria-label="Edit registrasi {{ $registrasi->riwayatStudi->mahasiswa->nim }}"
                                            class="rounded-lg bg-siakad-dark px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-siakad-active">Edit</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div
                                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="mt-3 font-semibold text-slate-700">Tidak ada registrasi yang sesuai.</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $adaFilter ? 'Coba ubah atau reset filter pencarian.' : 'Belum ada registrasi semester yang tercatat.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registrasiDaftar->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $registrasiDaftar->links('admin.registrasi-semester._pagination') }}
            </div>
        @endif
    </section>
@endsection
