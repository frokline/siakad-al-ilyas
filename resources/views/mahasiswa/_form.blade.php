@php
    $mengedit = $mahasiswa->exists;

    $nilai = static function (string $kolom, mixed $default = ''): string {
        $hasil = old($kolom, $default);

        return is_scalar($hasil) ? (string) $hasil : '';
    };

    $tanggalMaksimal = now(config('siakad.timezone', 'Asia/Makassar'))->toDateString();
@endphp

<div class="mhs-account">
    <strong>{{ $akun->nama }}</strong>
    <div class="help">{{ $akun->email }}</div>
    <div class="help">
        Nama, email, dan status akun dikelola melalui menu Pengguna.
    </div>
</div>

@error('user_id')
    <p class="mhs-error" role="alert">{{ $message }}</p>
@enderror

@if ($mengedit)
    <input type="hidden" name="versi" value="{{ $nilai('versi', $mahasiswa->versiForm()) }}">

    @error('versi')
        <div class="mhs-warning" role="alert">
            <p>{{ $message }}</p>

            <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}">
                Muat ulang formulir
            </a>
        </div>
    @enderror
@else
    <input type="hidden" name="user_id" value="{{ $akun->id }}">
@endif

<div class="mhs-grid">
    <div class="field">
        <label for="nim">NIM <span aria-hidden="true">*</span></label>

        <input type="text" id="nim" name="nim" maxlength="40" value="{{ $nilai('nim', $mahasiswa->nim) }}"
            autocomplete="off" aria-describedby="nim-help" required>

        <p class="help" id="nim-help">
            NIM harus unik. Huruf akan disimpan sebagai huruf kapital.
        </p>

        @error('nim')
            <p class="mhs-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="jenis_kelamin">Jenis kelamin</label>

        <select id="jenis_kelamin" name="jenis_kelamin">
            <option value="">Belum diisi</option>

            @foreach (\App\Models\Mahasiswa::JENIS_KELAMIN as $kode => $label)
                <option value="{{ $kode }}" @selected($nilai('jenis_kelamin', $mahasiswa->jenis_kelamin) === $kode)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('jenis_kelamin')
            <p class="mhs-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="tempat_lahir">Tempat lahir</label>

        <input type="text" id="tempat_lahir" name="tempat_lahir" maxlength="100"
            value="{{ $nilai('tempat_lahir', $mahasiswa->tempat_lahir) }}">

        @error('tempat_lahir')
            <p class="mhs-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="tanggal_lahir">Tanggal lahir</label>

        <input type="date" id="tanggal_lahir" name="tanggal_lahir" min="1900-01-01" max="{{ $tanggalMaksimal }}"
            value="{{ $nilai('tanggal_lahir', $mahasiswa->tanggal_lahir?->format('Y-m-d')) }}">

        @error('tanggal_lahir')
            <p class="mhs-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field mhs-full">
        <label for="alamat">Alamat</label>

        <textarea id="alamat" name="alamat" rows="4" maxlength="2000">{{ $nilai('alamat', $mahasiswa->alamat) }}</textarea>

        @error('alamat')
            <p class="mhs-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="actions mhs-section">
    <button type="submit" class="button" @disabled($errors->has('versi'))>
        {{ $mengedit ? 'Simpan perubahan' : 'Simpan mahasiswa' }}
    </button>

    <a class="button secondary"
        href="{{ $mengedit ? route('admin.mahasiswa.show', $mahasiswa) : route('admin.mahasiswa.index') }}">
        Batal
    </a>
</div>
