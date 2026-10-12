@extends('layouts.admin')

@section('title', 'Tambah Registrasi Semester')

@section('content')
    @php
        $langkah =
            $riwayat === null
                ? 1
                : ($periodeId > 0 && $registrasiLama === null && $rombelPilihan->isNotEmpty()
                    ? 3
                    : 2);
        $periodeParam = $periodeId > 0 ? $periodeId : null;
        $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
        $kepala = 'rounded-t-2xl border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
        $judul = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20';
        $langkahDaftar = [1 => 'Pilih mahasiswa', 2 => 'Pilih periode', 3 => 'Rombel & simpan'];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('admin.registrasi-semester.index') }}" class="hover:text-siakad-dark">Registrasi
                    Semester</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-700">Tambah</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Registrasi Semester</h1>
            <p class="mt-1 text-sm text-slate-500">Pilih mahasiswa, periode, lalu rombel yang sesuai kurikulum.</p>
        </div>
        <a href="{{ route('admin.registrasi-semester.index') }}"
            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Daftar
            registrasi</a>
    </div>

    {{-- Indikator langkah --}}
    <ol class="mb-6 grid grid-cols-3 gap-3" aria-label="Langkah">
        @foreach ($langkahDaftar as $no => $nama)
            <li class="flex items-center gap-3 rounded-xl border p-3 {{ $no === $langkah ? 'border-siakad-dark bg-emerald-50' : ($no < $langkah ? 'border-emerald-200 bg-white' : 'border-slate-200 bg-white') }}"
                @if ($no === $langkah) aria-current="step" @endif>
                <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $no < $langkah ? 'bg-siakad-dark text-white' : ($no === $langkah ? 'bg-siakad-dark text-white' : 'bg-slate-100 text-slate-500') }}">
                    {{ $no < $langkah ? '✓' : $no }}
                </span>
                <span
                    class="hidden text-sm font-semibold sm:block {{ $no <= $langkah ? 'text-slate-900' : 'text-slate-400' }}">{{ $nama }}</span>
            </li>
        @endforeach
    </ol>

    @if ($riwayat === null)
        {{-- LANGKAH 1: pilih mahasiswa --}}
        <section class="{{ $kartu }} overflow-hidden">
            <div class="{{ $kepala }}">
                <h2 class="{{ $judul }}">Pilih mahasiswa</h2>
            </div>

            <div class="p-6">
                <form method="GET" action="{{ route('admin.registrasi-semester.create') }}"
                    class="flex flex-col gap-3 sm:flex-row">
                    <div class="flex-1">
                        <label for="q" class="sr-only">Cari mahasiswa</label>
                        <input type="search" id="q" name="q" maxlength="100"
                            value="{{ $filter['q'] ?? '' }}" placeholder="Cari NIM atau nama mahasiswa"
                            class="{{ $input }}">
                    </div>
                    @if ($periodeParam)
                        <input type="hidden" name="periode_akademik_id" value="{{ $periodeParam }}">
                    @endif
                    <button type="submit"
                        class="rounded-xl bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-siakad-active">Cari</button>
                </form>
                <p class="mt-2 text-xs text-slate-500">Menampilkan riwayat studi aktif dengan akun mahasiswa, kurikulum, dan
                    program studi aktif.</p>
            </div>

            <div class="overflow-x-auto border-t border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Mahasiswa</th>
                            <th scope="col" class="px-4 py-3">Program studi / kurikulum</th>
                            <th scope="col" class="px-4 py-3">Angkatan</th>
                            <th scope="col" class="px-6 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($riwayatPilihan as $pilihan)
                            <tr class="transition hover:bg-emerald-50/40">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $pilihan->mahasiswa->user->nama }}</p>
                                    <p class="text-xs text-slate-500">{{ $pilihan->mahasiswa->nim }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-slate-800">{{ $pilihan->kurikulum->programStudi->nama }}</p>
                                    <p class="text-xs text-slate-500">{{ $pilihan->kurikulum->kode }}</p>
                                </td>
                                <td class="px-4 py-4 text-slate-700">{{ $pilihan->angkatan }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.registrasi-semester.create', ['riwayat_studi_id' => $pilihan->id, 'periode_akademik_id' => $periodeParam]) }}"
                                        aria-label="Pilih mahasiswa {{ $pilihan->mahasiswa->nim }}"
                                        class="rounded-lg bg-siakad-dark px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-siakad-active">Pilih</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">
                                    Mahasiswa yang memenuhi syarat belum ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($riwayatPilihan->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $riwayatPilihan->links('admin.registrasi-semester._pagination') }}
                </div>
            @endif
        </section>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">

                {{-- Mahasiswa terpilih --}}
                <section class="{{ $kartu }}">
                    <div class="{{ $kepala }} flex items-center justify-between">
                        <h2 class="{{ $judul }}">Mahasiswa terpilih</h2>
                        <a href="{{ route('admin.registrasi-semester.create', ['periode_akademik_id' => $periodeParam]) }}"
                            class="text-xs font-semibold text-siakad-active hover:underline">Pilih mahasiswa lain</a>
                    </div>
                    <div class="p-6">@include('admin.registrasi-semester._identitas')</div>
                </section>

                {{-- Periode --}}
                <section class="{{ $kartu }}">
                    <div class="{{ $kepala }}">
                        <h2 class="{{ $judul }}">Periode akademik</h2>
                    </div>
                    <div class="p-6">
                        <form method="GET" action="{{ route('admin.registrasi-semester.create') }}"
                            class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <input type="hidden" name="riwayat_studi_id" value="{{ $riwayat->id }}">
                            <div class="flex-1">
                                <label for="periode_akademik_id"
                                    class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Periode</label>
                                <select name="periode_akademik_id" id="periode_akademik_id" required
                                    class="{{ $input }}">
                                    <option value="">Pilih periode</option>
                                    @foreach ($periodePilihan as $periode)
                                        <option value="{{ $periode->id }}" @selected($periodeId === (int) $periode->id)>
                                            {{ $periode->kode }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" @disabled($periodePilihan->isEmpty())
                                class="rounded-xl border border-siakad-dark bg-white px-5 py-2.5 text-sm font-semibold text-siakad-dark transition hover:bg-emerald-50 disabled:opacity-50">Tampilkan
                                rombel</button>
                        </form>

                        @if ($periodePilihan->isEmpty())
                            <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                Belum ada periode persiapan atau aktif. Siapkan periode akademik terlebih dahulu.</p>
                        @elseif ($registrasiLama !== null)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                Mahasiswa ini sudah memiliki registrasi untuk periode tersebut.
                                <a href="{{ route('admin.registrasi-semester.show', $registrasiLama) }}"
                                    class="font-semibold underline">Buka registrasi yang sudah ada</a>.
                            </div>
                        @elseif ($rombelPilihan->isEmpty())
                            <p class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                Belum ada rombel yang memenuhi syarat untuk kurikulum dan periode ini.
                                Periksa paket terbit, mata kuliah aktif, dan rombel.</p>
                        @endif
                    </div>
                </section>

                {{-- Rombel & simpan --}}
                @if ($periodePilihan->isNotEmpty() && $registrasiLama === null && $rombelPilihan->isNotEmpty())
                    <section class="{{ $kartu }}">
                        <div class="{{ $kepala }}">
                            <h2 class="{{ $judul }}">Rombel & konfirmasi</h2>
                        </div>
                        <form method="POST" action="{{ route('admin.registrasi-semester.store') }}" class="p-6">
                            @include('admin.registrasi-semester._form')
                        </form>
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Alur registrasi</h2>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-emerald-50">
                        <li class="flex gap-2"><span aria-hidden="true">•</span>Registrasi baru selalu berstatus
                            Terdaftar.</li>
                        <li class="flex gap-2"><span aria-hidden="true">•</span>Aktivasi dilakukan lewat halaman edit
                            setelah data diperiksa.</li>
                        <li class="flex gap-2"><span aria-hidden="true">•</span>Satu mahasiswa hanya punya satu registrasi
                            per periode.</li>
                        <li class="flex gap-2"><span aria-hidden="true">•</span>Registrasi tidak dihapus; gunakan status
                            Batal.</li>
                    </ul>
                </section>
            </aside>
        </div>
    @endif
@endsection
