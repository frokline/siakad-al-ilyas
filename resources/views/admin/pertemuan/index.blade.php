@extends('layouts.admin')
@section('title', 'Pertemuan Kuliah')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Pertemuan Kuliah</h1>
            <p class="text-sm text-slate-500 mt-1">Rencana dan pelaksanaan (realisasi) sesi perkuliahan.</p>
        </div>
        <a href="{{ route('admin.pertemuan.create', $kelasTerpilih ? ['kelas_id' => $kelasTerpilih->id] : []) }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 self-start">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Pertemuan Baru
        </a>
    </div>

    @if ($kelasTerpilih)
        <div
            class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center justify-between shadow-sm">
            <span>Menampilkan filter pertemuan khusus kelas <strong
                    class="font-mono">{{ $kelasTerpilih->kode }}</strong>.</span>
            <a href="{{ route('admin.pertemuan.index') }}"
                class="font-semibold underline hover:text-amber-900 text-xs">Hapus filter kelas</a>
        </div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6 w-full">
        <form method="GET" action="{{ route('admin.pertemuan.index') }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            @if ($kelasTerpilih)
                <input type="hidden" name="kelas_id" value="{{ $kelasTerpilih->id }}">
            @endif

            <div class="sm:col-span-4 w-full">
                <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Topik,
                    Kelas, atau MK</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="q" name="q" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Cari..." maxlength="80"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="periode_id" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode
                    Akademik</label>
                <select id="periode_id" name="periode_id"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua periode</option>
                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="dosen_id"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Penanggung Jawab</label>
                <select id="dosen_id" name="dosen_id"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua dosen</option>
                    @foreach ($daftarDosen as $dosen)
                        <option value="{{ $dosen->id }}" @selected((string) ($filter['dosen_id'] ?? '') === (string) $dosen->id)>{{ $dosen->kode_dosen }} —
                            {{ $dosen->user->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="status"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Pertemuan::STATUS as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="tanggal" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tanggal
                    Rencana</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $filter['tanggal'] ?? '' }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <div class="sm:col-span-4 flex gap-2 w-full">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors shadow-sm">Terapkan</button>
                <a href="{{ route('admin.pertemuan.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center shadow-sm">Reset</a>
            </div>
        </form>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Daftar Pertemuan Kuliah</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ number_format($daftarPertemuan->total(), 0, ',', '.') }} sesi
                    &bull; TZ {{ config('siakad.timezone', 'Asia/Makassar') }}</p>
            </div>
            <span
                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">{{ number_format($daftarPertemuan->total(), 0, ',', '.') }}
                pertemuan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Rencana Sesi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas & MK</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Jenis & Topik</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Dosen</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftarPertemuan as $sesi)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong class="font-bold text-slate-800 block">Pertemuan {{ $sesi->nomor }}</strong>
                                <span
                                    class="text-xs text-slate-600 font-medium block mt-0.5">{{ $sesi->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y') }}</span>
                                <span
                                    class="text-xs text-slate-400 font-mono block mt-0.5">{{ $sesi->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}–{{ $sesi->selesai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.kelas-kuliah.show', $sesi->kelasKuliah) }}"
                                    class="font-bold text-slate-800 hover:text-siakad-dark transition-colors font-mono text-xs block">{{ $sesi->kelasKuliah->kode }}</a>
                                <div class="text-slate-700 text-sm mt-0.5">{{ $sesi->kelasKuliah->nama_mk_snapshot }}
                                </div>
                                <div class="text-xs text-slate-400 mt-1">{{ $sesi->kelasKuliah->rombel->kode }} &bull;
                                    {{ $sesi->kelasKuliah->rombel->periodeAkademik->kode }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700">
                                    {{ \App\Models\Pertemuan::JENIS[$sesi->jenis] ?? $sesi->jenis }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $sesi->topik }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $sesi->pengajar_snapshot['nama'] ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">
                                    {{ $sesi->pengajar_snapshot['kode_dosen'] ?? '—' }}</div>
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
                                    {{ \App\Models\Pertemuan::STATUS[$sesi->status] ?? $sesi->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.pertemuan.show', $sesi) }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">Tidak ada daftar pertemuan
                                yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50"><x-pagination :paginator="$daftarPertemuan" /></div>
    </div>
@endsection
