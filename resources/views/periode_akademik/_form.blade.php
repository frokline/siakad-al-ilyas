@php
    $editing = $periodeAkademik->exists;

    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };

    $tanggalFields = [
        'mulai' => 'Mulai perkuliahan',
        'selesai' => 'Selesai perkuliahan',
    ];

    $krsFields = [
        'krs_mulai' => 'Awal pengisian KRS',
        'krs_selesai' => 'Batas akhir pengisian KRS',
    ];
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version) }}">

    @error('version')
        <p class="error">
            {{ $message }}

            <a href="{{ route('admin.periode-akademik.edit', $periodeAkademik) }}">
                Muat ulang data terbaru
            </a>
        </p>
    @enderror
@endif

<div class="form-grid">
    <div class="field">
        <label for="tahun_mulai">Tahun awal akademik</label>

        <input type="number" id="tahun_mulai" name="tahun_mulai"
            value="{{ $value('tahun_mulai', $periodeAkademik->tahun_mulai) }}" min="1900" max="9998"
            step="1" placeholder="2026" required>

        <p class="help">Isi 2026 untuk tahun ajaran 2026/2027.</p>

        @error('tahun_mulai')
            <p class="error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="jenis">Jenis semester</label>

        <select id="jenis" name="jenis" required>
            @foreach ($jenisOptions as $key => $label)
                <option value="{{ $key }}" @selected($value('jenis', $periodeAkademik->jenis) === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('jenis')
            <p class="error">{{ $message }}</p>
        @enderror
    </div>
</div>

<p class="help">
    Kode periode dibentuk otomatis dari tahun awal dan jenis semester,
    misalnya 2026-GANJIL.
</p>

<div class="form-grid">
    @foreach ($tanggalFields as $field => $label)
        <div class="field">
            <label for="{{ $field }}">{{ $label }}</label>

            <input type="date" id="{{ $field }}" name="{{ $field }}"
                value="{{ $value($field, $periodeAkademik->{$field}?->format('Y-m-d')) }}" min="1900-01-01"
                max="9999-12-31" required
                @error($field)
                    aria-invalid="true"
                    aria-describedby="{{ $field }}-error"
                @enderror>

            @error($field)
                <p id="{{ $field }}-error" class="error">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>

<h2>Jadwal Pengisian KRS</h2>

<p class="help">
    Zona waktu: {{ config('siakad.timezone') }}.
    Isi kedua batas waktu atau kosongkan keduanya.
    KRS boleh dimulai sebelum tanggal perkuliahan.
</p>

<div class="form-grid">
    @foreach ($krsFields as $field => $label)
        <div class="field">
            <label for="{{ $field }}">{{ $label }}</label>

            <input type="datetime-local" id="{{ $field }}" name="{{ $field }}"
                value="{{ $value($field, $periodeAkademik->{$field}?->setTimezone(config('siakad.timezone'))->format('Y-m-d\TH:i')) }}"
                min="1900-01-01T00:00" max="9999-12-31T23:59" step="60"
                @error($field)
                    aria-invalid="true"
                    aria-describedby="{{ $field }}-error"
                @enderror>

            @error($field)
                <p id="{{ $field }}-error" class="error">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>

<div class="field">
    <label for="status">Status periode</label>

    <select id="status" name="status" required>
        @foreach ($statusOptions as $key => $label)
            <option value="{{ $key }}" @selected($value('status', $periodeAkademik->status) === $key)>
                {{ $label }}
            </option>
        @endforeach
    </select>

    <p class="help">
        Pengisian KRS membutuhkan status Aktif dan jadwal KRS yang sedang berlaku.
        Status Persiapan atau Arsip menutup pengisian KRS.
    </p>

    @error('status')
        <p class="error">{{ $message }}</p>
    @enderror
</div>
