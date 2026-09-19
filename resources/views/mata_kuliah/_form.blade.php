@php
    $editing = $mataKuliah->exists;

    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version) }}">

    @error('version')
        <p class="field-error" role="alert">{{ $message }}</p>
    @enderror

    @if ($errors->any())
        <p class="help">
            <a href="{{ route('admin.mata-kuliah.edit', $mataKuliah) }}">
                Muat ulang formulir dari data terbaru
            </a>
        </p>
    @endif

    <dl class="detail-grid">
        <div>
            <dt>Program studi</dt>
            <dd>
                {{ $mataKuliah->programStudi->kode }} —
                {{ $mataKuliah->programStudi->nama }}
            </dd>
        </div>

        <div>
            <dt>Kode mata kuliah</dt>
            <dd>{{ $mataKuliah->kode }}</dd>
        </div>
    </dl>

    <p class="help">
        Kode dan program studi ditetapkan saat pembuatan.
    </p>
@else
    <div class="field">
        <label for="program_studi_id">Program studi</label>

        <select id="program_studi_id" name="program_studi_id" required
            aria-invalid="{{ $errors->has('program_studi_id') ? 'true' : 'false' }}">
            <option value="">Pilih program studi</option>

            @foreach ($daftarProdi as $prodi)
                <option value="{{ $prodi->id }}" @selected($value('program_studi_id') === (string) $prodi->id)>
                    {{ $prodi->kode }} — {{ $prodi->nama }}
                </option>
            @endforeach
        </select>

        @error('program_studi_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="kode">Kode mata kuliah</label>

        <input id="kode" name="kode" type="text" value="{{ $value('kode') }}" maxlength="40"
            placeholder="Contoh: SYR-101" autocomplete="off" spellcheck="false" required
            aria-invalid="{{ $errors->has('kode') ? 'true' : 'false' }}">

        <p class="help">
            Kode harus unik dalam program studi yang sama.
            Pastikan kode benar sebelum menyimpan.
        </p>

        @error('kode')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@endif

<div class="field">
    <label for="nama">Nama mata kuliah</label>

    <input id="nama" name="nama" type="text" value="{{ $value('nama', $mataKuliah->nama) }}" maxlength="150"
        placeholder="Contoh: Pengantar Ilmu Syariah" required
        aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}">

    @error('nama')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

<div class="field">
    <label for="aktif">Status mata kuliah</label>

    <select id="aktif" name="aktif" required aria-invalid="{{ $errors->has('aktif') ? 'true' : 'false' }}">
        @foreach ($statusOptions as $kodeStatus => $labelStatus)
            <option value="{{ $kodeStatus }}" @selected($value('aktif', (int) $mataKuliah->aktif) === (string) $kodeStatus)>
                {{ $labelStatus }}
            </option>
        @endforeach
    </select>

    <p class="help">
        Gunakan Nonaktif jika mata kuliah tidak lagi digunakan.
    </p>

    @error('aktif')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
