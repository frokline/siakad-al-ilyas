@csrf

@if ($kelas->exists)
    @method('PATCH')
    <input type="hidden" name="versi" value="{{ old('versi', $versi) }}">

    <div class="field">
        <p><strong>Mata kuliah:</strong> {{ $kelas->nama_mk_snapshot }}</p>
        <p class="help">SKS: {{ str_replace('.', ',', $kelas->sks_snapshot) }}</p>
    </div>
@else
    <input type="hidden" name="rombel_id" value="{{ $rombel->id }}">

    <div class="field">
        <label for="detail_paket_id">Mata kuliah paket <span aria-hidden="true">*</span></label>
        <select name="detail_paket_id" id="detail_paket_id" required aria-describedby="mata-kuliah-help"
            aria-invalid="{{ $errors->has('detail_paket_id') ? 'true' : 'false' }}">
            <option value="">Pilih mata kuliah</option>
            @foreach ($detailPilihan as $detail)
                <option value="{{ $detail->id }}" @selected((string) old('detail_paket_id') === (string) $detail->id)>
                    {{ $detail->kurikulumMataKuliah->mataKuliah->kode }}
                    — {{ $detail->kurikulumMataKuliah->mataKuliah->nama }}
                    — {{ str_replace('.', ',', $detail->kurikulumMataKuliah->sks) }} SKS
                </option>
            @endforeach
        </select>
        <p class="help" id="mata-kuliah-help">
            Hanya mata kuliah aktif dari paket rombel yang belum memiliki kelas.
            Nama dan SKS disimpan saat kelas dibuat.
        </p>
        @error('detail_paket_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@endif

@if (!$kelas->exists || $kelas->kodeDapatDiubah())
    <div class="field">
        <label for="kode">Kode kelas <span aria-hidden="true">*</span></label>
        <input type="text" name="kode" id="kode" required maxlength="40" autocomplete="off"
            autocapitalize="characters" spellcheck="false" value="{{ old('kode', $kelas->kode) }}"
            placeholder="Contoh: UMQ-A" aria-describedby="kode-help"
            aria-invalid="{{ $errors->has('kode') ? 'true' : 'false' }}">
        <p class="help" id="kode-help">
            Maksimal 40 karakter. Kode harus unik dalam rombel dan terkunci setelah aktivasi pertama.
        </p>
        @error('kode')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@else
    <div class="field">
        <p><strong>Kode kelas:</strong> {{ $kelas->kode }}</p>
        <p class="help">Kode telah dikunci sejak aktivasi pertama.</p>
    </div>
@endif

@if ($kelas->exists)
    <div class="field">
        <label for="status">Status kelas <span aria-hidden="true">*</span></label>
        <select name="status" id="status" required aria-describedby="status-help">
            @foreach ($kelas->pilihanStatus() as $kode => $label)
                <option value="{{ $kode }}" @selected(old('status', $kelas->status) === $kode)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <p class="help" id="status-help">
            Aktivasi memerlukan periode aktif. Kelas aktif harus diselesaikan sebelum diarsipkan.
            Arsip yang belum pernah aktif dapat dikembalikan ke persiapan.
        </p>
        @error('status')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@else
    <p class="help">
        Status awal kelas adalah <strong>persiapan</strong>.
        Setelah data diperiksa, aktivasi dilakukan melalui halaman edit.
    </p>
@endif

<label class="kelas-confirm" for="konfirmasi">
    <input type="checkbox" name="konfirmasi" id="konfirmasi" value="1" required>
    <span>Saya telah memeriksa rombel, mata kuliah, kode kelas, dan status yang dipilih.</span>
</label>
@error('konfirmasi')
    <p class="field-error">{{ $message }}</p>
@enderror

@if ($errors->has('versi') && $kelas->exists)
    <div class="alert alert-error" role="alert">
        {{ $errors->first('versi') }}
        <a href="{{ route('admin.kelas-kuliah.edit', $kelas) }}">Muat formulir terbaru</a>
    </div>
@endif

<div class="actions kelas-form-actions">
    <button type="submit" class="button" @disabled($errors->has('versi'))>
        {{ $kelas->exists ? 'Simpan perubahan' : 'Simpan kelas' }}
    </button>
    <a class="button secondary"
        href="{{ $kelas->exists ? route('admin.kelas-kuliah.show', $kelas) : route('admin.kelas-kuliah.index') }}">
        Kembali
    </a>
</div>
