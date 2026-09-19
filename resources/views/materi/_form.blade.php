<div class="notice">
    <strong>{{ $kelas->kode }}</strong> · {{ $kelas->nama_mk_snapshot }}. Kelas materi tidak dapat diganti setelah
    disimpan.
</div>
<div class="field"><label for="judul">Judul *</label>
    <input id="judul" name="judul" required maxlength="200" value="{{ old('judul', $materi->judul) }}">
    @error('judul')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
<div class="field"><label for="pertemuan_id">Pertemuan terkait</label>
    <select id="pertemuan_id" name="pertemuan_id">
        <option value="">Materi umum kelas</option>
        @foreach ($pertemuan as $sesi)
            <option value="{{ $sesi->id }}" @selected((string) old('pertemuan_id', $materi->pertemuan_id) === (string) $sesi->id)>
                Pertemuan {{ $sesi->nomor }} — {{ $sesi->topik }}
            </option>
        @endforeach
    </select>
    <small>Pertemuan yang dibatalkan tidak dapat dipilih. Pilih ulang jika kaitan sebelumnya telah dibatalkan.</small>
</div>
<div class="field"><label for="isi">Uraian materi</label>
    <textarea id="isi" name="isi" rows="10" maxlength="10000">{{ old('isi', $materi->isi) }}</textarea>
    <small>Teks biasa, maksimal 10.000 karakter. HTML dan skrip tidak dijalankan.</small>
</div>
<div class="field"><label for="tautan_eksternal">Tautan referensi</label>
    <input type="url" id="tautan_eksternal" name="tautan_eksternal" maxlength="2000" placeholder="https://..."
        value="{{ old('tautan_eksternal', $materi->tautan_eksternal) }}">
    <small>Host HTTPS yang diizinkan: {{ implode(', ', $hostTautan) }}. Pastikan dokumen eksternal juga mempunyai izin
        akses yang sesuai.</small>
</div>
<fieldset class="field">
    <legend>Lampiran materi</legend>
    <p>Unggah file melalui <a href="{{ route('berkas.index') }}" target="_blank" rel="noopener noreferrer">Berkas saya
            (tab baru)</a>.
        Salin ID dari alamat detail berkas, misalnya <code>/berkas/12</code> berarti ID <code>12</code>.</p>
    <label for="lampiran_ids">Daftar ID berkas, pisahkan dengan koma</label>
    <input id="lampiran_ids" name="lampiran_ids" maxlength="250" placeholder="12, 15, 19"
        value="{{ old('lampiran_ids', $materi->exists ? $materi->lampiran->pluck('berkas_id')->implode(', ') : '') }}">
    <small>Maksimal {{ config('materi.maks_lampiran', 10) }}. ID baru wajib milik Anda dan berstatus tersedia.
        Kosongkan untuk melepas seluruh lampiran dari draf; objek file tetap disimpan.</small>
    @if ($materi->exists && $materi->lampiran->isNotEmpty())
        <ul>
            @foreach ($materi->lampiran as $p)
                <li>ID {{ $p->berkas_id }} — {{ $p->berkas->label }} ({{ $p->berkas->ukuranLabel() }})</li>
            @endforeach
        </ul>
        <small>Lampiran aktif milik pengajar lain boleh tetap dipertahankan. Setelah dilepas, hanya pemilik berkas yang
            dapat memasangnya kembali.</small>
    @endif
</fieldset>
@if ($materi->exists)
    <div class="field"><label for="alasan">Alasan perubahan *</label>
        <textarea id="alasan" name="alasan" rows="2" minlength="10" maxlength="1000" required>{{ old('alasan') }}</textarea>
    </div>
@endif
<p class="muted">Simpan sebagai draf terlebih dahulu. Materi belum terlihat oleh mahasiswa sampai diterbitkan.</p>
<div class="actions"><button type="submit">Simpan draf</button>
    <a href="{{ $materi->exists ? route('materi.show', $materi) : route('materi.kelas') }}">Batal</a>
</div>
