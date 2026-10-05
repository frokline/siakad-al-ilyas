@extends('layouts.admin')
@section('title', 'Presensi Pertemuan ' . $sesi->nomor)

@section('content')
    @php
        $teks = static function (string $form, string $key, string $fallback = ''): string {
            if (old('form_presensi') !== $form || !session()->hasOldInput($key)) {
                return $fallback;
            }
            return is_string(old($key)) ? old($key) : '';
        };

        $warnaStatus = [
            'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'izin' => 'bg-blue-50 text-blue-700 border-blue-200',
            'sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
            'alpa' => 'bg-rose-50 text-rose-700 border-rose-200',
            'belum' => 'bg-slate-100 text-slate-600 border-slate-200',
        ];
    @endphp

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Pertemuan {{ $sesi->nomor }}</h1>
            <p class="text-sm text-slate-500 mt-1 font-mono">
                {{ $sesi->kelasKuliah->kode }} &middot; <span
                    class="font-sans">{{ $sesi->kelasKuliah->nama_mk_snapshot }}</span>
            </p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('presensi.index') }}">
            &larr; Daftar Seluruh Pertemuan
        </a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="p-6">
            <div class="flex flex-col md:flex-row justify-between md:items-start gap-4 pb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-1">{{ $sesi->topik }}</h2>
                    <div class="text-sm text-slate-600">Rencana: <span
                            class="font-mono">{{ $sesi->mulai_rencana->setTimezone($zona)->format('d-m-Y H:i') }} &ndash;
                            {{ $sesi->selesai_rencana->setTimezone($zona)->format('H:i') }}</span> ({{ $zona }})
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 uppercase tracking-widest shadow-sm">
                        Status: <span
                            class="{{ $sesi->status === 'berlangsung' ? 'text-blue-600' : ($sesi->status === 'selesai' ? 'text-emerald-600' : '') }} ml-1">{{ ucfirst($sesi->status) }}</span>
                    </span>
                    <a href="{{ route('presensi.show', $sesi) }}"
                        class="inline-flex items-center justify-center rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200 transition-colors shadow-sm"
                        title="Muat ulang halaman">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                @if ($sesi->mulai_aktual)
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Mulai
                            Aktual</span>
                        <span
                            class="font-mono font-medium text-slate-700">{{ $sesi->mulai_aktual->setTimezone($zona)->format('d-m-Y H:i:s') }}</span>
                    </div>
                @endif
                @if ($sesi->selesai_aktual)
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Selesai
                            Aktual</span>
                        <span
                            class="font-mono font-medium text-slate-700">{{ $sesi->selesai_aktual->setTimezone($zona)->format('d-m-Y H:i:s') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($sesi->status === \App\Models\Pertemuan::TERJADWAL && $bolehJalankan)
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 overflow-hidden w-full mb-8 shadow-sm">
            <div class="p-6">
                <div class="flex items-center gap-2 text-emerald-800 mb-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-base font-bold">Mulai Pertemuan</h2>
                </div>
                <p class="text-sm text-emerald-700 mb-4">Sistem mendeteksi bahwa pertemuan ini dapat dimulai dalam rentang
                    waktu yang telah direncanakan.</p>

                <form action="{{ route('presensi.mulai', $sesi) }}" method="post">
                    @csrf
                    <input type="hidden" name="form_presensi" value="mulai">
                    <input type="hidden" name="versi_pertemuan"
                        value="{{ $teks('mulai', 'versi_pertemuan', $versiSesi) }}">

                    <div class="mb-4">
                        <label for="alasan-mulai"
                            class="block text-xs font-bold text-emerald-800 mb-2 uppercase tracking-wider">Catatan Mulai
                            <span class="text-rose-500">*</span></label>
                        <textarea id="alasan-mulai" name="alasan" required minlength="10" maxlength="2000" rows="2"
                            class="w-full rounded-lg border border-emerald-300 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">{{ $teks('mulai', 'alasan') }}</textarea>
                    </div>

                    <div class="mb-5">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="konfirmasi" value="1" required
                                class="h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-600">
                            <span class="text-sm font-medium text-emerald-800">Saya siap memulai sesi perkuliahan ini
                                sekarang.</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                        Mulai Pertemuan
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if ($daftar === null)
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Daftar Peserta Belum Disiapkan</h2>
            </div>
            <div class="p-6">
                @if ($bolehCatat)
                    <p class="text-sm text-slate-600 mb-4">Peserta diambil secara otomatis dari KRS yang disahkan, dengan
                        status registrasi semester dan riwayat studi yang aktif saat tombol penyiapan ditekan. Pastikan
                        proses pengesahan KRS mahasiswa sudah selesai.</p>
                    <form action="{{ route('presensi.siapkan', $sesi) }}" method="post">
                        @csrf
                        <input type="hidden" name="form_presensi" value="siapkan">
                        <input type="hidden" name="versi_pertemuan"
                            value="{{ $teks('siapkan', 'versi_pertemuan', $versiSiapkan) }}">

                        <div class="mb-5">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark">
                                <span class="text-sm font-medium text-slate-700">Saya sudah memeriksa dan memastikan
                                    kesiapan data peserta kelas.</span>
                            </label>
                        </div>

                        <button type="submit"
                            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors">
                            Generate / Siapkan Daftar Peserta
                        </button>
                    </form>
                @else
                    <div
                        class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center gap-3">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Tombol penyiapan daftar hadir baru akan tersedia saat pertemuan berstatus
                        <strong>Berlangsung</strong> dan berada dalam masa aktif kelas/periode.
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800">Resume Status Presensi</h2>
                <span
                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-700 uppercase tracking-widest shadow-sm">
                    Status Form: <span
                        class="{{ $daftar->status === 'terbuka' ? 'text-emerald-600' : 'text-slate-500' }} ml-1">{{ ucfirst($daftar->status) }}</span>
                </span>
            </div>

            <div class="p-6">
                <div class="flex flex-col sm:flex-row gap-8 mb-6 pb-6 border-b border-slate-100">
                    <div class="flex-1 text-sm text-slate-600 space-y-2">
                        <p><strong class="text-slate-800 text-base">{{ $daftar->jumlah_peserta }}</strong> Peserta
                            Terdaftar</p>
                        <p>Dibuka oleh <strong class="text-slate-800">{{ $daftar->pembuka?->nama ?? 'Sistem' }}</strong>
                            pada <span
                                class="font-mono text-xs">{{ $daftar->dibuka_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</span>
                        </p>
                        @if ($daftar->ditutup_at)
                            <p>Ditutup oleh <strong
                                    class="text-slate-800">{{ $daftar->penutup?->nama ?? 'Sistem' }}</strong> pada <span
                                    class="font-mono text-xs">{{ $daftar->ditutup_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</span>
                            </p>
                        @endif
                    </div>
                    <div class="flex-1 bg-slate-50 border border-slate-200 rounded-lg p-4">
                        @php $persenHadir = $daftar->jumlah_peserta > 0 ? (100 * $ringkasan['hadir']) / $daftar->jumlah_peserta : 0; @endphp
                        <div class="flex items-end justify-between mb-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tingkat
                                Kehadiran</span>
                            <span
                                class="text-xl font-black text-emerald-600">{{ number_format($persenHadir, 1, ',', '.') }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 mb-2">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $persenHadir }}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 text-center"><strong
                                class="text-slate-700">{{ $ringkasan['hadir'] }}</strong> dari
                            {{ $daftar->jumlah_peserta }} peserta hadir.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    @foreach (\App\Models\Presensi::STATUS as $status => $label)
                        <div
                            class="rounded-lg border border-slate-200 p-4 flex flex-col items-center justify-center bg-white shadow-sm hover:shadow-md transition-shadow">
                            <span class="text-2xl font-black text-slate-800 mb-1">{{ $ringkasan[$status] }}</span>
                            <span
                                class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-[10px] text-slate-400 italic text-center">Catatan: Peserta berstatus 'Belum' tetap
                    dihitung sebagai pembagi dalam jumlah total peserta. Nama dan NIM di-snapshot saat form ini dibuat,
                    perubahan KRS setelahnya tidak merubah rekaman.</p>
            </div>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 p-4">
                <form method="get" action="{{ route('presensi.show', $sesi) }}"
                    class="flex flex-col sm:flex-row sm:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Filter Status
                            Peserta</label>
                        <select name="status"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800 outline-none focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark">
                            <option value="">Semua peserta (Tanpa Filter)</option>
                            @foreach (\App\Models\Presensi::STATUS as $status => $label)
                                <option value="{{ $status }}" @selected(($filter['status'] ?? '') === $status)>Hanya tampilkan:
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">Filter</button>
                        <a href="{{ route('presensi.show', $sesi) }}"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold w-1/4">Mahasiswa</th>
                            <th scope="col" class="px-6 py-4 font-semibold w-1/4">Status & Catatan</th>
                            <th scope="col" class="px-6 py-4 font-semibold w-1/4">Log Pencatatan</th>
                            <th scope="col" class="px-6 py-4 font-semibold w-1/4">Panel Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($baris as $row)
                            <tr id="peserta-{{ $row->id }}" class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <strong
                                        class="font-bold text-slate-800 block text-sm">{{ $row->peserta_snapshot['nama'] ?? 'Nama tidak tersedia' }}</strong>
                                    <span
                                        class="text-xs text-slate-500 font-mono mt-0.5 block">{{ $row->peserta_snapshot['nim'] ?? 'NIM tidak tersedia' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded border px-2 py-1 text-xs font-bold uppercase tracking-wider mb-1 {{ $warnaStatus[$row->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $row->labelStatus() }}
                                    </span>
                                    <span
                                        class="text-xs text-slate-500 block whitespace-pre-line italic">{{ $row->catatan ?? 'Tidak ada catatan' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-700 text-xs">
                                        {{ $row->pencatat?->nama ?? 'Sistem / Belum dicatat' }}</div>
                                    <span
                                        class="text-[10px] text-slate-400 font-mono mt-1 block">{{ $row->dicatat_at?->setTimezone($zona)->format('d-m-Y H:i:s') ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($bolehCatat && $row->status === \App\Models\Presensi::BELUM)
                                        <form
                                            action="{{ route('presensi.catat', ['pertemuan' => $sesi, 'presensi' => $row]) }}"
                                            method="post" class="flex flex-col gap-2">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="form_presensi"
                                                value="baris-{{ $row->id }}">
                                            <input type="hidden" name="versi_presensi"
                                                value="{{ $teks('baris-' . $row->id, 'versi_presensi', $row->versiForm()) }}">
                                            <input type="hidden" name="halaman" value="{{ $baris->currentPage() }}">
                                            <input type="hidden" name="filter_status"
                                                value="{{ $filter['status'] ?? '' }}">

                                            <label class="sr-only" for="catatan-{{ $row->id }}">Catatan untuk
                                                {{ $row->peserta_snapshot['nama'] ?? 'Peserta' }}</label>
                                            <input id="catatan-{{ $row->id }}" name="catatan" maxlength="1000"
                                                placeholder="Ketik catatan opsional..."
                                                value="{{ $teks('baris-' . $row->id, 'catatan') }}"
                                                class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-800 outline-none focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark">

                                            <div class="flex flex-wrap gap-1">
                                                @foreach (\App\Models\Presensi::PILIHAN as $status => $label)
                                                    @php
                                                        $btnClass = match ($status) {
                                                            'hadir'
                                                                => 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border-emerald-200',
                                                            'izin'
                                                                => 'bg-blue-100 text-blue-800 hover:bg-blue-200 border-blue-200',
                                                            'sakit'
                                                                => 'bg-amber-100 text-amber-800 hover:bg-amber-200 border-amber-200',
                                                            'alpa'
                                                                => 'bg-rose-100 text-rose-800 hover:bg-rose-200 border-rose-200',
                                                            default
                                                                => 'bg-slate-100 text-slate-800 hover:bg-slate-200 border-slate-200',
                                                        };
                                                    @endphp
                                                    <button type="submit" name="status" value="{{ $status }}"
                                                        class="flex-1 rounded border px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider transition-colors {{ $btnClass }}">
                                                        {{ $label }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </form>
                                    @elseif($bolehKoreksi && $row->status !== \App\Models\Presensi::BELUM)
                                        <a href="{{ route('presensi.edit', ['pertemuan' => $sesi, 'presensi' => $row]) }}"
                                            class="inline-flex w-full items-center justify-center rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors mb-2 shadow-sm">
                                            Koreksi Status
                                        </a>
                                    @endif

                                    <div class="mt-2 text-right">
                                        <a href="{{ route('presensi.audit', ['pertemuan' => $sesi, 'presensi' => $row]) }}"
                                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400 hover:text-siakad-dark hover:underline flex items-center justify-end gap-1">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Lihat Log
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 italic">Tidak ada peserta
                                    yang sesuai dengan filter saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('presensi._pagination', ['paginator' => $baris])
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @if ($bolehCatat && $daftar->status === \App\Models\PresensiPertemuan::TERBUKA)
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-6 shadow-sm">
                    <h2 class="text-base font-bold text-amber-800 mb-2">Tutup Sesi Presensi Kelas</h2>
                    @if ($ringkasan[\App\Models\Presensi::BELUM] > 0)
                        <div class="rounded-lg bg-white/60 border border-amber-200 p-4 text-sm font-medium text-amber-700">
                            Masih terdapat <strong class="text-amber-900">{{ $ringkasan[\App\Models\Presensi::BELUM] }}
                                peserta</strong> yang belum dicatat kehadirannya. Silakan periksa seluruh halaman form tabel
                            di atas.
                        </div>
                    @else
                        <p class="text-sm text-amber-700 mb-4">Pastikan data seluruh peserta valid sebelum ditutup.</p>
                        <form action="{{ route('presensi.tutup', $sesi) }}" method="post">
                            @csrf
                            <input type="hidden" name="form_presensi" value="tutup">
                            <input type="hidden" name="versi_daftar"
                                value="{{ $teks('tutup', 'versi_daftar', $versiTutup) }}">

                            <label class="flex items-start gap-3 cursor-pointer mb-5">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="mt-0.5 h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-600">
                                <span class="text-sm font-medium text-amber-900">Seluruh peserta sudah saya periksa. Saya
                                    memahami bahwa koreksi form setelah ditutup hanya dapat dilakukan oleh admin
                                    akademik.</span>
                            </label>

                            <button type="submit"
                                class="w-full rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition-colors">Tutup
                                Sesi Presensi</button>
                        </form>
                    @endif
                </div>
            @endif

            @if (
                $bolehJalankan &&
                    $sesi->status === \App\Models\Pertemuan::BERLANGSUNG &&
                    $daftar->status === \App\Models\PresensiPertemuan::DITUTUP)
                <div class="rounded-xl bg-blue-50 border border-blue-200 p-6 shadow-sm">
                    <h2 class="text-base font-bold text-blue-800 mb-2">Finalisasi & Selesaikan Pertemuan</h2>
                    <p class="text-sm text-blue-700 mb-4">Sesi presensi telah ditutup. Silakan isi form realisasi
                        perkuliahan berikut untuk menyelesaikan sesi.</p>

                    <form action="{{ route('presensi.selesai', $sesi) }}" method="post">
                        @csrf
                        <input type="hidden" name="form_presensi" value="selesai">
                        <input type="hidden" name="versi_pertemuan"
                            value="{{ $teks('selesai', 'versi_pertemuan', $versiSesi) }}">

                        <div class="mb-4">
                            <label for="realisasi"
                                class="block text-xs font-bold text-blue-800 mb-1.5 uppercase tracking-wider">Realisasi
                                Perkuliahan <span class="text-rose-500">*</span></label>
                            <textarea id="realisasi" name="realisasi" rows="3" required minlength="10" maxlength="20000"
                                class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ $teks('selesai', 'realisasi') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="alasan-selesai"
                                class="block text-xs font-bold text-blue-800 mb-1.5 uppercase tracking-wider">Catatan
                                Penyelesaian <span class="text-rose-500">*</span></label>
                            <textarea id="alasan-selesai" name="alasan" rows="2" required minlength="10" maxlength="2000"
                                class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ $teks('selesai', 'alasan') }}</textarea>
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer mb-5">
                            <input type="checkbox" name="konfirmasi" value="1" required
                                class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-600">
                            <span class="text-sm font-medium text-blue-900">Pertemuan ini sudah selesai dilaksanakan secara
                                utuh.</span>
                        </label>

                        <button type="submit"
                            class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">Selesaikan
                            Pertemuan Ini</button>
                    </form>
                </div>
            @endif
        </div>
    @endif
@endsection
