@extends('layouts.admin')

@section('title', 'Jadwal Kuliah')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Jadwal Kuliah</h1>
            <p class="text-sm text-slate-500 mt-1">Pola mingguan kelas, rombel, dan tim pengajar.</p>
        </div>

        <a href="{{ route('admin.jadwal-kuliah.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Jadwal
        </a>
    </div>

    @if ($rombelTerpilih || $kelasTerpilih)
        <div
            class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                @if ($rombelTerpilih)
                    <span
                        class="inline-flex items-center rounded bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">Rombel:
                        {{ $rombelTerpilih->kode }}</span>
                @endif
                @if ($kelasTerpilih)
                    <span
                        class="inline-flex items-center rounded bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">Kelas:
                        {{ $kelasTerpilih->kode }}</span>
                @endif
            </div>
            <a href="{{ route('admin.jadwal-kuliah.index') }}" class="font-semibold underline hover:text-amber-900 text-xs">
                Hapus filter khusus
            </a>
        </div>
    @endif

    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('admin.jadwal-kuliah.index') }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            @if ($rombelTerpilih)
                <input type="hidden" name="rombel_id" value="{{ $rombelTerpilih->id }}">
            @endif
            @if ($kelasTerpilih)
                <input type="hidden" name="kelas_id" value="{{ $kelasTerpilih->id }}">
            @endif

            <div class="sm:col-span-4 w-full">
                <label for="q" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Cari
                    Kelas / MK / Rombel</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="q" name="q" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Kode, nama MK, atau rombel..." maxlength="80"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="periode_id"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Periode Akademik</label>
                <select id="periode_id" name="periode_id"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua periode</option>
                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="dosen_id" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Dosen
                    Tim Aktif</label>
                <select id="dosen_id" name="dosen_id"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua dosen</option>
                    @foreach ($daftarDosen as $dosen)
                        <option value="{{ $dosen->id }}" @selected((string) ($filter['dosen_id'] ?? '') === (string) $dosen->id)>{{ $dosen->kode_dosen }} —
                            {{ $dosen->user->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="hari"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Hari</label>
                <select id="hari" name="hari"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua hari</option>
                    @foreach (\App\Models\JadwalKuliah::HARI as $kode => $label)
                        <option value="{{ $kode }}" @selected((string) ($filter['hari'] ?? '') === (string) $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="tanggal" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Berlaku
                    Pada Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $filter['tanggal'] ?? '' }}"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="aktif" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Status
                    Pola</label>
                <select id="aktif" name="aktif"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    <option value="1" @selected(($filter['aktif'] ?? '') === '1')>Aktif</option>
                    <option value="0" @selected(($filter['aktif'] ?? '') === '0')>Nonaktif</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex gap-2 w-full pt-1">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
                    Terapkan
                </button>
                <a href="{{ route('admin.jadwal-kuliah.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">

        <div
            class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Daftar Pola Jadwal Kuliah</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ number_format($daftarJadwal->total(), 0, ',', '.') }} pola sesuai filter &bull; Jam
                    {{ config('siakad.timezone', 'Asia/Makassar') }}
                </p>
            </div>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftarJadwal->total(), 0, ',', '.') }} jadwal
            </span>
        </div>

        <div class="px-6 py-2.5 bg-slate-50/80 border-b border-slate-200 text-xs text-slate-500 italic">
            Filter tanggal menampilkan pola yang jatuh pada hari tersebut. Tanggal libur dan perubahan pertemuan diatur pada
            modul pelaksanaan kuliah.
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Hari / Jam</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas / Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Rombel / Periode</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Berlaku</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Metode / Lokasi</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftarJadwal as $jadwal)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong
                                    class="font-bold text-slate-800 block">{{ \App\Models\JadwalKuliah::HARI[$jadwal->hari] }}</strong>
                                <span
                                    class="text-xs text-slate-500 font-mono inline-block mt-0.5">{{ substr($jadwal->jam_mulai, 0, 5) }}
                                    – {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.kelas-kuliah.show', $jadwal->kelasKuliah) }}"
                                    class="font-bold text-slate-800 hover:text-siakad-dark transition-colors font-mono text-xs block">{{ $jadwal->kelasKuliah->kode }}</a>
                                <div class="text-slate-700 text-sm mt-0.5">{{ $jadwal->kelasKuliah->nama_mk_snapshot }}
                                </div>
                                <div class="text-xs text-slate-400 mt-1">
                                    {{ $jadwal->kelasKuliah->pengajarKelas->where('aktif', true)->map(fn($p) => $p->dosen->user->nama)->implode(', ') ?: 'Belum ada dosen aktif' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.jadwal-kuliah.index', ['rombel_id' => $jadwal->kelasKuliah->rombel_id]) }}"
                                    class="font-bold text-slate-800 hover:text-siakad-dark transition-colors block">{{ $jadwal->kelasKuliah->rombel->kode }}</a>
                                <span
                                    class="text-xs text-slate-400 mt-0.5 block">{{ $jadwal->kelasKuliah->rombel->periodeAkademik->kode }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700">
                                <div class="font-medium">{{ $jadwal->berlaku_mulai->format('d-m-Y') }}</div>
                                <div class="text-slate-400 mt-0.5">s.d. {{ $jadwal->berlaku_selesai->format('d-m-Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700">
                                    {{ \App\Models\JadwalKuliah::METODE[$jadwal->metode] }}</div>
                                <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[150px]"
                                    title="{{ $jadwal->lokasi ?? '—' }}">{{ $jadwal->lokasi ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($jadwal->aktif)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-bold text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.jadwal-kuliah.show', $jadwal) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        title="Detail Jadwal">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 mb-4">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Tidak ada jadwal yang sesuai</p>
                                <p class="mt-1 text-xs text-slate-500">Ubah filter pencarian atau buat jadwal baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$daftarJadwal" />
    </div>
@endsection
