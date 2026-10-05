@extends('layouts.admin')
@section('title', 'Tambah Pengajar Kelas')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Tambah Pengajar Kelas</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih kelas, periksa tim, lalu tentukan dosen dan perannya.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.pengajar-kelas.index') }}">Daftar penugasan</a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Pilih Kelas</h2>
        </div>
        <div class="p-6">
            <form class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end" method="GET"
                action="{{ route('admin.pengajar-kelas.create') }}">
                <div class="sm:col-span-6 w-full">
                    <label for="q"
                        class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kelas, Mata Kuliah,
                        atau Rombel</label>
                    <input type="search" name="q" id="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Cari kelas"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                </div>
                <div class="sm:col-span-4 w-full">
                    <label for="periode_id"
                        class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode</label>
                    <select name="periode_id" id="periode_id"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                        <option value="">Semua periode terbuka</option>
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex gap-2 w-full">
                    <button
                        class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center shadow-sm"
                        type="submit">Cari</button>
                    <a class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center shadow-sm"
                        href="{{ route('admin.pengajar-kelas.create') }}">Reset</a>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto border-t border-slate-200">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas / Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Periode / Rombel</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status Kelas</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Penugasan Aktif</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($calonKelas as $calon)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <strong class="font-bold text-slate-800 block"><span
                                        class="font-mono text-xs">{{ $calon->kode }}</span></strong>
                                <span class="text-slate-700 block mt-0.5">{{ $calon->nama_mk_snapshot }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">
                                {{ $calon->rombel->periodeAkademik->kode }} <br>
                                <span class="text-xs text-slate-500 font-normal">{{ $calon->rombel->kode }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-700">
                                {{ \App\Models\KelasKuliah::STATUS[$calon->status] }}</td>
                            <td class="px-6 py-4 text-center font-medium text-slate-700">{{ $calon->pengajar_aktif_count }}
                                dosen</td>
                            <td class="px-6 py-4 text-right">
                                <a class="inline-flex items-center gap-1 rounded-lg bg-siakad-dark px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm"
                                    href="{{ route('admin.pengajar-kelas.create', [
                                        'kelas_id' => $calon->id,
                                        'q' => $filter['q'] ?? null,
                                        'periode_id' => $filter['periode_id'] ?? null,
                                    ]) . '#kelas-terpilih' }}">Pilih
                                    kelas</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada kelas persiapan/aktif pada periode terbuka sesuai pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50"><x-pagination :paginator="$calonKelas" /></div>
    </div>

    @if ($kelas)
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8" id="kelas-terpilih">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Kelas Terpilih</h2>
            </div>
            <div class="p-6">@include('admin.pengajar-kelas._kelas')</div>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Tim Pengajar Saat Ini</h2>
            </div>
            @include('admin.pengajar-kelas._tim')
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Penugasan Baru</h2>
            </div>
            <div class="p-6">
                @unless ($bolehSimpan)
                    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
                        @if ($dosenPilihan->isEmpty())
                            Belum ada dosen yang dapat ditambahkan. Periksa data dosen, akun dan role, atau gunakan penugasan
                            lama pada tim di atas.
                        @else
                            Periksa status kelas/periode serta keaktifan program studi dan kurikulumnya. Penugasan baru hanya
                            tersedia pada kelas persiapan/aktif dengan sumber akademik yang sesuai.
                        @endif
                    </div>
                @endunless
                <form method="POST" action="{{ route('admin.pengajar-kelas.store') }}">
                    @include('admin.pengajar-kelas._form')
                </form>
            </div>
        </div>
    @endif
@endsection
