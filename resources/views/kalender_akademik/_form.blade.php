@csrf
@if ($baru)
    <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
@else
    @method('PATCH')<input type="hidden" name="versi" value="{{ old('versi', $agenda->versiForm()) }}">
@endif
<label for="periode">Periode akademik *</label><select id="periode" name="periode_akademik_id" required
    @disabled($agenda->diterbitkan_at !== null)>
    <option value="">Pilih periode</option>
    @foreach ($periode as $p)
        <option value="{{ $p->id }}" @selected((string) old('periode_akademik_id', $agenda->periode_akademik_id) === (string) $p->id)>{{ $p->kode }} ({{ $p->status }})
        </option>
    @endforeach
</select>
<label for="prodi">Sasaran program studi</label><select id="prodi" name="program_studi_id"
    @disabled($agenda->diterbitkan_at !== null)>
    <option value="">Seluruh kampus</option>
    @foreach ($prodi as $p)
        <option value="{{ $p->id }}" @selected((string) old('program_studi_id', $agenda->program_studi_id) === (string) $p->id)>
            {{ $p->nama }}{{ $p->aktif ? '' : ' (nonaktif)' }}</option>
    @endforeach
</select>
@if ($agenda->diterbitkan_at)
    <input type="hidden" name="periode_akademik_id" value="{{ $agenda->periode_akademik_id }}"><input type="hidden"
        name="program_studi_id" value="{{ $agenda->program_studi_id }}">
    <p class="notice">Periode dan sasaran tetap setelah publikasi. Perubahan judul, isi, atau waktu langsung terlihat
        pengguna.</p>
@endif
<label for="judul">Judul *</label><input id="judul" name="judul" maxlength="200" minlength="3"
    value="{{ old('judul', $agenda->judul) }}" required>
<label for="jenis">Jenis agenda *</label><select id="jenis" name="jenis" required>
    @foreach (\App\Models\KalenderAkademik::JENIS as $kode => $label)
        <option value="{{ $kode }}" @selected(old('jenis', $agenda->jenis ?? 'lainnya') === $kode)>{{ $label }}</option>
    @endforeach
</select>
<div class="kal-waktu">
    <div><label for="mulai">Mulai (WITA) *</label><input type="datetime-local" id="mulai" name="mulai_lokal"
            min="2000-01-01T00:00" max="2100-12-31T23:59" required
            value="{{ old('mulai_lokal', $agenda->mulai_at?->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('Y-m-d\TH:i')) }}">
    </div>
    <div><label for="selesai">Selesai (WITA) *</label><input type="datetime-local" id="selesai" name="selesai_lokal"
            min="2000-01-01T00:00" max="2100-12-31T23:59" required
            value="{{ old('selesai_lokal', $agenda->selesai_at?->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('Y-m-d\TH:i')) }}">
    </div>
</div>
<p class="muted">Akhir harus setelah awal. Untuk satu hari penuh, isi 00:00 sampai 00:00 hari berikutnya. Waktu selesai
    tidak termasuk durasi penayangan hari berikutnya.</p>
<label for="keterangan">Keterangan</label>
<textarea id="keterangan" name="keterangan" rows="5" maxlength="5000">{{ old('keterangan', $agenda->keterangan) }}</textarea>
@unless ($baru)
    <label for="alasan">Alasan perubahan *</label>
    <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="1000" required>{{ old('alasan') }}</textarea>
    <p class="muted">Alasan perubahan terakhir tampil kepada pembaca agenda terbit.</p>
@endunless
<div class="actions"><button type="submit">{{ $baru ? 'Simpan draf' : 'Simpan perubahan' }}</button><a
        href="{{ $baru ? route('kalender.index') : route('kalender.show', $agenda) }}">Batal</a></div>
