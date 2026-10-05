@php
    $zona = config('siakad.timezone', 'Asia/Makassar');
    // Cerminan syarat "mulai" di KelolaPertemuan: kelas & periode aktif dan waktu berada dalam rentang rencana.
    $sekarang = \Carbon\CarbonImmutable::now('UTC');
    $kelasPeriodeAktif =
        $sesi->kelasKuliah->status === \App\Models\KelasKuliah::AKTIF &&
        $sesi->kelasKuliah->rombel->periodeAkademik->status === 'aktif';
    $bisaMulai = $kelasPeriodeAktif && $sekarang->gte($sesi->mulai_rencana) && $sekarang->lt($sesi->selesai_rencana);
@endphp

<div class="space-y-6">
    @if ($sesi->status === \App\Models\Pertemuan::TERJADWAL && $sesi->konteksTerbuka())
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <div class="flex items-center gap-2 text-emerald-800 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-sm font-bold">Mulai Sesi Pertemuan</h3>
            </div>
            @unless ($bisaMulai)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                    @if (!$kelasPeriodeAktif)
                        Kelas dan periode akademik harus berstatus <strong>Aktif</strong> untuk memulai sesi.
                    @elseif ($sekarang->lt($sesi->mulai_rencana))
                        Sesi baru dapat dimulai pada
                        <strong>{{ $sesi->mulai_rencana->setTimezone($zona)->format('d-m-Y H:i') }}</strong>
                        ({{ $zona }}). Muat ulang halaman ini saat waktunya tiba.
                    @else
                        Rentang waktu rencana sesi sudah lewat
                        ({{ $sesi->selesai_rencana->setTimezone($zona)->format('d-m-Y H:i') }}).
                        Bila pelaksanaan bergeser, perbaiki jam lewat
                        <a class="font-semibold underline" href="{{ route('admin.pertemuan.edit', $sesi) }}">Edit
                            Rencana</a>.
                    @endif
                </div>
            @endunless
            <form method="POST" action="{{ route('admin.pertemuan.mulai', $sesi) }}">
                @csrf
                <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
                <div class="mb-4">
                    <label for="alasan_mulai"
                        class="block text-xs font-bold text-emerald-800 mb-1.5 uppercase tracking-wider">Catatan Mulai
                        <span class="text-rose-500">*</span></label>
                    <input id="alasan_mulai" name="alasan" type="text" minlength="10" maxlength="2000" required
                        placeholder="Contoh: perkuliahan dimulai sesuai jadwal"
                        class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer" for="konfirmasi_mulai">
                        <input id="konfirmasi_mulai" name="konfirmasi" type="checkbox" value="1" required
                            class="h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-600">
                        <span class="text-sm font-medium text-emerald-800">Saya mengonfirmasi sesi dimulai
                            sekarang.</span>
                    </label>
                </div>
                <button
                    class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                    type="submit" @disabled(!$bisaMulai)>Mulai Sesi</button>
            </form>
        </div>
    @endif

    @if ($sesi->status === \App\Models\Pertemuan::BERLANGSUNG)
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <div class="flex items-center gap-2 text-blue-800 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <h3 class="text-sm font-bold">Selesaikan Sesi Pertemuan</h3>
            </div>
            <form method="POST" action="{{ route('admin.pertemuan.selesai', $sesi) }}">
                @csrf
                <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
                <div class="mb-4">
                    <label for="realisasi"
                        class="block text-xs font-bold text-blue-800 mb-1.5 uppercase tracking-wider">Catatan Realisasi
                        Pembelajaran <span class="text-rose-500">*</span></label>
                    <textarea id="realisasi" name="realisasi" rows="4" minlength="10" maxlength="20000" required
                        class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('realisasi') }}</textarea>
                    @error('realisasi')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="alasan_selesai"
                        class="block text-xs font-bold text-blue-800 mb-1.5 uppercase tracking-wider">Alasan
                        Penyelesaian <span class="text-rose-500">*</span></label>
                    <input id="alasan_selesai" name="alasan" type="text" minlength="10" maxlength="2000" required
                        placeholder="Contoh: materi selesai dibahas"
                        class="w-full rounded-lg border border-blue-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="mb-5">
                    <label class="flex items-center gap-2 cursor-pointer" for="konfirmasi_selesai">
                        <input id="konfirmasi_selesai" name="konfirmasi" type="checkbox" value="1" required
                            class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-600">
                        <span class="text-sm font-medium text-blue-800">Saya mengonfirmasi catatan realisasi di atas
                            sudah benar.</span>
                    </label>
                </div>
                <button
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors"
                    type="submit">Tandai Selesai</button>
            </form>
        </div>
    @endif

    @if ($sesi->status === \App\Models\Pertemuan::TERJADWAL)
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex items-center gap-2 text-rose-800 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-sm font-bold">Batalkan Sesi</h3>
            </div>
            <form method="POST" action="{{ route('admin.pertemuan.batalkan', $sesi) }}">
                @csrf
                <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
                <div class="mb-4">
                    <label for="alasan_batal"
                        class="block text-xs font-bold text-rose-800 mb-1.5 uppercase tracking-wider">Alasan Pembatalan
                        <span class="text-rose-500">*</span></label>
                    <input id="alasan_batal" name="alasan" type="text" minlength="10" maxlength="2000" required
                        class="w-full rounded-lg border border-rose-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                </div>
                <div class="mb-5">
                    <label class="flex items-center gap-2 cursor-pointer" for="konfirmasi_batal">
                        <input id="konfirmasi_batal" name="konfirmasi" type="checkbox" value="1" required
                            class="h-4 w-4 rounded border-rose-300 text-rose-600 focus:ring-rose-600">
                        <span class="text-sm font-medium text-rose-800">Saya memahami bahwa sesi batal tidak dihapus
                            dari riwayat dan dapat dipulihkan.</span>
                    </label>
                </div>
                <button
                    class="rounded-lg bg-rose-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors"
                    type="submit">Batalkan Sesi</button>
            </form>
        </div>
    @endif

    @if ($sesi->status === \App\Models\Pertemuan::BATAL && $sesi->konteksTerbuka())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="flex items-center gap-2 text-amber-800 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <h3 class="text-sm font-bold">Pulihkan Sesi ke Terjadwal</h3>
            </div>
            <form method="POST" action="{{ route('admin.pertemuan.pulihkan', $sesi) }}">
                @csrf
                <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
                <div class="mb-4">
                    <label for="alasan_pulih"
                        class="block text-xs font-bold text-amber-800 mb-1.5 uppercase tracking-wider">Alasan Pemulihan
                        <span class="text-rose-500">*</span></label>
                    <input id="alasan_pulih" name="alasan" type="text" minlength="10" maxlength="2000" required
                        class="w-full rounded-lg border border-amber-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                </div>
                <div class="mb-5">
                    <label class="flex items-center gap-2 cursor-pointer" for="konfirmasi_pulih">
                        <input id="konfirmasi_pulih" name="konfirmasi" type="checkbox" value="1" required
                            class="h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-600">
                        <span class="text-sm font-medium text-amber-800">Saya mengonfirmasi sesi ini dikembalikan
                            menjadi status terjadwal.</span>
                    </label>
                </div>
                <button
                    class="rounded-lg bg-amber-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition-colors"
                    type="submit">Pulihkan Sesi</button>
            </form>
        </div>
    @endif

    @if ($sesi->status === \App\Models\Pertemuan::SELESAI)
        <div class="rounded-lg bg-slate-50 border border-slate-200 p-4 text-sm text-slate-500 flex items-center gap-3">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>Sesi telah selesai. Rencana dan waktu aktual tidak dapat diedit kembali.</span>
        </div>
    @endif
</div>
