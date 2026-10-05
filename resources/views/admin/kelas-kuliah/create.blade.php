@extends('layouts.admin')

@section('title', 'Tambah Kelas Kuliah')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Tambah Kelas Kuliah</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih rombel, kemudian mata kuliah dari paketnya.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.kelas-kuliah.index') }}">
            Daftar Kelas
        </a>
    </div>

    @if ($rombel === null)
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6 w-full">
            <form method="GET" action="{{ route('admin.kelas-kuliah.create') }}"
                class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-6 w-full">
                    <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Cari
                        Rombel atau Paket</label>
                    <input type="search" name="q" id="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Kode rombel atau nama paket"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                </div>
                <div class="sm:col-span-4 w-full">
                    <label for="periode_akademik_id"
                        class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode</label>
                    <select name="periode_akademik_id" id="periode_akademik_id"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                        <option value="">Semua periode terbuka</option>
                        @foreach ($periodePilihan as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $periode->id)>
                                {{ $periode->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 w-full">
                    <button type="submit"
                        class="w-full rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center shadow-sm">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Daftar Rombel Pilihan</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Rombel</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Periode</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Paket</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Kelas Tercatat</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rombelPilihan as $pilihan)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <strong class="font-bold text-slate-800 block">{{ $pilihan->kode }}</strong>
                                    <span
                                        class="text-xs text-slate-500 block mt-0.5">{{ $pilihan->paketSemester->kurikulum->programStudi->nama }}</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-700">{{ $pilihan->periodeAkademik->kode }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="font-medium text-slate-800 block">{{ $pilihan->paketSemester->nama }}</span>
                                    <span class="text-xs text-slate-500 block mt-0.5">
                                        Semester {{ $pilihan->paketSemester->semester_studi }}
                                        · Versi {{ $pilihan->paketSemester->versi }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-medium text-slate-700">
                                    {{ $pilihan->kelas_kuliah_count }} / {{ $pilihan->paketSemester->details_count }} MK
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="inline-flex items-center gap-1 rounded-lg bg-siakad-dark px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm"
                                        href="{{ route('admin.kelas-kuliah.create', ['rombel_id' => $pilihan->id]) }}"
                                        aria-label="Pilih rombel {{ $pilihan->kode }} periode {{ $pilihan->periodeAkademik->kode }}">
                                        Pilih
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada rombel yang memenuhi syarat pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div
                class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-500">Jumlah kelas tercatat mencakup semua status, termasuk arsip.</p>
                <x-pagination :paginator="$rombelPilihan" />
            </div>
        </div>
    @else
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800">Rombel Terpilih</h2>
                <a href="{{ route('admin.kelas-kuliah.create') }}"
                    class="text-xs font-semibold text-siakad-dark hover:underline">Pilih rombel lain</a>
            </div>
            <div class="p-6">
                @include('admin.kelas-kuliah._identitas')
            </div>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Data Kelas Baru</h2>
            </div>
            <div class="p-6">
                @if (!$bolehMembuat)
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                        Kelas baru memerlukan periode persiapan atau aktif, kurikulum dan program studi aktif, serta paket
                        rombel yang sudah pernah diterbitkan.
                    </div>
                @elseif ($detailPilihan->isEmpty())
                    @if ($kelasAda->count() >= (int) $rombel->paketSemester->details_count)
                        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                            Semua mata kuliah paket sudah memiliki kelas. Gunakan daftar di bawah untuk membuka kelasnya.
                        </div>
                    @else
                        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                            Tidak ada mata kuliah aktif yang dapat ditambahkan. Periksa status mata kuliah dalam paket.
                        </div>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.kelas-kuliah.store') }}">
                        @include('admin.kelas-kuliah._form')
                    </form>
                @endif
            </div>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800">Kelas dalam Rombel Ini</h2>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                    {{ $kelasAda->count() }} / {{ $rombel->paketSemester->details_count }} mata kuliah
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Kode Kelas</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Mata Kuliah</th>
                            <th scope="col" class="px-6 py-4 font-semibold">SKS</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kelasAda as $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-slate-800 text-xs">{{ $item->kode }}</td>
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $item->nama_mk_snapshot }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-700">
                                    {{ str_replace('.', ',', $item->sks_snapshot) }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $s = $item->status;
                                        $badgeClass = match ($s) {
                                            'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'selesai' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'arsip' => 'bg-slate-100 text-slate-600 border-slate-200',
                                            default => 'bg-amber-50 text-amber-700 border-amber-100',
                                        };
                                        $dotClass = match ($s) {
                                            'aktif' => 'bg-emerald-500',
                                            'selesai' => 'bg-blue-500',
                                            'arsip' => 'bg-slate-400',
                                            default => 'bg-amber-500',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ \App\Models\KelasKuliah::STATUS[$item->status] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        href="{{ route('admin.kelas-kuliah.show', $item) }}"
                                        aria-label="Buka kelas {{ $item->kode }}">
                                        Buka kelas
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada kelas dalam rombel ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
