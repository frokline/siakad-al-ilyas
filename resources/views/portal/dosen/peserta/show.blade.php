@extends('layouts.portal')

@section('title', 'Detail Peserta Kelas')

@section('content')
    @php
        $krs = $peserta->krs;
        $registrasi = $krs?->registrasiSemester;
        $periode = $registrasi?->periodeAkademik;
        $riwayat = $registrasi?->riwayatStudi;
        $mahasiswa = $riwayat?->mahasiswa;
        $akun = $mahasiswa?->user;

        $jumlahPresensi = $rekapPresensi['jumlah'];
        $riwayatPresensi = $rekapPresensi['riwayat'];
        $persentaseHadir = (float) $rekapPresensi['persentase_hadir'];

        $badgeStatus = static function (string $label): string {
            $nilai = strtolower($label);

            if (in_array($nilai, ['aktif', 'disahkan', 'terdaftar', 'hadir'], true)) {
                return 'bg-emerald-50 text-emerald-700 border-emerald-200';
            }

            if (in_array($nilai, ['diajukan', 'draf', 'menunggu pengesahan', 'sakit', 'cuti'], true)) {
                return 'bg-amber-50 text-amber-700 border-amber-200';
            }

            if ($nilai === 'izin') {
                return 'bg-blue-50 text-blue-700 border-blue-200';
            }

            if (in_array($nilai, ['batal', 'dibatalkan', 'alpa'], true)) {
                return 'bg-rose-50 text-rose-700 border-rose-200';
            }

            if ($nilai === 'belum dicatat') {
                return 'bg-slate-100 text-slate-500 border-slate-200';
            }

            return 'bg-slate-100 text-slate-600 border-slate-200';
        };

        $labelRegistrasi = $registrasi
            ? (\App\Models\RegistrasiSemester::STATUS[$registrasi->status] ?? $registrasi->status)
            : null;
        $labelKrs = $krs
            ? (\App\Models\Krs::STATUS[$krs->status] ?? $krs->status)
            : null;
        $labelPeserta = \App\Models\DetailKrs::STATUS[$peserta->status] ?? $peserta->status;
    @endphp

    <!-- HEADER HALAMAN -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex flex-wrap items-center gap-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal Dosen</span></li>
                    <li><span>&middot;</span></li>
                    <li><span class="font-medium">Kelas Saya</span></li>
                    <li><span>&middot;</span></li>
                    <li><span class="font-medium">Peserta Kelas</span></li>
                    <li><span>&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Detail Peserta</li>
                </ol>
            </nav>

            <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                <span class="rounded-md bg-slate-200 px-2 py-1 font-mono text-xl text-slate-700">
                    {{ $kelas->kode }}
                </span>
                Detail Peserta
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ $akun?->nama ?? '—' }}
                @if ($mahasiswa?->nim)
                    &middot;
                    <span class="font-mono">{{ $mahasiswa->nim }}</span>
                @endif
            </p>
        </div>

        <a href="{{ route('portal.dosen.peserta.index', $kelas->id) }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            &larr; Kembali ke Daftar Peserta
        </a>
    </div>

    <!-- GRID IDENTITAS & RINGKASAN PRESENSI -->
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- KARTU IDENTITAS AKADEMIK -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-4 border-b border-slate-100 pb-2 text-sm font-bold text-slate-800">
                Identitas Akademik
            </h2>

            <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">Nama Mahasiswa</dt>
                    <dd class="text-right font-semibold text-slate-800">{{ $akun?->nama ?? '—' }}</dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">NIM</dt>
                    <dd class="text-right font-mono font-semibold text-slate-800">
                        {{ $mahasiswa?->nim ?? '—' }}
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">Kelas</dt>
                    <dd class="text-right font-mono font-semibold text-slate-800">{{ $kelas->kode }}</dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">Mata Kuliah</dt>
                    <dd class="text-right font-semibold text-slate-800">{{ $kelas->nama_mk_snapshot }}</dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">Jumlah SKS</dt>
                    <dd class="text-right font-semibold text-slate-800">
                        {{ str_replace('.', ',', (string) $kelas->sks_snapshot) }} SKS
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">Periode Akademik</dt>
                    <dd class="text-right font-semibold text-slate-800">{{ $periode?->kode ?? '—' }}</dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">Semester Studi</dt>
                    <dd class="text-right font-semibold text-slate-800">
                        {{ $registrasi?->semester_studi ?? '—' }}
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-slate-500">Status Registrasi</dt>
                    <dd class="text-right">
                        @if ($labelRegistrasi)
                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeStatus($labelRegistrasi) }}">
                                {{ $labelRegistrasi }}
                            </span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4 border-b border-slate-50 pb-2 sm:border-0 sm:pb-0">
                    <dt class="text-slate-500">Status KRS</dt>
                    <dd class="text-right">
                        @if ($labelKrs)
                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeStatus($labelKrs) }}">
                                {{ $labelKrs }}
                            </span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-slate-500">Status Peserta</dt>
                    <dd class="text-right">
                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeStatus($labelPeserta) }}">
                            {{ $labelPeserta }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- KARTU RINGKASAN PRESENSI -->
        <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-50/60"></div>

            <h2 class="relative z-10 mb-4 border-b border-slate-100 pb-2 text-sm font-bold text-slate-800">
                Ringkasan Presensi
            </h2>

            <div class="relative z-10 mb-4">
                <span class="block text-3xl font-black text-emerald-600">
                    {{ number_format($persentaseHadir, 2, ',', '.') }}%
                </span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Persentase Hadir</span>

                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-500 transition-all"
                        style="width: {{ min(100, max(0, $persentaseHadir)) }}%"></div>
                </div>

                <p class="mt-2 text-xs text-slate-500">
                    {{ number_format($rekapPresensi['total_tercatat'], 0, ',', '.') }} dari
                    {{ number_format($rekapPresensi['total'], 0, ',', '.') }} pertemuan tercatat
                </p>
            </div>

            <div class="relative z-10 grid grid-cols-2 gap-2">
                <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-3 text-center" title="Hadir">
                    <span class="block font-bold text-emerald-700">
                        {{ number_format($jumlahPresensi['hadir'] ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="mt-1 block text-[9px] font-bold uppercase tracking-wider text-emerald-600">Hadir</span>
                </div>

                <div class="rounded-lg border border-blue-100 bg-blue-50 p-3 text-center" title="Izin">
                    <span class="block font-bold text-blue-700">
                        {{ number_format($jumlahPresensi['izin'] ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="mt-1 block text-[9px] font-bold uppercase tracking-wider text-blue-600">Izin</span>
                </div>

                <div class="rounded-lg border border-amber-100 bg-amber-50 p-3 text-center" title="Sakit">
                    <span class="block font-bold text-amber-700">
                        {{ number_format($jumlahPresensi['sakit'] ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="mt-1 block text-[9px] font-bold uppercase tracking-wider text-amber-600">Sakit</span>
                </div>

                <div class="rounded-lg border border-rose-100 bg-rose-50 p-3 text-center" title="Alpa">
                    <span class="block font-bold text-rose-700">
                        {{ number_format($jumlahPresensi['alpa'] ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="mt-1 block text-[9px] font-bold uppercase tracking-wider text-rose-600">Alpa</span>
                </div>
            </div>

            <p class="relative z-10 mt-3 text-center text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                Belum dicatat:
                {{ number_format($jumlahPresensi[\App\Models\Presensi::BELUM] ?? 0, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <!-- TABEL RIWAYAT PRESENSI -->
    <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Presensi</h2>
            <span class="text-xs text-slate-500">
                {{ number_format($riwayatPresensi->count(), 0, ',', '.') }} catatan
            </span>
        </div>

        @if ($riwayatPresensi->isEmpty())
            <div class="px-6 py-12 text-center">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Belum ada riwayat presensi</p>
                <p class="mt-1 text-xs text-slate-500">
                    Belum ada daftar presensi yang dibuat untuk peserta pada kelas ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] border-collapse text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Pertemuan</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Topik</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Waktu Rencana</th>
                            <th scope="col" class="px-6 py-4 text-center font-semibold">Status</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Dicatat Pada</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Catatan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($riwayatPresensi as $presensi)
                            @php
                                $pertemuan = $presensi->daftar?->pertemuan;
                                $labelStatus = \App\Models\Presensi::STATUS[$presensi->status]
                                    ?? $presensi->status;
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="px-6 py-4">
                                    @if ($pertemuan?->nomor)
                                        <span
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                            {{ $pertemuan->nomor }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <span class="block font-semibold text-slate-800">
                                        {{ $pertemuan?->topik ?: 'Tanpa Topik' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ $pertemuan?->mulai_rencana
                                        ? $pertemuan->mulai_rencana->format('d M Y, H:i')
                                        : '—' }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeStatus($labelStatus) }}">
                                        {{ $labelStatus }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                    {{ $presensi->dicatat_at
                                        ? $presensi->dicatat_at->format('d M Y, H:i')
                                        : '—' }}
                                </td>

                                <td class="px-6 py-4 text-slate-500">
                                    {{ $presensi->catatan ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- CATATAN PRIVASI -->
    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Catatan Privasi</h3>
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Rekap hanya berasal dari presensi pada kelas ini. Data pribadi seperti alamat,
                    telepon, tempat lahir, tanggal lahir, dan informasi peserta kelas lain tidak
                    ditampilkan.
                </p>
            </div>
        </div>
    </div>
@endsection
