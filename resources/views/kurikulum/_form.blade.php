@php
    $editing = $kurikulum->exists;
    $editable = $kurikulum->identitasDapatDiubah();

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
            <a href="{{ route('admin.kurikulum.edit', $kurikulum) }}">
                Muat ulang formulir dari data terbaru
            </a>
        </p>
    @endif
@endif

@if ($editable)
    <div class="field">
        <label for="program_studi_id">Program studi</label>

        <select id="program_studi_id" @disabled($prodiTerkunci) name="program_studi_id" required
            aria-invalid="{{ $errors->has('program_studi_id') ? 'true' : 'false' }}">
            <option value="">Pilih program studi</option>

            @foreach ($daftarProdi as $prodi)
                <option value="{{ $prodi->id }}" @selected($value('program_studi_id', $kurikulum->program_studi_id) === (string) $prodi->id)>
                    {{ $prodi->kode }} — {{ $prodi->nama }}
                    {{ $prodi->aktif ? '' : '(Nonaktif)' }}
                </option>
            @endforeach

            @if ($prodiTerkunci)
                <input type="hidden" name="program_studi_id" value="{{ $kurikulum->program_studi_id }}">

                <p class="help">
                    Program studi terkunci karena kurikulum sudah memiliki mata kuliah.
                </p>
            @endif
        </select>

        @error('program_studi_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid">
        <div class="field">
            <label for="kode">Kode kurikulum</label>

            <input id="kode" name="kode" type="text" value="{{ $value('kode', $kurikulum->kode) }}"
                maxlength="40" placeholder="Contoh: KUR-2026" autocomplete="off" spellcheck="false" required
                aria-invalid="{{ $errors->has('kode') ? 'true' : 'false' }}">

            <p class="help">Kode harus unik dalam program studi yang sama.</p>

            @error('kode')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="tahun_berlaku">Tahun berlaku</label>

            <input id="tahun_berlaku" name="tahun_berlaku" type="number"
                value="{{ $value('tahun_berlaku', $kurikulum->tahun_berlaku) }}" min="1900" max="9999"
                step="1" inputmode="numeric" placeholder="Contoh: 2026" required
                aria-invalid="{{ $errors->has('tahun_berlaku') ? 'true' : 'false' }}">

            @error('tahun_berlaku')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="field">
        <label for="nama">Nama kurikulum</label>

        <input id="nama" name="nama" type="text" value="{{ $value('nama', $kurikulum->nama) }}"
            maxlength="150" placeholder="Contoh: Kurikulum Ilmu Syariah 2026" required
            aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}">

        @error('nama')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@else
    <p class="help">
        Identitas kurikulum sudah terkunci. Buat kurikulum versi baru
        apabila terdapat perubahan identitas.
    </p>

    <dl class="detail-grid">
        <div>
            <dt>Program studi</dt>
            <dd>
                {{ $kurikulum->programStudi->kode }} —
                {{ $kurikulum->programStudi->nama }}
            </dd>
        </div>

        <div>
            <dt>Kode kurikulum</dt>
            <dd>{{ $kurikulum->kode }}</dd>
        </div>

        <div>
            <dt>Nama kurikulum</dt>
            <dd>{{ $kurikulum->nama }}</dd>
        </div>

        <div>
            <dt>Tahun berlaku</dt>
            <dd>{{ $kurikulum->tahun_berlaku }}</dd>
        </div>
    </dl>
@endif

@if ($editing)
    <div class="field">
        <label for="status">Status kurikulum</label>

        <select id="status" name="status" required aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}">
            @foreach ($statusOptions as $kodeStatus => $labelStatus)
                <option value="{{ $kodeStatus }}" @selected($value('status', $kurikulum->status) === $kodeStatus)>
                    {{ $labelStatus }}
                </option>
            @endforeach
        </select>

        @if ($editable)
            <p class="help">
                Memilih Aktif atau Arsip akan mengunci identitas kurikulum.
                Status tidak dapat dikembalikan menjadi Draf.
            </p>
        @else
            <p class="help">
                Kurikulum arsip dapat diaktifkan kembali jika program studi aktif.
                Identitasnya tetap terkunci.
            </p>
        @endif

        @error('status')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@else
    <p class="help">
        Status awal: <strong>Draf</strong>.
        Aktivasi dilakukan melalui halaman edit.
    </p>
@endif
