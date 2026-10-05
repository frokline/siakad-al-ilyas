@extends('layouts.admin')
@section('title', 'Pengajar Kelas')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Pengajar Kelas</h1>
            <p class="text-sm text-slate-500 mt-1">Penugasan dosen, koordinator, dan riwayat tim pengajar.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 self-start"
            href="{{ route('admin.pengajar-kelas.create') }}">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah penugasan
        </a>
    </div>

    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6 w-full">
        @if (!empty($filter['kelas_id']) || !empty($filter['dosen_id']))
            <div
                class="mb-4 rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600 flex items-center justify-between">
                <span>
                    @if (!empty($filter['kelas_id']))
                        Kelas #{{ $filter['kelas_id'] }}.
                    @endif
                    @if (!empty($filter['dosen_id']))
                        Dosen #{{ $filter['dosen_id'] }}.
                    @endif
                    Gunakan Reset untuk menampilkan seluruh penugasan.
                </span>
            </div>
        @endif
        <form class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end" method="GET"
            action="{{ route('admin.pengajar-kelas.index') }}">
            @foreach (['kelas_id', 'dosen_id'] as $kunci)
                @if (!empty($filter[$kunci]))
                    <input type="hidden" name="{{ $kunci }}" value="{{ $filter[$kunci] }}">
                @endif
            @endforeach
            <div class="sm:col-span-3 w-full">
                <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Dosen
                    atau Kelas</label>
                <input id="q" name="q" type="search" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    placeholder="Kode, nama dosen, mata kuliah"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>
            <div class="sm:col-span-3 w-full">
                <label for="periode_id"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode</label>
                <select id="periode_id" name="periode_id"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua periode</option>
                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 w-full">
                <label for="peran"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Peran</label>
                <select id="peran" name="peran"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua peran</option>
                    @foreach (\App\Models\PengajarKelas::PERAN as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['peran'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 w-full">
                <label for="aktif"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Penugasan</label>
                <select id="aktif" name="aktif"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    <option value="1" @selected(($filter['aktif'] ?? '') === '1')>Aktif</option>
                    <option value="0" @selected(($filter['aktif'] ?? '') === '0')>Nonaktif</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex gap-2 w-full">
                <button
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center shadow-sm"
                    type="submit">Tampilkan</button>
                <a class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center shadow-sm"
                    href="{{ route('admin.pengajar-kelas.index') }}">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Penugasan Pengajar</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ $daftarPenugasan->total() }} penugasan ditemukan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Dosen</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas / Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Periode / Rombel</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Peran / Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarPenugasan as $penugasan)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <strong class="font-bold text-slate-800 block"><span
                                        class="font-mono">{{ $penugasan->dosen->kode_dosen }}</span></strong>
                                <span
                                    class="text-xs text-slate-500 block mt-0.5">{{ $penugasan->dosen->user->nama }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="font-mono font-bold text-slate-800 text-xs block">{{ $penugasan->kelasKuliah->kode }}</span>
                                <span
                                    class="text-slate-700 block mt-0.5">{{ $penugasan->kelasKuliah->nama_mk_snapshot }}</span>
                                <div class="text-xs font-semibold text-slate-500 mt-0.5">
                                    {{ \App\Models\KelasKuliah::STATUS[$penugasan->kelasKuliah->status] }}</div>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">
                                {{ $penugasan->kelasKuliah->rombel->periodeAkademik->kode }} <br>
                                <span
                                    class="text-xs text-slate-500 font-normal">{{ $penugasan->kelasKuliah->rombel->kode }}</span>
                            </td>
                            <td class="px-6 py-4 text-center space-x-1">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $penugasan->peran === 'koordinator' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ \App\Models\PengajarKelas::PERAN[$penugasan->peran] }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $penugasan->aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' }}">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full {{ $penugasan->aktif ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $penugasan->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.pengajar-kelas.show', $penugasan) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        title="Detail penugasan #{{ $penugasan->id }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if ($penugasan->dapatDiubah())
                                        <a href="{{ route('admin.pengajar-kelas.edit', $penugasan) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                            title="Edit penugasan #{{ $penugasan->id }}">
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
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Belum ada penugasan sesuai pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            <x-pagination :paginator="$daftarPenugasan" />
        </div>
    </div>
@endsection
