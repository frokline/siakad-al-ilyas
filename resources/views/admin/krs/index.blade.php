@extends('layouts.admin')

@section('title', 'Kartu Rencana Studi')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Kartu Rencana Studi</h1>
            <p class="text-sm text-slate-500 mt-1">KRS paket semester dan pengesahan oleh admin akademik.</p>
        </div>

        <a href="{{ route('admin.krs.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 self-start">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat KRS
        </a>
    </div>

    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6 w-full">
        <form method="GET" action="{{ route('admin.krs.index') }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">

            <div class="sm:col-span-3 w-full">
                <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">NIM atau
                    Nama</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="q" name="q" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Cari mahasiswa..." maxlength="100"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="periode_id"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode</label>
                <select name="periode_id" id="periode_id"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua periode</option>
                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>
                            {{ $periode->kode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="program_studi_id"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Program Studi</label>
                <select name="program_studi_id" id="program_studi_id"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua program studi</option>
                    @foreach ($daftarProdi as $prodi)
                        <option value="{{ $prodi->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $prodi->id)>
                            {{ $prodi->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 w-full grid grid-cols-2 gap-2">
                <div>
                    <label for="status"
                        class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status KRS</label>
                    <select name="status" id="status"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                        <option value="">Semua</option>
                        @foreach (\App\Models\Krs::STATUS as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-slate-800 px-3 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors shadow-sm text-center">
                        Cari
                    </button>
                    <a href="{{ route('admin.krs.index') }}"
                        class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm text-center">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Kartu Rencana Studi</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftarKrs->total(), 0, ',', '.') }} KRS ditemukan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Mahasiswa</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Periode / Rombel</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Paket</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarKrs as $krs)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong
                                    class="font-bold font-mono text-slate-800 block text-xs group-hover:text-siakad-dark transition-colors">{{ $krs->registrasiSemester->riwayatStudi->mahasiswa->nim }}</strong>
                                <div class="text-slate-700 font-medium mt-0.5">
                                    {{ $krs->registrasiSemester->riwayatStudi->mahasiswa->user->nama }}</div>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $krs->registrasiSemester->riwayatStudi->kurikulum->programStudi->nama }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $krs->registrasiSemester->periodeAkademik->kode }}
                                </div>
                                <div class="text-xs font-mono text-slate-500 mt-0.5">
                                    {{ $krs->registrasiSemester->rombel->kode }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700">Semester
                                    {{ $krs->registrasiSemester->semester_studi }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $krs->details->count() }} mata kuliah &middot;
                                    {{ str_replace('.', ',', $krs->totalSks()) }} SKS
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusBadgeClass = match ($krs->status) {
                                        'disahkan' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'diajukan' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'draf' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-100',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $dotClass = match ($krs->status) {
                                        'disahkan' => 'bg-emerald-500',
                                        'diajukan' => 'bg-amber-500',
                                        'draf' => 'bg-blue-500',
                                        'dibatalkan' => 'bg-rose-500',
                                        default => 'bg-slate-400',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold uppercase tracking-wider {{ $statusBadgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ \App\Models\Krs::STATUS[$krs->status] }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Versi
                                    {{ $krs->versi }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.krs.show', $krs) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ $krs->id }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 mb-4 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Belum ada KRS sesuai pencarian</p>
                                <p class="mt-1 text-xs text-slate-500">Sesuaikan kata kunci pencarian atau filter Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            <x-pagination :paginator="$daftarKrs" />
        </div>
    </div>
@endsection
