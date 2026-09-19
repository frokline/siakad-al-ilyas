<div class="notice">
    <strong>{{ $kelas->kode }}</strong> · {{ $kelas->nama_mk_snapshot }}. Kelas kegiatan tetap setelah disimpan.
</div>
<div class="field"><label for="jenis">Jenis *</label><select id="jenis" name="jenis" required>
        @foreach (\App\Models\Kegiatan::JENIS as $kode => $label)
            <option value="{{ $kode }}" @selected(old('jenis', $kegiatan->jenis ?? 'tugas') === $kode)>{{ $label }}</option>
        @endforeach
    </select></div>
<div class="field"><label for="judul">Judul *</label><input id="judul" name="judul" maxlength="200" required
        value="{{ old('judul', $kegiatan->judul) }}"></div>
<div class="field"><label for="pertemuan_id">Pertemuan terkait</label>
    <select id="pertemuan_id" name="pertemuan_id">
        <option value="">Kegiatan umum kelas</option>
        @foreach ($pertemuan as $sesi)
            <option value="{{ $sesi->id }}" @selected((string) old('pertemuan_id', $kegiatan->pertemuan_id) === (string) $sesi->id)>
                Pertemuan {{ $sesi->nomor }} — {{ $sesi->topik }}
            </option>
        @endforeach
    </select><small>Pertemuan yang dibatalkan tidak dapat dipilih.</small>
</div>
<div class="field"><label for="instruksi">Instruksi pengerjaan *</label>
    <textarea id="instruksi" name="instruksi" rows="10" minlength="10" maxlength="10000" required>{{ old('instruksi', $kegiatan->instruksi) }}</textarea>
    <small>Teks biasa, 10–10.000 karakter. Tuliskan soal, cara pengerjaan, dan ketentuan jawaban di sini.</small>
</div>
<fieldset class="field">
    <legend>Jadwal ({{ $zona }})</legend>
    <div class="field"><label for="buka_lokal">Mulai *</label>
        <input type="datetime-local" id="buka_lokal" name="buka_lokal" step="60" required
            value="{{ old('buka_lokal', $kegiatan->buka_at?->setTimezone($zona)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="field"><label for="tenggat_lokal">Tenggat *</label>
        <input type="datetime-local" id="tenggat_lokal" name="tenggat_lokal" step="60" required
            value="{{ old('tenggat_lokal', $kegiatan->tenggat_at?->setTimezone($zona)->format('Y-m-d\TH:i')) }}">
    </div>
    <small>Instruksi dan lampiran dibuka untuk mahasiswa pada waktu mulai. Batas pengumpulan bersifat ketat: sebelum
        tenggat, bukan tepat pada tenggat.</small>
</fieldset>
<fieldset class="field">
    <legend>Ketentuan berkas jawaban</legend>
    <div class="field"><label for="maks_mb">Ukuran maksimal setiap berkas (MB) *</label>
        <input type="number" id="maks_mb" name="maks_mb" min="1" max="20" step="1" required
            value="{{ old('maks_mb', $kegiatan->exists ? (int) ($kegiatan->maks_ukuran_byte / 1048576) : 10) }}">
    </div>
    <div class="field"><label for="maks_berkas">Jumlah maksimal berkas jawaban *</label>
        <input type="number" id="maks_berkas" name="maks_berkas" min="1" max="5" step="1" required
            value="{{ old('maks_berkas', $kegiatan->maks_berkas ?? 3) }}">
    </div>
    <p>Format jawaban yang diizinkan (pilih minimal satu):</p>
    @foreach (config('kegiatan.ekstensi_jawaban', ['pdf', 'jpg', 'png']) as $ext)
        <label class="check"><input type="checkbox" name="ekstensi_diizinkan[]" value="{{ $ext }}"
                @checked(in_array($ext, (array) old('ekstensi_diizinkan', $kegiatan->ekstensi_diizinkan ?? ['pdf']), true))> {{ strtoupper($ext) }}{{ $ext === 'jpg' ? ' / JPEG' : '' }}</label>
    @endforeach
    <small>Ketentuan jawaban berbeda dari lampiran soal milik pengajar. Metode kegiatan ini adalah pengumpulan berkas,
        termasuk untuk UTS/UAS.</small>
</fieldset>
<fieldset class="field">
    <legend>Lampiran instruksi / soal</legend>
    <p>Unggah melalui <a href="{{ route('berkas.index') }}" target="_blank" rel="noopener noreferrer">Berkas saya (tab
            baru)</a>, lalu salin ID dari URL detail.
        Contoh <code>/berkas/12</code> berarti ID <code>12</code>.</p>
    <label for="lampiran_ids">ID berkas, pisahkan dengan koma</label>
    <input id="lampiran_ids" name="lampiran_ids" maxlength="250" placeholder="12, 18"
        value="{{ old('lampiran_ids', $kegiatan->exists ? $kegiatan->lampiran->pluck('berkas_id')->implode(', ') : '') }}">
    <small>Maksimal {{ config('kegiatan.maks_lampiran_instruksi', 10) }} lampiran. File baru wajib milik Anda dan
        tersedia.
        Jangan memakai file soal yang telah dibagikan melalui materi atau kegiatan lain sebelum jadwal ujian.</small>
    @if ($kegiatan->exists && $kegiatan->lampiran->isNotEmpty())
        <ul>
            @foreach ($kegiatan->lampiran as $p)
                <li>ID {{ $p->berkas_id }} — {{ $p->berkas->label }}</li>
            @endforeach
        </ul>
    @endif
</fieldset>
@if ($kegiatan->exists)
    <div class="field"><label for="alasan">Alasan perubahan *</label>
        <textarea id="alasan" name="alasan" rows="2" minlength="10" maxlength="1000" required>{{ old('alasan') }}</textarea>
    </div>
@endif
<p class="notice">Periksa sebelum menerbitkan. Instruksi, lampiran, waktu mulai, serta ketentuan jawaban dikunci sejak
    penerbitan pertama. Tenggat hanya dapat diperpanjang.</p>
<div class="actions"><button type="submit">Simpan draf</button><a
        href="{{ $kegiatan->exists ? route('kegiatan.show', $kegiatan) : route('kegiatan.kelas') }}">Batal</a></div>
