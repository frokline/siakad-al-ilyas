@php
    $editing = $programStudi->exists;

    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version) }}">

    @error('version')
        <p class="error">
            {{ $message }}

            <a href="{{ route('admin.program-studi.edit', $programStudi) }}">
                Muat ulang data terbaru
            </a>
        </p>
    @enderror
@endif

<div class="form-grid">
    <div class="field">
        <label for="kode">Kode program studi</label>

        <input type="text" id="kode" name="kode" value="{{ $value('kode', $programStudi->kode) }}"
            maxlength="30" autocomplete="off" autocapitalize="characters" spellcheck="false" required
            @error('kode')
                aria-invalid="true"
                aria-describedby="kode-error"
            @enderror>

        @error('kode')
            <p id="kode-error" class="error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="jenjang">Jenjang / program</label>

        <input type="text" id="jenjang" name="jenjang" value="{{ $value('jenjang', $programStudi->jenjang) }}"
            maxlength="30" required
            @error('jenjang')
                aria-invalid="true"
                aria-describedby="jenjang-error"
            @enderror>

        @error('jenjang')
            <p id="jenjang-error" class="error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="field">
    <label for="nama">Nama program studi</label>

    <input type="text" id="nama" name="nama" value="{{ $value('nama', $programStudi->nama) }}"
        maxlength="150" required
        @error('nama')
            aria-invalid="true"
            aria-describedby="nama-error"
        @enderror>

    @error('nama')
        <p id="nama-error" class="error">{{ $message }}</p>
    @enderror
</div>

<div class="field">
    <label for="aktif">Status penggunaan</label>

    <select id="aktif" name="aktif" required
        @error('aktif')
            aria-invalid="true"
            aria-describedby="aktif-error"
        @enderror>
        <option value="1" @selected($value('aktif', (int) $programStudi->aktif) === '1')>
            Aktif
        </option>

        <option value="0" @selected($value('aktif', (int) $programStudi->aktif) === '0')>
            Nonaktif
        </option>
    </select>

    @error('aktif')
        <p id="aktif-error" class="error">{{ $message }}</p>
    @enderror
</div>
