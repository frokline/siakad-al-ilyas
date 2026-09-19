@csrf
@if ($baru)
    <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
    <label for="kode">Kode *</label><input id="kode" name="kode" maxlength="30" minlength="2" required
        autocomplete="off" placeholder="SPP" value="{{ old('kode') }}" aria-describedby="kode-hint">
    <p id="kode-hint" class="muted">2–30 karakter; diawali huruf, memakai huruf, angka, - atau _. Huruf kecil otomatis
        menjadi besar. Kode tetap setelah dibuat.</p>
@else
    @method('PATCH')<input type="hidden" name="versi" value="{{ old('versi', $jenisBiaya->versiForm()) }}">
    <label for="kode">Kode tetap</label><input id="kode" value="{{ $jenisBiaya->kode }}" readonly>
@endif
<label for="nama">Nama biaya *</label><input id="nama" name="nama" minlength="3" maxlength="100" required
    placeholder="SPP Bulanan" value="{{ old('nama', $jenisBiaya->nama) }}">
<label for="keterangan">Keterangan</label>
<textarea id="keterangan" name="keterangan" rows="4" maxlength="1000">{{ old('keterangan', $jenisBiaya->keterangan) }}</textarea>
@unless ($baru)
    <label for="alasan">Alasan perubahan *</label>
    <textarea id="alasan" name="alasan" minlength="10" maxlength="1000" rows="3" required>{{ old('alasan') }}</textarea>
@endunless
<div class="actions"><button type="submit">{{ $baru ? 'Simpan jenis biaya' : 'Simpan perubahan' }}</button>
    <a
        href="{{ $baru ? route('keuangan.jenis-biaya.index') : route('keuangan.jenis-biaya.show', $jenisBiaya) }}">Batal</a>
</div>
