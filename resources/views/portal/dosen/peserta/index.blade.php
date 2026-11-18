@extends('layouts.portal')

@section('title', 'Peserta Kelas')

@section('content')
    @php
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

            return 'bg-slate-100 text-slate-600 border-slate-200';
        };
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
                    <li class="font-semibold text-siakad-dark" aria-current="page">Peserta Kelas</li>
                </ol>
            </nav>

            <h1 class="flex items-center gap-3 text-2xl font-bold text-slate-800">
                <span class="rounded-md bg-slate-200 px-2 py-1 font-mono text-xl text-slate-700">
                    {{ $kelas->kode }}
                </span>
                Peserta Kelas
            </h1>

            <p class="mt-1 text-sm text-slate-500">{{ $kelas->nama_mk_snapshot }}</p>
        </div>

        <a href="{{ route('portal.dosen.kelas.show', $kelas->id) }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            &larr; Kembali ke Detail Kelas
        </a>
    </div>

    <!-- STATISTIK RINGKASAN -->
    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center shadow-sm">
            <span class="block text-2xl font-extrabold text-emerald-700">
                {{ number_format($jumlahAktif, 0, ',', '.') }}
            </span>
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Peserta Aktif</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
            <span class="block text-2xl font-extrabold text-slate-800">
                {{ number_format($jumlahRiwayat, 0, ',', '.') }}
            </span>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Riwayat Disahkan</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
            <span class="block text-2xl font-extrabold text-slate-800">
                {{ str_replace('.', ',', $kelas->sks_snapshot) }}
            </span>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">SKS</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm">
            <span class="block text-lg font-extrabold text-slate-800">
                {{ $kelas->rombel?->periodeAkademik?->kode ?? '—' }}
            </span>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Periode Akademik</span>
        </div>
    </div>

    <!-- KARTU PENCARIAN -->
    <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-800">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Cari Peserta
        </h2>

        <form method="get" action="{{ route('portal.dosen.peserta.index', $kelas->id) }}"
            class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full flex-1">
                <label for="q" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    NIM atau Nama Mahasiswa
                </label>

                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input id="q" type="search" name="q" maxlength="100" value="{{ $pencarian }}"
                        placeholder="Ketik NIM atau nama mahasiswa..."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:bg-white focus:ring-1 focus:ring-siakad-dark">
                </div>
            </div>

            <button type="submit"
                class="rounded-lg bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                Cari
            </button>

            <a href="{{ route('portal.dosen.peserta.index', $kelas->id) }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
                Reset
            </a>
        </form>

        @if ($pencarian !== '')
            <p class="mt-3 text-xs text-slate-500">
                Menampilkan hasil untuk
                <span class="font-semibold text-siakad-dark">&ldquo;{{ $pencarian }}&rdquo;</span>
            </p>
        @endif
    </div>

    <!-- KARTU TABEL PESERTA -->
    <div class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Daftar Peserta</h2>
            <span class="text-xs text-slate-500">
                {{ number_format($peserta->total(), 0, ',', '.') }} peserta tercatat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] border-collapse text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">No.</th>
                        <th scope="col" class="px-6 py-4 font-semibold">NIM</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Nama Mahasiswa</th>
                        <th scope="col" class="px-6 py-4 text-center font-semibold">Semester</th>
                        <th scope="col" class="px-6 py-4 text-center font-semibold">Status Registrasi</th>
                        <th scope="col" class="px-6 py-4 text-center font-semibold">Status KRS</th>
                        <th scope="col" class="px-6 py-4 text-center font-semibold">Status Peserta</th>
                        <th scope="col" class="px-6 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($peserta as $detail)
                        @php
                            $krs = $detail->krs;
                            $registrasi = $krs?->registrasiSemester;
                            $riwayat = $registrasi?->riwayatStudi;
                            $mahasiswa = $riwayat?->mahasiswa;
                            $akun = $mahasiswa?->user;

                            $labelRegistrasi = $registrasi
                                ? (\App\Models\RegistrasiSemester::STATUS[$registrasi->status] ?? $registrasi->status)
                                : null;
                            $labelKrs = $krs
                                ? (\App\Models\Krs::STATUS[$krs->status] ?? $krs->status)
                                : null;
                            $labelPeserta = \App\Models\DetailKrs::STATUS[$detail->status] ?? $detail->status;
                        @endphp

                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td class="px-6 py-4 font-mono text-slate-500">
                                {{ $peserta->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 font-mono font-semibold text-slate-800">
                                {{ $mahasiswa?->nim ?? '—' }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="block font-semibold text-slate-800">
                                    {{ $akun?->nama ?? '—' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                {{ $registrasi?->semester_studi ?? '—' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($labelRegistrasi)
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeStatus($labelRegistrasi) }}">
                                        {{ $labelRegistrasi }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($labelKrs)
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeStatus($labelKrs) }}">
                                        {{ $labelKrs }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeStatus($labelPeserta) }}">
                                    {{ $labelPeserta }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('portal.dosen.peserta.show', ['kelas' => $kelas->id, 'peserta' => $detail->id]) }}"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-siakad-dark transition-colors hover:text-emerald-500">
                                    Detail
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Belum ada peserta ditemukan</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($pencarian !== '')
                                        Tidak ada peserta yang cocok dengan pencarian
                                        &ldquo;{{ $pencarian }}&rdquo;. Coba kata kunci lain atau reset pencarian.
                                    @else
                                        Belum ada peserta disahkan yang tercatat pada kelas ini.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($peserta->hasPages())
            <div class="border-t border-slate-200 bg-slate-50/50 px-6 py-4">
                {{ $peserta->links() }}
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
                    Halaman ini hanya menampilkan identitas akademik yang diperlukan untuk kegiatan
                    pembelajaran. Alamat, tanggal lahir, nomor telepon, dan informasi pribadi lainnya
                    tidak ditampilkan.
                </p>
            </div>
        </div>
    </div>
@endsection
