@csrf
@if ($baru)
    <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
    <label for="kode">Kode *</label><input id="kode" name="kode" maxlength="30" minlength="2" required
        autocomplete="off" placeholder="AKTIF_KULIAH" value="{{ old('kode') }}" aria-describedby="kode-hint">
    <p id="kode-hint" class="muted">2–30 karakter; diawali huruf, memakai huruf, angka, - atau _. Huruf kecil otomatis
        menjadi besar. Kode tetap setelah dibuat.</p>
@else
    @method('PATCH')<input type="hidden" name="versi" value="{{ old('versi', $jenisSurat->versiForm()) }}">
    <label for="kode">Kode tetap</label><input id="kode" value="{{ $jenisSurat->kode }}" readonly>
@endif
<label for="nama">Nama surat *</label><input id="nama" name="nama" minlength="3" maxlength="100" required
    placeholder="Surat Keterangan Aktif Kuliah" value="{{ old('nama', $jenisSurat->nama) }}">
<label for="syarat">Persyaratan</label>
<textarea id="syarat" name="syarat" rows="4" maxlength="1000">{{ old('syarat', $jenisSurat->syarat) }}</textarea>
@unless ($baru)
    <label for="alasan">Alasan perubahan *</label>
    <textarea id="alasan" name="alasan" minlength="10" maxlength="1000" rows="3" required>{{ old('alasan') }}</textarea>
@endunless
<div class="actions"><button type="submit">{{ $baru ? 'Simpan jenis surat' : 'Simpan perubahan' }}</button>
    <a href="{{ $baru ? route('admin.jenis-surat.index') : route('admin.jenis-surat.show', $jenisSurat) }}">Batal</a>
</div>
