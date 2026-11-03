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
                @if ($bolehCatat || $bolehSiapkanHistoris)
                    @if ($bolehSiapkanHistoris)
                        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            Pertemuan ini sudah selesai. Admin Akademik dapat menyiapkan daftar historis tanpa mengubah
                            tanggal pelaksanaan. Peserta tetap harus berasal dari KRS disahkan, dengan registrasi dan
                            riwayat studi aktif. Status kehadiran tidak diisi otomatis.
                        </div>
                    @else
                        <p class="text-sm text-slate-600 mb-4">Peserta diambil secara otomatis dari KRS yang disahkan,
                            dengan
                            status registrasi semester dan riwayat studi yang aktif saat tombol penyiapan ditekan. Pastikan
                            proses pengesahan KRS mahasiswa sudah selesai.</p>
                    @endif
                    <form action="{{ route('presensi.siapkan', $sesi) }}" method="post">
                        @csrf
                        <input type="hidden" name="form_presensi" value="siapkan">
                        <input type="hidden" name="versi_pertemuan"
                            value="{{ $teks('siapkan', 'versi_pertemuan', $versiSiapkan) }}">

                        @if ($bolehSiapkanHistoris)
                            <div class="mb-5">
                                <label for="alasan-siapkan-historis"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Alasan Penyiapan Historis <span class="text-rose-500">*</span>
                                </label>
                                <textarea id="alasan-siapkan-historis" name="alasan" required minlength="10" maxlength="2000" rows="3"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800">{{ $teks('siapkan', 'alasan') }}</textarea>
                                @error('alasan')
                                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

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
                        Penyiapan reguler tersedia saat pertemuan berlangsung pada kelas/periode aktif.
                        Penyiapan historis hanya tersedia bagi Admin Akademik yang berwenang.
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


        @if ($bolehSinkronHistoris)
            <div class="mb-8 overflow-hidden rounded-xl border border-amber-200 bg-white shadow-sm">
                <div class="border-b border-amber-200 bg-amber-50 px-6 py-4">
                    <h2 class="text-base font-bold text-amber-900">
                        Sinkronisasi Peserta Historis
                    </h2>
                </div>

                <div class="p-6">
                    <p class="mb-4 text-sm leading-6 text-slate-700">
                        Daftar presensi sudah tersedia. Sinkronisasi akan menambahkan
                        mahasiswa yang memenuhi persyaratan akademik tetapi belum terdaftar.
                        Status dan catatan peserta lama tidak akan diubah. Peserta baru
                        akan berstatus Belum Dicatat.
                    </p>

                    <form action="{{ route('presensi.siapkan', $sesi) }}" method="post">
                        @csrf

                        <input type="hidden" name="form_presensi" value="siapkan">
                        <input type="hidden" name="versi_pertemuan"
                            value="{{ $teks('siapkan', 'versi_pertemuan', $versiSiapkan) }}">

                        <div class="mb-5">
                            <label for="alasan-sinkronisasi-historis"
                                class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Alasan Sinkronisasi <span class="text-rose-500">*</span>
                            </label>

                            <textarea id="alasan-sinkronisasi-historis" name="alasan" required minlength="10" maxlength="2000" rows="3"
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800">{{ $teks('siapkan', 'alasan') }}</textarea>

                            @error('alasan')
                                <p class="mt-1 text-xs font-semibold text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark">
                                <span class="text-sm font-medium text-slate-700">
                                    Saya sudah memeriksa pengesahan KRS dan memastikan
                                    peserta tambahan memenuhi persyaratan akademik.
                                </span>
                            </label>
                        </div>

                        <button type="submit"
                            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors">
                            Sinkronkan Peserta Historis
                        </button>
                    </form>
                </div>
            </div>
        @endif


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

            @if ($bolehCatat && $daftar->status === \App\Models\PresensiPertemuan::TERBUKA)
                <div id="presensi-batch-panel" hidden data-endpoint="{{ route('presensi.batch-catat', $sesi) }}"
                    data-csrf="{{ csrf_token() }}"
                    class="mx-4 mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 sm:mx-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-emerald-950">Pencatatan presensi bertahap</p>
                            <p id="presensi-batch-count" class="mt-1 text-sm text-emerald-800" aria-live="polite">
                                Belum ada perubahan yang dipilih.
                            </p>
                        </div>
                        <button id="presensi-batch-send" type="button" disabled
                            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 disabled:cursor-not-allowed disabled:opacity-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                            <span id="presensi-batch-send-label">Kirim Semua</span>
                        </button>
                    </div>
                    <section id="presensi-batch-result" hidden
                        class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" role="status"
                        aria-live="polite" aria-atomic="true">
                        <div class="flex items-start gap-3">
                            <div id="presensi-batch-result-icon"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-lg font-bold text-slate-700"
                                aria-hidden="true">i</div>
                            <div class="min-w-0 flex-1">
                                <h3 id="presensi-batch-result-title" class="font-bold text-slate-900">Ringkasan pengiriman
                                </h3>
                                <p id="presensi-batch-message" class="mt-1 text-sm leading-6 text-slate-600"></p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-3">
                                <p id="presensi-batch-saved-count"
                                    class="text-2xl font-bold tabular-nums text-emerald-800">0</p>
                                <p class="mt-0.5 text-xs font-semibold text-emerald-900">Berhasil disimpan</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                                <p id="presensi-batch-unchanged-count"
                                    class="text-2xl font-bold tabular-nums text-slate-700">0</p>
                                <p class="mt-0.5 text-xs font-semibold text-slate-700">Sudah sesuai</p>
                            </div>
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-3">
                                <p id="presensi-batch-failed-count" class="text-2xl font-bold tabular-nums text-rose-800">
                                    0</p>
                                <p class="mt-0.5 text-xs font-semibold text-rose-900">Perlu diperiksa</p>
                            </div>
                        </div>

                        <details id="presensi-batch-errors-wrap" hidden
                            class="mt-4 rounded-xl border border-rose-200 bg-rose-50/60 p-3">
                            <summary class="cursor-pointer text-sm font-semibold text-rose-900">Lihat peserta yang belum
                                berhasil dan alasannya</summary>
                            <ul id="presensi-batch-errors" class="mt-3 space-y-2 text-sm text-rose-900"></ul>
                        </details>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button id="presensi-batch-reload" type="button" hidden
                                class="inline-flex min-h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Muat ulang data terbaru
                            </button>
                        </div>
                    </section>
                </div>
            @endif

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
                                    <span data-presensi-status
                                        class="inline-flex items-center rounded border px-2 py-1 text-xs font-bold uppercase tracking-wider mb-1 {{ $warnaStatus[$row->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                        {{ $row->labelStatus() }}
                                    </span>
                                    <span data-draft-indicator hidden
                                        class="mt-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                        Draft belum dikirim
                                    </span>
                                    <p data-row-batch-error hidden class="mt-1 text-xs font-medium text-rose-700"></p>
                                    <span data-presensi-note
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
                                            method="post" data-presensi-row data-row-id="{{ $row->id }}"
                                            data-row-name="{{ $row->peserta_snapshot['nama'] ?? 'Peserta' }}"
                                            data-initial-status="{{ $row->status }}"
                                            data-initial-status-label="{{ $row->labelStatus() }}"
                                            class="js-presensi-row-form flex flex-col gap-2">
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
                                            <button type="button" data-discard-draft hidden
                                                class="inline-flex items-center justify-center rounded border border-slate-300 bg-white px-2 py-1.5 text-[10px] font-semibold text-slate-600 hover:bg-slate-50">
                                                Batalkan pilihan
                                            </button>
                                        </form>
                                        <span data-saved-indicator hidden
                                            class="inline-flex items-center justify-center rounded border border-emerald-200 bg-emerald-50 px-2 py-1.5 text-xs font-semibold text-emerald-800">
                                            Tersimpan
                                        </span>
                                        @if ($bolehKoreksi)
                                            <a data-correction-link hidden
                                                href="{{ route('presensi.edit', ['pertemuan' => $sesi, 'presensi' => $row]) }}"
                                                class="mt-2 inline-flex w-full items-center justify-center rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors">
                                                Koreksi Status
                                            </a>
                                        @endif
                                    @elseif($bolehKoreksi && ($row->status !== \App\Models\Presensi::BELUM || ($adminKoreksi ?? false)))
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
            @if (($bolehCatat || $bolehTutupHistoris) && $daftar->status === \App\Models\PresensiPertemuan::TERBUKA)
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
                        <form id="form-tutup-presensi" action="{{ route('presensi.tutup', $sesi) }}" method="post">
                            @csrf
                            <input type="hidden" name="form_presensi" value="tutup">
                            <input type="hidden" name="versi_daftar"
                                value="{{ $teks('tutup', 'versi_daftar', $versiTutup) }}">

                            @if ($bolehTutupHistoris)
                                <div class="mb-5">
                                    <label for="alasan-tutup-historis"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-amber-900">
                                        Alasan Penutupan Historis <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea id="alasan-tutup-historis" name="alasan" required minlength="10" maxlength="2000" rows="3"
                                        class="w-full rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm text-slate-800">{{ $teks('tutup', 'alasan') }}</textarea>
                                    @error('alasan')
                                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

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

    @if ($bolehCatat && $daftar !== null && $daftar->status === \App\Models\PresensiPertemuan::TERBUKA)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const panel = document.getElementById('presensi-batch-panel');
                if (!panel) return;

                const sendButton = document.getElementById('presensi-batch-send');
                const sendLabel = document.getElementById('presensi-batch-send-label');
                const countLabel = document.getElementById('presensi-batch-count');
                const resultPanel = document.getElementById('presensi-batch-result');
                const resultIcon = document.getElementById('presensi-batch-result-icon');
                const resultTitle = document.getElementById('presensi-batch-result-title');
                const messageBox = document.getElementById('presensi-batch-message');
                const savedCount = document.getElementById('presensi-batch-saved-count');
                const unchangedCount = document.getElementById('presensi-batch-unchanged-count');
                const failedCount = document.getElementById('presensi-batch-failed-count');
                const errorWrap = document.getElementById('presensi-batch-errors-wrap');
                const errorList = document.getElementById('presensi-batch-errors');
                const reloadButton = document.getElementById('presensi-batch-reload');
                const forms = Array.from(document.querySelectorAll('form[data-presensi-row]'));
                const formById = new Map(forms.map(form => [String(form.dataset.rowId), form]));
                const drafts = new Map();
                const processed = new Set();
                let isSending = false;
                let mustReload = false;
                let allowUnload = false;

                const labels = {
                    hadir: 'Hadir',
                    izin: 'Izin',
                    sakit: 'Sakit',
                    alpa: 'Alpa'
                };
                const statusStyles = {
                    hadir: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    izin: 'bg-blue-50 text-blue-700 border-blue-200',
                    sakit: 'bg-amber-50 text-amber-700 border-amber-200',
                    alpa: 'bg-rose-50 text-rose-700 border-rose-200'
                };
                const initialStatusStyle = 'bg-slate-100 text-slate-600 border-slate-200';
                const baseBadgeClass =
                    'inline-flex items-center rounded border px-2 py-1 text-xs font-bold uppercase tracking-wider mb-1';

                panel.hidden = false;
                reloadButton.addEventListener('click', () => {
                    // Reload ini memang disengaja; jangan munculkan prompt beforeunload untuk draft stale.
                    allowUnload = true;
                    window.location.reload();
                });

                function showMessage(text, type, counts = {}) {
                    resultPanel.hidden = false;
                    messageBox.textContent = text;
                    resultPanel.className = 'mt-4 rounded-2xl border bg-white p-4 shadow-sm sm:p-5';
                    resultIcon.className =
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-lg font-bold';

                    if (type === 'success') {
                        resultPanel.classList.add('border-emerald-200');
                        resultIcon.classList.add('bg-emerald-100', 'text-emerald-800');
                        resultIcon.textContent = '✓';
                        resultTitle.textContent = 'Presensi berhasil diproses';
                        messageBox.className = 'mt-1 text-sm leading-6 text-emerald-900';
                    } else if (type === 'error') {
                        resultPanel.classList.add('border-rose-200');
                        resultIcon.classList.add('bg-rose-100', 'text-rose-800');
                        resultIcon.textContent = '!';
                        resultTitle.textContent = 'Sebagian perubahan perlu diperiksa';
                        messageBox.className = 'mt-1 text-sm leading-6 text-rose-900';
                    } else {
                        resultPanel.classList.add('border-amber-200');
                        resultIcon.classList.add('bg-amber-100', 'text-amber-800');
                        resultIcon.textContent = 'i';
                        resultTitle.textContent = 'Perhatian';
                        messageBox.className = 'mt-1 text-sm leading-6 text-amber-900';
                    }

                    if (counts.saved !== undefined) savedCount.textContent = String(counts.saved);
                    if (counts.unchanged !== undefined) unchangedCount.textContent = String(counts.unchanged);
                    if (counts.failed !== undefined) failedCount.textContent = String(counts.failed);
                }

                function setRowError(form, text) {
                    const row = form.closest('tr');
                    const error = row?.querySelector('[data-row-batch-error]');
                    if (!error) return;
                    error.textContent = text;
                    error.hidden = !text;
                }

                function updateRow(form, draft) {
                    const row = form.closest('tr');
                    if (!row) return;
                    if (form.hidden) return;
                    if (processed.has(String(form.dataset.rowId))) return;
                    const badge = row.querySelector('[data-presensi-status]');
                    const draftIndicator = row.querySelector('[data-draft-indicator]');
                    const note = row.querySelector('[data-presensi-note]');
                    const discard = form.querySelector('[data-discard-draft]');
                    const buttons = Array.from(form.querySelectorAll('button[type="submit"][name="status"]'));

                    if (badge) {
                        if (!form.dataset.originalBadgeClass) form.dataset.originalBadgeClass = badge.className;
                        if (draft) {
                            badge.className = baseBadgeClass + ' ' + (statusStyles[draft.status] || initialStatusStyle);
                            badge.textContent = labels[draft.status] || draft.status;
                        } else {
                            badge.className = form.dataset.originalBadgeClass;
                            badge.textContent = form.dataset.initialStatusLabel || 'Belum dicatat';
                        }
                    }
                    if (draftIndicator) {
                        draftIndicator.hidden = !draft;
                        draftIndicator.textContent = 'Belum dikirim';
                        draftIndicator.className =
                            'mt-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800';
                        draftIndicator.removeAttribute('data-draft');
                    }
                    if (note) {
                        note.textContent = draft?.catatan || 'Tidak ada catatan';
                    }
                    if (discard) discard.hidden = !draft;

                    buttons.forEach(button => {
                        const selected = Boolean(draft && button.value === draft.status);
                        button.classList.toggle('ring-2', selected);
                        button.classList.toggle('ring-offset-1', selected);
                        button.classList.toggle('ring-slate-800', selected);
                        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                    });
                    setRowError(form, '');
                }

                function renderPanel() {
                    const count = drafts.size;
                    countLabel.textContent = count === 0 ?
                        'Belum ada perubahan yang dipilih.' :
                        count + ' perubahan belum dikirim ke database.';
                    sendButton.disabled = isSending || mustReload || count === 0;
                    sendLabel.textContent = isSending ? 'Mengirim…' : 'Kirim Semua (' + count + ')';
                }

                function setFormBusy(form, busy) {
                    form.querySelectorAll('button[type="submit"], [data-discard-draft], input[name="catatan"]').forEach(
                        control => {
                            control.disabled = busy || mustReload;
                        });
                }

                forms.forEach(form => {
                    const noteInput = form.querySelector('input[name="catatan"]');
                    const discardButton = form.querySelector('[data-discard-draft]');

                    form.querySelectorAll('button[type="submit"][name="status"]').forEach(button => {
                        button.setAttribute('aria-pressed', 'false');
                    });

                    form.addEventListener('submit', function(event) {
                        // Dengan JavaScript aktif, klik status hanya membuat/mengubah draft.
                        // Tanpa JavaScript, form lama tetap dapat dikirim langsung sebagai fallback.
                        event.preventDefault();
                        if (isSending || mustReload) return;
                        const trigger = event.submitter;
                        if (!trigger || !trigger.name || trigger.name !== 'status') return;

                        const id = String(form.dataset.rowId);
                        const version = form.querySelector('input[name="versi_presensi"]')?.value;
                        if (!version) {
                            showMessage(
                                'Token versi tidak ditemukan untuk salah satu peserta. Muat ulang halaman.',
                                'error');
                            mustReload = true;
                            renderPanel();
                            reloadButton.hidden = false;
                            return;
                        }

                        const draft = {
                            id: Number(id),
                            status: trigger.value,
                            catatan: noteInput?.value.trim() || null,
                            versi_presensi: version,
                            nama: form.dataset.rowName || 'Peserta'
                        };
                        drafts.set(id, draft);
                        updateRow(form, draft);
                        renderPanel();
                    });

                    noteInput?.addEventListener('input', function() {
                        const draft = drafts.get(String(form.dataset.rowId));
                        if (!draft || isSending || mustReload) return;
                        draft.catatan = noteInput.value.trim() || null;
                        drafts.set(String(form.dataset.rowId), draft);
                        updateRow(form, draft);
                    });

                    discardButton?.addEventListener('click', function() {
                        if (isSending || mustReload) return;
                        drafts.delete(String(form.dataset.rowId));
                        updateRow(form, null);
                        renderPanel();
                    });
                });

                const closeForm = document.getElementById('form-tutup-presensi');
                closeForm?.addEventListener('submit', function(event) {
                    if (drafts.size > 0) {
                        event.preventDefault();
                        showMessage('Masih ada ' + drafts.size +
                            ' perubahan yang belum dikirim. Kirim atau batalkan draft sebelum menutup presensi.',
                            'error');
                        panel.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                });

                window.addEventListener('beforeunload', function(event) {
                    if (drafts.size > 0 && !isSending && !allowUnload) {
                        event.preventDefault();
                        event.returnValue = '';
                    }
                });

                sendButton.addEventListener('click', async function() {
                    if (drafts.size === 0 || isSending || mustReload) return;

                    isSending = true;
                    resultPanel.hidden = true;
                    errorWrap.hidden = true;
                    errorList.replaceChildren();
                    reloadButton.hidden = true;
                    forms.forEach(form => setFormBusy(form, true));
                    renderPanel();

                    const sentDrafts = new Map(Array.from(drafts.entries()).map(([id, draft]) => [id, {
                        ...draft
                    }]));
                    const items = Array.from(sentDrafts.values()).map(({
                        id,
                        status,
                        catatan,
                        versi_presensi
                    }) => ({
                        id,
                        status,
                        catatan,
                        versi_presensi
                    }));

                    try {
                        const response = await fetch(panel.dataset.endpoint, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': panel.dataset.csrf,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                items
                            })
                        });

                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            if (payload.needs_refresh === true) {
                                mustReload = true;
                                reloadButton.hidden = false;
                            }
                            const validationMessages = payload.errors ?
                                Object.values(payload.errors).flat().join(' ') :
                                '';
                            const requestError = new Error(validationMessages || payload.message ||
                                'Pengiriman gagal. Draft tetap tersedia di halaman ini.');
                            requestError.needsRefresh = payload.needs_refresh === true;
                            throw requestError;
                        }

                        const results = Array.isArray(payload.results) ? payload.results : [];
                        // Validasi kontrak dilakukan sebelum menyentuh draft/DOM. Kalau respons parsial
                        // atau rusak, catch akan memulihkan seluruh draft dan retry aman untuk baris identik.
                        const resultIds = results.map(item => String(item?.id ?? ''));
                        const resultIdSet = new Set(resultIds);
                        const validResponse = results.length === sentDrafts.size &&
                            resultIdSet.size === results.length &&
                            resultIds.every(id => sentDrafts.has(id)) &&
                            results.every(item => typeof item?.success === 'boolean');
                        if (!validResponse) {
                            throw new Error(
                                'Rincian hasil dari server tidak lengkap atau tidak valid. Draft dipertahankan; muat ulang halaman sebelum mencoba lagi jika status database belum pasti.'
                            );
                        }

                        // API mengembalikan `success`, `changed`, `code`, dan `needs_refresh`.
                        const failed = results.filter(item => item.success !== true);
                        const succeeded = results.filter(item => item.success === true);
                        const saved = Number.isFinite(Number(payload.saved)) ?
                            Number(payload.saved) :
                            succeeded.filter(item => item.changed === true).length;
                        const unchanged = Number.isFinite(Number(payload.unchanged)) ?
                            Number(payload.unchanged) :
                            succeeded.filter(item => item.changed !== true).length;

                        // Kode stabil berasal dari backend; tidak bergantung pada teks pesan/bahasa.
                        const needsRefresh = payload.needs_refresh === true || failed.some(item =>
                            item.needs_refresh === true || ['stale_version', 'not_found',
                                'already_recorded',
                                'list_not_found', 'meeting_inactive', 'presensi_closed'
                            ].includes(item.code)
                        );
                        mustReload = needsRefresh;

                        results.forEach(result => {
                            const id = String(result.id);
                            const form = formById.get(id);
                            if (!form) return;
                            const row = form.closest('tr');
                            const badge = row?.querySelector('[data-presensi-status]');
                            const draftIndicator = row?.querySelector('[data-draft-indicator]');
                            const note = row?.querySelector('[data-presensi-note]');
                            const savedIndicator = row?.querySelector('[data-saved-indicator]');
                            const sentDraft = sentDrafts.get(id);

                            if (result.success === true) {
                                processed.add(id);
                                if (badge && sentDraft) {
                                    badge.className = baseBadgeClass + ' ' + (statusStyles[sentDraft
                                        .status] || initialStatusStyle);
                                    badge.textContent = labels[sentDraft.status] || sentDraft
                                        .status;
                                }
                                if (draftIndicator) {
                                    draftIndicator.hidden = false;
                                    if (result.changed === true) {
                                        draftIndicator.textContent = 'Tersimpan';
                                        draftIndicator.className =
                                            'mt-1 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800';
                                    } else {
                                        draftIndicator.textContent = 'Sudah sesuai';
                                        draftIndicator.className =
                                            'mt-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700';
                                    }
                                    draftIndicator.removeAttribute('data-draft');
                                }
                                if (note && sentDraft) note.textContent = sentDraft.catatan ||
                                    'Tidak ada catatan';
                                form.hidden = true;
                                if (savedIndicator) savedIndicator.hidden = false;
                                const correctionLink = row?.querySelector('[data-correction-link]');
                                if (correctionLink) correctionLink.hidden = false;
                                setRowError(form, '');
                                return;
                            }

                            // Perubahan yang gagal tetap menjadi draft agar bisa diperbaiki/dikirim ulang.
                            if (sentDraft) drafts.set(id, sentDraft);
                            updateRow(form, sentDraft || null);
                            setRowError(form, result.message ||
                                'Presensi belum berhasil disimpan.');
                        });

                        failed.forEach(item => {
                            const id = String(item.id);
                            const form = formById.get(id);
                            const draft = sentDrafts.get(id);
                            const li = document.createElement('li');
                            li.className = 'rounded-lg border border-rose-200 bg-white px-3 py-2';
                            const name = document.createElement('p');
                            name.className = 'font-semibold text-rose-950';
                            name.textContent = draft?.nama || form?.dataset.rowName || (
                                'Peserta ID ' + id);
                            const reason = document.createElement('p');
                            reason.className = 'mt-0.5 text-rose-800';
                            reason.textContent = item.message ||
                                'Presensi belum berhasil disimpan.';
                            li.append(name, reason);
                            errorList.appendChild(li);
                        });

                        const counts = {
                            saved,
                            unchanged,
                            failed: failed.length
                        };
                        if (failed.length === 0) {
                            showMessage(
                                unchanged > 0 ?
                                'Semua permintaan telah diperiksa. Data yang berubah sudah disimpan; data yang sudah sesuai tidak ditulis ulang.' :
                                'Semua perubahan berhasil disimpan ke database. Tampilan status telah diperbarui.',
                                'success', counts
                            );
                        } else if (needsRefresh) {
                            showMessage(
                                saved + ' perubahan berhasil disimpan. ' + failed.length +
                                ' belum berhasil karena data mungkin sudah berubah. Muat ulang daftar sebelum melanjutkan.',
                                'error', counts
                            );
                            reloadButton.hidden = false;
                        } else {
                            showMessage(
                                saved + ' berhasil disimpan, ' + unchanged + ' sudah sesuai, dan ' +
                                failed.length +
                                ' perlu diperbaiki. Perubahan yang gagal tetap menjadi draft sehingga bisa dicoba kembali.',
                                'error', counts
                            );
                        }

                        errorWrap.hidden = failed.length === 0;
                        reloadButton.hidden = !needsRefresh;

                        // Commit draft baru setelah semua pemrosesan hasil dan DOM selesai.
                        // Jika ada exception sebelumnya, backup di catch akan memulihkan seluruh pilihan.
                        drafts.clear();
                        failed.forEach(item => {
                            const id = String(item.id);
                            const sentDraft = sentDrafts.get(id);
                            if (sentDraft) drafts.set(id, {
                                ...sentDraft
                            });
                        });
                    } catch (error) {
                        // Respons/network/DOM error tidak boleh menghapus pilihan yang dibuat dosen.
                        drafts.clear();
                        sentDrafts.forEach((draft, id) => drafts.set(id, {
                            ...draft
                        }));
                        if (error?.needsRefresh || mustReload) {
                            mustReload = true;
                            reloadButton.hidden = false;
                        }
                        showMessage(
                            error.message ||
                            'Terjadi kesalahan saat mengirim presensi. Draft dipulihkan di halaman ini.',
                            'error', {
                                saved: 0,
                                unchanged: 0,
                                failed: drafts.size
                            }
                        );
                    } finally {
                        isSending = false;
                        forms.forEach(form => setFormBusy(form, false));
                        renderPanel();
                    }
                });
                renderPanel();
            });
        </script>
    @endif

@endsection
