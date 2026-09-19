@php
    $mengubah = isset($krs) && $krs->exists;
    $catatanLama = old('catatan', $mengubah ? $krs->catatan : '');
    $catatanLama = is_string($catatanLama) ? $catatanLama : '';
    $formLama = $mengubah && $errors->has('versi_form');
@endphp

@csrf
<input type="hidden" name="_form" value="{{ $mengubah ? 'update' : 'store' }}">

@if ($mengubah)
    @method('PATCH')
    <input type="hidden" name="versi_form" value="{{ $versiForm }}">
@else
    <input type="hidden" name="registrasi_semester_id" value="{{ $registrasi->id }}">
@endif

@if ($formLama)
    <div class="alert alert-error" role="alert">
        Data berubah sejak formulir dibuka.
        <a href="{{ route('admin.krs.edit', $krs) }}">Muat formulir terbaru</a>
        sebelum menyimpan kembali.
    </div>
@endif

<div class="field">
    <label for="catatan">Catatan KRS</label>
    <textarea id="catatan" name="catatan" rows="4" maxlength="2000" aria-describedby="catatan-help"
        aria-invalid="{{ $errors->has('catatan') ? 'true' : 'false' }}">{{ $catatanLama }}</textarea>
    <p id="catatan-help" class="help">Opsional, maksimal 2.000 karakter. Mata kuliah mengikuti paket yang sudah
        ditetapkan.</p>
    @error('catatan')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

<label class="krs-konfirmasi" for="konfirmasi-draf">
    <input type="checkbox" id="konfirmasi-draf" name="konfirmasi" value="1" required>
    <span>Saya sudah memeriksa identitas mahasiswa, periode, rombel, dan seluruh mata kuliah paket.</span>
</label>
@error('konfirmasi')
    <p class="field-error">{{ $message }}</p>
@enderror

<div class="actions krs-section">
    <button class="button" type="submit" @disabled(!$bolehSimpan || $formLama)>
        {{ $mengubah ? 'Simpan catatan' : 'Buat draf KRS seluruh paket' }}
    </button>
    <a class="button secondary"
        href="{{ $mengubah ? route('admin.krs.show', $krs) : route('admin.krs.index') }}">Kembali</a>
</div>
