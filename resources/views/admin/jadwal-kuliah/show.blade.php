@extends('layouts.admin')
@section('title', 'Detail Jadwal Kuliah')

@section('content')
    @php
        $tokenLama = old('versi_jadwal');
        $usang = $tokenLama !== null && (!is_string($tokenLama) || !hash_equals($versi, $tokenLama));
        $alasanLama = old('alasan', '');
        $tautan = $jadwal->tautan_pertemuan;
        $tanggalPertama = $jadwal->tanggalPertama();
    @endphp

    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                Jadwal #{{ $jadwal->id }}
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $jadwal->aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $jadwal->aktif ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ $jadwal->aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelas <span class="font-mono font-bold text-slate-700">{{ $kelas->kode }}</span> &middot; Revisi
                {{ $jadwal->revisi }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($jadwal->dapatDiubah())
                <a class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                    href="{{ route('admin.jadwal-kuliah.edit', $jadwal) }}">Edit jadwal</a>
                <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                    href="{{ route('admin.jadwal-kuliah.create', ['kelas_id' => $kelas->id]) }}">Tambah pola lain</a>
            @endif
            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                href="{{ route('admin.jadwal-kuliah.index', ['kelas_id' => $kelas->id]) }}">Daftar kelas ini</a>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-6 flex flex-col sm:flex-row items-center gap-6">
            <div
                class="flex flex-col items-center justify-center bg-slate-800 text-white rounded-2xl p-5 min-w-[140px] shadow-sm">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-300 mb-1">Setiap
                    {{ \App\Models\JadwalKuliah::HARI[$jadwal->hari] }}</span>
                <span class="text-2xl font-black font-mono tracking-tight">{{ substr($jadwal->jam_mulai, 0, 5) }}</span>
                <span
                    class="text-2xl font-black font-mono tracking-tight leading-none text-slate-400 opacity-50 my-0.5">|</span>
                <span class="text-2xl font-black font-mono tracking-tight">{{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                <span class="text-[10px] text-slate-400 mt-2">{{ config('siakad.timezone', 'Asia/Makassar') }}</span>
            </div>

            <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rentang Berlaku</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $jadwal->berlaku_mulai->format('d-m-Y') }} <span
                            class="text-slate-400 mx-0.5 font-normal">s.d.</span>
                        {{ $jadwal->berlaku_selesai->format('d-m-Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Metode Pertemuan</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ \App\Models\JadwalKuliah::METODE[$jadwal->metode] }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Pertama (Sesuai Pola)</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $tanggalPertama?->format('d-m-Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Lokasi / Ruangan</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $jadwal->lokasi ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Terakhir Diperbarui</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        {{ $jadwal->updated_at->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Waktu Dinonaktifkan</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        {{ $jadwal->dinonaktifkan_at?->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') ?? '—' }}
                    </dd>
                </div>
            </div>
        </div>

        <div class="p-6 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-800 mb-3">Tautan Pertemuan Daring</h3>
            @if ($tautan !== null && \App\Rules\TautanPertemuanAman::sesuai($tautan))
                <a href="{{ $tautan }}" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer"
                    class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Tautan Tersimpan
                </a>
            @elseif($tautan !== null)
                <div
                    class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-start gap-3">
                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5 text-amber-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Tautan tersimpan tidak memenuhi standar format yang diizinkan sistem keamanan saat ini. Silakan perbarui
                    melalui formulir edit.
                </div>
            @else
                <p
                    class="text-sm text-slate-500 italic border border-dashed border-slate-300 rounded-lg p-4 bg-slate-50 inline-block w-full text-center">
                    Belum ada tautan pertemuan disematkan.</p>
            @endif
        </div>
        <div class="bg-slate-50/50 p-4 text-xs text-slate-500 text-center">
            Pola mingguan adalah <strong>rencana perkuliahan</strong>. Realisasi riil pertemuan, penetapan hari libur, dan
            pencatatan presensi akan ditangani pada modul berikutnya.
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Identitas Kelas dan Tim Pengajar</h2>
        </div>
        <div class="p-6">@include('admin.jadwal-kuliah._kelas')</div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Seluruh Pola Jadwal Kelas Ini</h2>
        </div>
        @include('admin.jadwal-kuliah._pola')
    </div>

    @if ($jadwal->aktif)
        <div class="rounded-xl bg-white shadow-sm border border-rose-200 overflow-hidden w-full mb-8">
            <div class="border-b border-rose-100 bg-rose-50 px-6 py-4 flex items-center gap-2 text-rose-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-sm font-bold">Nonaktifkan Pola Jadwal</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-4">Pemesanan waktu rombel dan seluruh dosen untuk pola jadwal ini akan
                    dilepaskan (dikosongkan). Data pola dan riwayat auditnya akan tetap tersimpan dalam arsip.</p>

                @if ($usang)
                    <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 flex items-center justify-between"
                        role="alert">
                        <span>Data telah berubah sejak dimuat.</span>
                        <a href="{{ route('admin.jadwal-kuliah.show', $jadwal) }}"
                            class="font-semibold underline hover:text-rose-900">Muat ulang halaman</a>
                        <span>sebelum memproses penonaktifan.</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.jadwal-kuliah.nonaktifkan', $jadwal) }}">
                    @csrf
                    <input type="hidden" name="versi_jadwal" value="{{ $versi }}">
                    <fieldset @disabled($usang)>
                        <legend class="sr-only">Konfirmasi penonaktifan pola</legend>
                        <div class="mb-4">
                            <label for="alasan_nonaktif"
                                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Alasan
                                Penonaktifan <span class="text-rose-500">*</span></label>
                            <textarea id="alasan_nonaktif" name="alasan" rows="3" minlength="10" maxlength="2000" required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:bg-white @error('alasan') border-rose-500 bg-rose-50/50 @enderror">{{ is_string($alasanLama) ? $alasanLama : '' }}</textarea>
                            @error('alasan')
                                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi_nonaktif">
                                <input class="h-4 w-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500"
                                    id="konfirmasi_nonaktif" name="konfirmasi" type="checkbox" value="1" required>
                                <span class="text-sm font-medium text-rose-700">Saya menyadari dan menyetujui penonaktifan
                                    pola jadwal ini secara permanen.</span>
                            </label>
                            @error('konfirmasi')
                                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex">
                            <button
                                class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2"
                                type="submit">Nonaktifkan jadwal ini</button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    @endif

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Perubahan & Audit Log</h2>
        </div>
        <div class="p-6">@include('admin.jadwal-kuliah._audit')</div>
    </div>
@endsection
