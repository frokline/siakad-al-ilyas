@csrf
@if ($baru)
    <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
    <input type="hidden" name="registrasi_semester_id" value="{{ $pilihan->id }}">
    <label>Jenis biaya<select name="jenis_biaya_id" required>
            <option value="">Pilih</option>
            @foreach ($jenis as $j)
                <option value="{{ $j->id }}" @selected((string) old('jenis_biaya_id') === (string) $j->id)>{{ $j->kode }} —
                    {{ $j->nama }}</option>
            @endforeach
        </select></label>
    <label>Tahun kewajiban<input type="number" name="tahun_tagihan" min="2000" max="2199"
            value="{{ old('tahun_tagihan', now('Asia/Makassar')->year) }}" required></label>
    <label>Bulan kewajiban<input type="number" name="bulan_tagihan" min="1" max="12"
            value="{{ old('bulan_tagihan') }}" required></label>
@else
    @method('PATCH')<input type="hidden" name="versi" value="{{ old('versi', $tagihan->versiForm()) }}">
    <p>{{ $tagihan->nomor }} · Bulan {{ sprintf('%02d/%04d', $tagihan->bulan_tagihan, $tagihan->tahun_tagihan) }}.
        Identitas tidak diganti.</p>
@endif
<label>Nominal rupiah<input name="nominal" inputmode="decimal" maxlength="15"
        value="{{ old('nominal', $tagihan->nominal) }}" placeholder="250000.00" required></label>
<small>Tanpa titik pemisah ribuan/koma. Contoh: 250000 atau 250000.50.</small>
<label>Jatuh tempo<input type="date" name="jatuh_tempo" min="2000-01-01" max="2199-12-31"
        value="{{ old('jatuh_tempo', $tagihan->jatuh_tempo?->format('Y-m-d')) }}" required></label>
<label>Catatan pada tagihan
    <textarea name="catatan" maxlength="1000">{{ old('catatan', $tagihan->catatan) }}</textarea>
</label>
<label>Alasan pencatatan/perubahan (internal)
    <textarea name="alasan" minlength="10" maxlength="1000" required>{{ old('alasan') }}</textarea>
</label>
<button type="submit">Simpan draf</button>
