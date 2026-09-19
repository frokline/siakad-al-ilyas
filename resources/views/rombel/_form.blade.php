@php
    $editing = $rombel->exists;

    $value = static function (string $key, mixed $default = ''): string {
        $input = old($key, $default);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version) }}">

    @error('version')
        <div class="alert alert-error" role="alert">
            {{ $message }}

            <a href="{{ route('admin.rombel.edit', $rombel) }}">
                Muat ulang formulir
            </a>
        </div>
    @enderror

    <dl class="detail-grid">
        <div>
            <dt>Periode akademik</dt>
            <dd>{{ $rombel->periodeAkademik->kode }}</dd>
        </div>

        <div>
            <dt>Program studi</dt>
            <dd>{{ $rombel->paketSemester->kurikulum->programStudi->nama }}</dd>
        </div>

        <div>
            <dt>Kurikulum</dt>
            <dd>{{ $rombel->paketSemester->kurikulum->kode }}</dd>
        </div>

        <div>
            <dt>Paket semester</dt>
            <dd>
                {{ $rombel->paketSemester->nama }}
                — Versi {{ $rombel->paketSemester->versi }}
            </dd>
        </div>

        <div>
            <dt>Semester studi</dt>
            <dd>{{ $rombel->paketSemester->semester_studi }}</dd>
        </div>
    </dl>

    <p class="help">
        Periode dan paket dikunci. Untuk penempatan pada periode
        atau paket berbeda, buat rombel baru.
    </p>
@else
    <div class="field">
        <label for="periode_akademik_id">Periode akademik</label>

        <select id="periode_akademik_id" name="periode_akademik_id" required
            aria-invalid="{{ $errors->has('periode_akademik_id') ? 'true' : 'false' }}"
            aria-describedby="periode-error">
            <option value="">Pilih periode akademik</option>

            @foreach ($daftarPeriode as $periode)
                <option value="{{ $periode->id }}" @selected($value('periode_akademik_id') === (string) $periode->id)>
                    {{ $periode->kode }}
                    — {{ $statusPeriodeOptions[$periode->status] }}
                </option>
            @endforeach
        </select>

        @error('periode_akademik_id')
            <p id="periode-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="paket_semester_id">Paket semester</label>

        <select id="paket_semester_id" name="paket_semester_id" required
            aria-invalid="{{ $errors->has('paket_semester_id') ? 'true' : 'false' }}"
            aria-describedby="paket-help paket-error">
            <option value="">Pilih paket semester</option>

            @foreach ($daftarPaket as $paket)
                <option value="{{ $paket->id }}" @selected($value('paket_semester_id') === (string) $paket->id)>
                    {{ $paket->kurikulum->programStudi->nama }}
                    — {{ $paket->kurikulum->kode }}
                    — {{ $paket->nama }}
                    — Semester {{ $paket->semester_studi }}
                    — V{{ $paket->versi }}
                </option>
            @endforeach
        </select>

        <p id="paket-help" class="help">
            Menampilkan paket terbit dengan kurikulum, program studi,
            dan mata kuliah aktif.
        </p>

        @error('paket_semester_id')
            <p id="paket-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>
@endif

<div class="form-grid">
    <div class="field">
        <label for="kode">Kode rombel</label>

        <input id="kode" name="kode" type="text" maxlength="40" required placeholder="Contoh: SI-1A"
            value="{{ $value('kode', $rombel->kode) }}" aria-invalid="{{ $errors->has('kode') ? 'true' : 'false' }}"
            aria-describedby="kode-help kode-error">

        <p id="kode-help" class="help">
            Harus unik dalam periode akademik yang sama.
        </p>

        @error('kode')
            <p id="kode-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="kapasitas">Kapasitas mahasiswa</label>

        <input id="kapasitas" name="kapasitas" type="number" min="1" max="32767" step="1"
            placeholder="Contoh: 30" value="{{ $value('kapasitas', $rombel->kapasitas) }}"
            aria-invalid="{{ $errors->has('kapasitas') ? 'true' : 'false' }}"
            aria-describedby="kapasitas-help kapasitas-error">

        <p id="kapasitas-help" class="help">
            Kosongkan jika tidak menetapkan batas kapasitas.
        </p>

        @error('kapasitas')
            <p id="kapasitas-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>
