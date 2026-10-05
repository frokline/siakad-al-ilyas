@extends('layouts.admin')
@section('title', 'Buat KRS Paket Semester')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Buat KRS Paket Semester</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih registrasi aktif, periksa seluruh paket, lalu buat draf.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.krs.index') }}">Daftar seluruh KRS</a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Pilih Registrasi Mahasiswa</h2>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('admin.krs.create') }}"
                class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-5 w-full">
                    <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">NIM
                        atau Nama Mahasiswa</label>
                    <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Cari mahasiswa..."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                </div>
                <div class="sm:col-span-5 w-full">
                    <label for="periode_id"
                        class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode Aktif</label>
                    <select id="periode_id" name="periode_id"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                        <option value="">Semua periode aktif</option>
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>
                                {{ $periode->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 flex gap-2 w-full">
                    <button
                        class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center shadow-sm"
                        type="submit">Cari</button>
                    <a class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center shadow-sm"
                        href="{{ route('admin.krs.create') }}">Reset</a>
                </div>
            </form>
            <p class="mt-4 text-xs text-slate-500 italic">Daftar di bawah menampilkan registrasi aktif pada periode aktif
                yang belum memiliki KRS.</p>
        </div>
        <div class="overflow-x-auto border-t border-slate-200">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Mahasiswa</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Periode</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Rombel / Semester</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($calonRegistrasi as $calon)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong
                                    class="font-bold text-slate-800 font-mono block">{{ $calon->riwayatStudi->mahasiswa->nim }}</strong>
                                <span
                                    class="text-slate-700 block mt-0.5">{{ $calon->riwayatStudi->mahasiswa->user->nama }}</span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">{{ $calon->periodeAkademik->kode }}</td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800 block">{{ $calon->rombel->kode }}</span>
                                <span class="text-xs text-slate-500 block mt-0.5">Semester
                                    {{ $calon->semester_studi }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a class="inline-flex items-center gap-1 rounded-lg bg-siakad-dark px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm"
                                    href="{{ route('admin.krs.create', [
                                        'registrasi_id' => $calon->id,
                                        'q' => $filter['q'] ?? null,
                                        'periode_id' => $filter['periode_id'] ?? null,
                                    ]) . '#paket-krs' }}">Periksa
                                    paket &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <p class="text-sm font-bold text-slate-700">Tidak ada registrasi maba/aktif yang sesuai.</p>
                                <p class="mt-1 text-xs">Periksa aktivasi registrasi mahasiswa atau buka KRS yang sudah
                                    pernah dibuat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50"><x-pagination :paginator="$calonRegistrasi" /></div>
    </div>

    @if ($registrasi)
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8" id="paket-krs">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Identitas dan Paket Terpilih</h2>
            </div>
            <div class="p-6">
                @include('admin.krs._identitas')

                @if ($kendala !== [])
                    <div class="mt-6 rounded-lg bg-rose-50 border border-rose-200 p-5 text-sm text-rose-700">
                        <strong class="block mb-2 font-bold text-rose-800">Pembuatan KRS belum tersedia karena:</strong>
                        <ul class="list-disc list-inside space-y-1 ml-2 text-xs">
                            @foreach ($kendala as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($registrasi->krs)
                        <a class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                            href="{{ route('admin.krs.show', $registrasi->krs) }}">Buka KRS yang sudah ada</a>
                    @endif
                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                        href="{{ route('admin.kelas-kuliah.index', ['rombel_id' => $registrasi->rombel_id]) }}">
                        Periksa ketersediaan kelas rombel
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto border-t border-slate-200">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <caption
                        class="bg-slate-50 px-6 py-3 text-xs font-bold text-slate-700 text-left border-b border-slate-200 uppercase tracking-wider">
                        Seluruh {{ $barisPaket->count() }} mata kuliah dalam paket
                    </caption>
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold w-16">No.</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Mata Kuliah</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">SKS</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Kelas Dibuat</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Status Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($barisPaket as $baris)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 text-center font-medium">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $baris['nama'] }}</td>
                                <td class="px-6 py-4 text-center font-bold text-slate-700">
                                    {{ str_replace('.', ',', $baris['sks']) }}</td>
                                <td
                                    class="px-6 py-4 font-mono text-xs font-bold {{ $baris['kelas'] ? 'text-siakad-dark' : 'text-slate-400' }}">
                                    {{ $baris['kelas']?->kode ?? 'Belum dibuat' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($baris['kelas'])
                                        <span
                                            class="inline-flex items-center rounded border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border-slate-200">
                                            {{ \App\Models\KelasKuliah::STATUS[$baris['kelas']->status] }}
                                        </span>
                                    @else
                                        <span class="text-rose-500 text-xs font-medium">Lengkapi dahulu</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 italic">Paket semester
                                    belum memiliki rincian mata kuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($kendala === [])
                <div class="p-6 border-t border-slate-200 bg-slate-50/30">
                    <div class="rounded-lg bg-blue-50 border border-blue-200 p-4 text-xs text-blue-800 mb-6">
                        Draf KRS akan mencakup seluruh mata kuliah di atas secara utuh. Proses <strong>Pengajuan</strong>
                        dapat dilakukan setelah dipastikan seluruh kelas bersatus aktif.
                    </div>
                    <form method="POST" action="{{ route('admin.krs.store') }}">
                        @include('admin.krs._form', ['krs' => null, 'bolehSimpan' => true])
                    </form>
                </div>
            @endif
        </div>
    @endif
@endsection
