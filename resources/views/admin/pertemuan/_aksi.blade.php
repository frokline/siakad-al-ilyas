@php
    $zona = config('siakad.timezone', 'Asia/Makassar');
    $formDasar = function (string $route) use ($versi): string {
        return $route;
    };
@endphp
<div class="pertemuan-actions-panel">
    @if ($sesi->status === \App\Models\Pertemuan::TERJADWAL && $sesi->konteksTerbuka())
        <form method="POST" action="{{ route('admin.pertemuan.mulai', $sesi) }}" class="pertemuan-action-form">
            @csrf
            <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
            <div class="field"><label for="alasan_mulai">Catatan mulai <span aria-hidden="true">*</span></label><input
                    id="alasan_mulai" name="alasan" type="text" minlength="10" maxlength="2000" required
                    placeholder="Contoh: perkuliahan dimulai sesuai jadwal"></div>
            <label class="pertemuan-confirm" for="konfirmasi_mulai"><input id="konfirmasi_mulai" name="konfirmasi"
                    type="checkbox" value="1" required><span>Saya mengonfirmasi sesi dimulai
                    sekarang.</span></label>
            <button class="button" type="submit">Mulai sesi</button>
        </form>
    @endif
    @if ($sesi->status === \App\Models\Pertemuan::BERLANGSUNG)
        <form method="POST" action="{{ route('admin.pertemuan.selesai', $sesi) }}" class="pertemuan-action-form">
            @csrf
            <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
            <div class="field"><label for="realisasi">Catatan realisasi <span aria-hidden="true">*</span></label>
                <textarea id="realisasi" name="realisasi" rows="4" minlength="10" maxlength="20000" required>{{ old('realisasi') }}</textarea>
            </div>
            <div class="field"><label for="alasan_selesai">Alasan penyelesaian <span
                        aria-hidden="true">*</span></label><input id="alasan_selesai" name="alasan" type="text"
                    minlength="10" maxlength="2000" required placeholder="Contoh: materi selesai dibahas"></div>
            <label class="pertemuan-confirm" for="konfirmasi_selesai"><input id="konfirmasi_selesai" name="konfirmasi"
                    type="checkbox" value="1" required><span>Saya mengonfirmasi catatan realisasi sudah
                    benar.</span></label>
            @error('realisasi')
                <p class="field-error">{{ $message }}</p>
            @enderror
            <button class="button" type="submit">Tandai selesai</button>
        </form>
    @endif
    @if ($sesi->status === \App\Models\Pertemuan::TERJADWAL)
        <form method="POST" action="{{ route('admin.pertemuan.batalkan', $sesi) }}"
            class="pertemuan-action-form pertemuan-action-danger">
            @csrf
            <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
            <div class="field"><label for="alasan_batal">Alasan pembatalan <span
                        aria-hidden="true">*</span></label><input id="alasan_batal" name="alasan" type="text"
                    minlength="10" maxlength="2000" required></div>
            <label class="pertemuan-confirm" for="konfirmasi_batal"><input id="konfirmasi_batal" name="konfirmasi"
                    type="checkbox" value="1" required><span>Saya memahami sesi tidak dihapus dan dapat
                    dipulihkan.</span></label>
            <button class="button danger" type="submit">Batalkan sesi</button>
        </form>
    @endif
    @if ($sesi->status === \App\Models\Pertemuan::BATAL && $sesi->konteksTerbuka())
        <form method="POST" action="{{ route('admin.pertemuan.pulihkan', $sesi) }}" class="pertemuan-action-form">
            @csrf
            <input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
            <div class="field"><label for="alasan_pulih">Alasan pemulihan <span
                        aria-hidden="true">*</span></label><input id="alasan_pulih" name="alasan" type="text"
                    minlength="10" maxlength="2000" required></div>
            <label class="pertemuan-confirm" for="konfirmasi_pulih"><input id="konfirmasi_pulih" name="konfirmasi"
                    type="checkbox" value="1" required><span>Saya mengonfirmasi sesi dikembalikan menjadi
                    terjadwal.</span></label>
            <button class="button" type="submit">Pulihkan sesi</button>
        </form>
    @endif
    @if ($sesi->status === \App\Models\Pertemuan::SELESAI)
        <p class="pertemuan-locked-note">Sesi selesai. Rencana dan waktu aktual tidak dapat diedit.</p>
    @endif
</div>
