@extends('layouts.admin')

@section('title', 'Kelas Kuliah')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Kelas Kuliah</h1>
            <p class="text-sm text-slate-500 mt-1">Penawaran mata kuliah pada rombel dan periode akademik.</p>
        </div>

        <a href="{{ route('admin.kelas-kuliah.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 self-start">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kelas
        </a>
    </div>

    @if ($rombelFilter !== null)
        <div
            class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center justify-between shadow-sm">
            <span>Menampilkan kelas rombel <strong>{{ $rombelFilter->kode }}</strong>.</span>
            <a href="{{ route('admin.kelas-kuliah.index', \Illuminate\Support\Arr::except($filter, ['rombel_id'])) }}"
                class="font-semibold underline hover:text-amber-900">
                Hapus filter rombel
            </a>
        </div>
    @endif

    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6 w-full">
        <form method="GET" action="{{ route('admin.kelas-kuliah.index') }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            @if ($rombelFilter !== null)
                <input type="hidden" name="rombel_id" value="{{ $rombelFilter->id }}">
            @endif

            <div class="sm:col-span-3 w-full">
                <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Cari
                    Kelas</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="q" name="q" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Kode, mata kuliah, rombel..." maxlength="100"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="periode_akademik_id"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode</label>
                <select id="periode_akademik_id" name="periode_akademik_id"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua periode</option>
                    @foreach ($periodePilihan as $periode)
                        <option value="{{ $periode->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $periode->id)>
                            {{ $periode->kode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="program_studi_id"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Program Studi</label>
                <select id="program_studi_id" name="program_studi_id"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua program studi</option>
                    @foreach ($prodiPilihan as $prodi)
                        <option value="{{ $prodi->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $prodi->id)>
                            {{ $prodi->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 w-full grid grid-cols-2 gap-2">
                <div>
                    <label for="semester_studi"
                        class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Semester</label>
                    <input type="number" id="semester_studi" name="semester_studi" min="1" max="32767"
                        value="{{ $filter['semester_studi'] ?? '' }}" placeholder="Semua"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                </div>
                <div>
                    <label for="status"
                        class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status</label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                        <option value="">Status</option>
                        @foreach (\App\Models\KelasKuliah::STATUS as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="sm:col-span-12 flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="submit"
                    class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors shadow-sm">
                    Tampilkan
                </button>
                <a href="{{ route('admin.kelas-kuliah.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center shadow-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Kelas</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($kelasDaftar->total(), 0, ',', '.') }} kelas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas / Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Rombel</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Periode</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">SKS</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kelasDaftar as $kelas)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong
                                    class="font-bold text-slate-800 font-mono text-xs group-hover:text-siakad-dark transition-colors block">{{ $kelas->kode }}</strong>
                                <div class="text-slate-700 mt-0.5">{{ $kelas->nama_mk_snapshot }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $kelas->rombel->kode }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $kelas->rombel->paketSemester->kurikulum->programStudi->nama }} &middot; Semester
                                    {{ $kelas->rombel->paketSemester->semester_studi }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">
                                {{ $kelas->rombel->periodeAkademik->kode }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700">
                                {{ str_replace('.', ',', $kelas->sks_snapshot) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusBadgeClass = match ($kelas->status) {
                                        'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'persiapan' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'selesai' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $dotClass = match ($kelas->status) {
                                        'aktif' => 'bg-emerald-500',
                                        'persiapan' => 'bg-amber-500',
                                        'selesai' => 'bg-blue-500',
                                        default => 'bg-slate-400',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.kelas-kuliah.show', $kelas) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        title="Detail kelas {{ $kelas->kode }} rombel {{ $kelas->rombel->kode }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if ($kelas->dapatDiubah())
                                        <a href="{{ route('admin.kelas-kuliah.edit', $kelas) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                            title="Edit kelas {{ $kelas->kode }} rombel {{ $kelas->rombel->kode }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div
                                    class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 mb-4 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Tidak ada kelas yang sesuai pencarian</p>
                                <p class="mt-1 text-xs text-slate-500">Sesuaikan kata kunci pencarian atau filter Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            <x-pagination :paginator="$kelasDaftar" />
        </div>
    </div>
@endsection
