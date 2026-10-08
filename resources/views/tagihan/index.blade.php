@extends('layouts.admin')

@section('title', 'Daftar Tagihan')

@section('content')
    @php
        $petugas = (bool) auth()->user()?->can('create', \App\Models\Tagihan::class);
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600';
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $filterAktif =
            !empty($filter['q']) || !empty($filter['status']) || !empty($filter['tahun']) || !empty($filter['bulan']);
    @endphp

    @include('tagihan._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $petugas ? 'Tagihan bulanan' : 'Tagihan saya' }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $petugas ? 'Draf, tagihan terbit, dan tagihan dibatalkan yang dapat Anda kelola.' : 'Buka detail tagihan untuk mengajukan bukti pembayaran dan melihat status pengajuan.' }}
            </p>
        </div>
        @can('create', \App\Models\Tagihan::class)
            <a href="{{ route('tagihan.create') }}"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat draf
            </a>
        @endcan
    </div>

    {{-- Filter --}}
    <form method="get" action="{{ route('tagihan.index') }}"
        class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="q" class="{{ $label }}">Nomor tagihan</label>
                <input id="q" name="q" value="{{ $filter['q'] ?? '' }}" maxlength="100"
                    placeholder="Ketik nomor tagihan" class="{{ $input }}">
            </div>
            <div>
                <label for="status" class="{{ $label }}">Status</label>
                <select id="status" name="status" class="{{ $input }}">
                    <option value="">Semua</option>
                    @foreach (\App\Models\Tagihan::STATUS as $kode => $nama)
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bulan" class="{{ $label }}">Bulan</label>
                <select id="bulan" name="bulan" class="{{ $input }}">
                    <option value="">Semua bulan</option>
                    @foreach ($namaBulan as $no => $nama)
                        <option value="{{ $no }}" @selected((string) ($filter['bulan'] ?? '') === (string) $no)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tahun" class="{{ $label }}">Tahun</label>
                <input id="tahun" type="number" name="tahun" min="2000" max="2199"
                    value="{{ $filter['tahun'] ?? '' }}" placeholder="Contoh: 2026" class="{{ $input }}">
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="submit"
                class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Tampilkan</button>
            <a href="{{ route('tagihan.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Reset</a>
            @if ($filterAktif)
                <span class="text-xs text-slate-500">Filter aktif &middot; {{ $daftar->total() }} tagihan
                    ditemukan</span>
            @endif
        </div>
    </form>

    {{-- Tabel --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Nomor</th>
                        @if ($petugas)
                            <th class="px-5 py-3">Mahasiswa</th>
                        @endif
                        <th class="px-5 py-3">Biaya / bulan</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        <th class="px-5 py-3">Jatuh tempo</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftar as $t)
                        <tr class="hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-800">
                                <a href="{{ route('tagihan.show', $t) }}"
                                    class="hover:text-siakad-active">{{ $t->nomor }}</a>
                            </td>
                            @if ($petugas)
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">
                                        {{ $t->snapshot['nama'] ?? $t->mahasiswa->user->nama }}</p>
                                    <p class="text-xs text-slate-500">{{ $t->snapshot['nim'] ?? $t->mahasiswa->nim }}
                                    </p>
                                </td>
                            @endif
                            <td class="px-5 py-4">
                                <p class="text-slate-800">{{ $t->snapshot['jenis_nama'] ?? $t->jenisBiaya->nama }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $namaBulan[(int) $t->bulan_tagihan] ?? sprintf('%02d', $t->bulan_tagihan) }}
                                    {{ $t->tahun_tagihan }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-slate-800">
                                {{ $t->nominalRupiah() }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                {{ $t->jatuh_tempo->format('d-m-Y') }}</td>
                            <td class="px-5 py-4">@include('tagihan._lencana', ['status' => $t->status])</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('tagihan.show', $t) }}"
                                    class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $petugas ? 7 : 6 }}" class="px-5 py-12 text-center text-slate-500">
                                <svg class="mx-auto mb-3 h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                                Belum ada tagihan yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $daftar->links('tagihan._pagination') }}
    </section>
@endsection
