@extends('layouts.admin')
@section('title', 'Daftar Pertemuan Presensi')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Presensi Perkuliahan</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih pertemuan yang akan dicatat. Zona waktu:
                <strong>{{ $zona }}</strong>.
            </p>
            <p
                class="mt-2 inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">
                {{ auth()->user()?->hasRole(\App\Models\Role::ADMIN_AKADEMIK) ? 'Admin Akademik: seluruh kelas' : 'Dosen: hanya kelas yang Anda ampu' }}
            </p>
        </div>
        @if (Route::has('portal.dosen.kelas.index'))
            <a href="{{ route('portal.dosen.kelas.index') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors shadow-sm self-start">
                Rekap Presensi per Kelas
            </a>
        @endif
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('presensi.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            <div class="sm:col-span-5 w-full">
                <label for="status" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status
                    Pertemuan</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    @foreach (['terjadwal' => 'Terjadwal', 'berlangsung' => 'Berlangsung', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(($filter['status'] ?? '') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="tanggal" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tanggal
                    Rencana</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $filter['tanggal'] ?? '' }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <div class="sm:col-span-3 flex gap-2 w-full">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors shadow-sm">Terapkan</button>
                <a href="{{ route('presensi.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center shadow-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Pertemuan yang Dapat Diakses</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftar->total(), 0, ',', '.') }} pertemuan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas / Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Pertemuan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Waktu Rencana</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Presensi</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftar as $sesi)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong
                                    class="font-bold text-slate-800 font-mono text-xs group-hover:text-siakad-dark transition-colors block">{{ $sesi->kelasKuliah->kode }}</strong>
                                <span
                                    class="text-slate-700 text-sm mt-0.5 block">{{ $sesi->kelasKuliah->nama_mk_snapshot }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">Pertemuan {{ $sesi->nomor }}</div>
                                <span class="text-xs text-slate-500 mt-0.5 block">{{ $sesi->topik }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700">
                                <div class="font-medium">
                                    {{ $sesi->mulai_rencana->setTimezone($zona)->format('d-m-Y H:i') }}</div>
                                <span class="text-slate-400 mt-0.5 block">s.d.
                                    {{ $sesi->selesai_rencana->setTimezone($zona)->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusBadgeClass = match ($sesi->status) {
                                        'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'berlangsung' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'terjadwal' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        default => 'bg-rose-50 text-rose-700 border-rose-100',
                                    };
                                    $dotClass = match ($sesi->status) {
                                        'selesai' => 'bg-emerald-500',
                                        'berlangsung' => 'bg-blue-500',
                                        'terjadwal' => 'bg-amber-500',
                                        default => 'bg-rose-500',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold uppercase tracking-wider {{ $statusBadgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ ucfirst($sesi->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $pStatus = $sesi->presensiPertemuan
                                        ? ucfirst($sesi->presensiPertemuan->status)
                                        : 'Belum disiapkan';
                                    $pBadgeClass = match ($sesi->presensiPertemuan?->status) {
                                        'terbuka' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'ditutup' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        default => 'bg-amber-50 text-amber-700 border-amber-100',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center rounded border px-2.5 py-1 text-xs font-bold uppercase tracking-wider {{ $pBadgeClass }}">
                                    {{ $pStatus }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('presensi.show', $sesi) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                                    Buka Panel
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <p class="text-sm font-bold text-slate-700">Belum ada pertemuan yang sesuai atau Anda belum
                                    ditugaskan.</p>
                                <p class="mt-1 text-xs">Sesuaikan kembali filter pencarian status dan tanggal Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('presensi._pagination', ['paginator' => $daftar])
    </div>
@endsection
