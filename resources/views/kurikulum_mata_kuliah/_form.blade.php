@php
    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

<input type="hidden" name="version" value="{{ $value('version', $version) }}">

@error('version')
    <p class="field-error" role="alert">{{ $message }}</p>
@enderror

@if ($errors->any())
    <p class="help">
        <a
            href="{{ $detail->exists
                ? route('admin.kurikulum.mata-kuliah.edit', [$kurikulum, $detail])
                : route('admin.kurikulum.mata-kuliah.create', $kurikulum) }}">
            Muat ulang formulir dari data terbaru
        </a>
    </p>
@endif

@if ($detail->exists)
    <div class="field">
        <label>Mata kuliah</label>

        <p>
            <strong>{{ $detail->mataKuliah->kode }}</strong>
            — {{ $detail->mataKuliah->nama }}
        </p>

        @if (!$detail->mataKuliah->aktif)
            <p class="help">Mata kuliah ini sedang nonaktif pada katalog.</p>
        @endif
    </div>
@else
    <div class="field">
        <label for="mata_kuliah_id">Mata kuliah</label>

        <select id="mata_kuliah_id" name="mata_kuliah_id" required
            aria-invalid="{{ $errors->has('mata_kuliah_id') ? 'true' : 'false' }}">
            <option value="">Pilih mata kuliah</option>

            @foreach ($daftarMataKuliah as $mataKuliah)
                <option value="{{ $mataKuliah->id }}" @selected($value('mata_kuliah_id') === (string) $mataKuliah->id)>
                    {{ $mataKuliah->kode }} — {{ $mataKuliah->nama }}
                </option>
            @endforeach
        </select>

        <p class="help">
            Daftar menampilkan mata kuliah aktif dari prodi yang sama
            dan belum tercantum dalam kurikulum ini.
        </p>

        @error('mata_kuliah_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@endif

<div class="form-grid">
    <div class="field">
        <label for="sks">SKS</label>

        <input id="sks" name="sks" type="text" inputmode="decimal"
            value="{{ $value('sks', $detail->sks) }}" maxlength="5" placeholder="Contoh: 3 atau 2,5" required
            aria-invalid="{{ $errors->has('sks') ? 'true' : 'false' }}">

        @error('sks')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="semester_rekomendasi">Semester rekomendasi</label>

        <input id="semester_rekomendasi" name="semester_rekomendasi" type="number"
            value="{{ $value('semester_rekomendasi', $detail->semester_rekomendasi) }}" min="1" max="32767"
            step="1" placeholder="Contoh: 1" required
            aria-invalid="{{ $errors->has('semester_rekomendasi') ? 'true' : 'false' }}">

        <p class="help">Semester studi mahasiswa, misalnya semester 1 atau 2.</p>

        @error('semester_rekomendasi')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="field">
    <label for="sifat">Sifat mata kuliah</label>

    <select id="sifat" name="sifat" required aria-invalid="{{ $errors->has('sifat') ? 'true' : 'false' }}">
        @foreach ($sifatOptions as $kodeSifat => $labelSifat)
            <option value="{{ $kodeSifat }}" @selected($value('sifat', $detail->sifat) === $kodeSifat)>
                {{ $labelSifat }}
            </option>
        @endforeach
    </select>

    <p class="help">
        Label Pilihan adalah penanda kurikulum.
        KRS versi pertama tetap mengikuti paket semester.
    </p>

    @error('sifat')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
