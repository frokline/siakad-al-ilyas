@php
    $editing = $dosen->exists;

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
            <a href="{{ route('admin.dosen.edit', $dosen) }}">
                Muat ulang formulir dari data terbaru
            </a>
        </p>
    @endif

    <dl class="detail-grid">
        <div>
            <dt>Nama dosen</dt>
            <dd>{{ $dosen->user->nama }}</dd>
        </div>

        <div>
            <dt>Akun terhubung</dt>
            <dd>
                <a href="{{ route('admin.users.show', $dosen->user) }}">
                    {{ $dosen->user->username }}
                </a>
            </dd>
        </div>
    </dl>

    <p class="help">
        Nama, email, telepon, dan akses akun dikelola melalui menu Pengguna.
    </p>
@else
    <div class="field">
        <label for="user_id">Akun dosen</label>

        <select id="user_id" name="user_id" required aria-invalid="{{ $errors->has('user_id') ? 'true' : 'false' }}">
            <option value="">Pilih akun dosen</option>

            @foreach ($daftarPengguna as $pengguna)
                <option value="{{ $pengguna->id }}" @selected($value('user_id') === (string) $pengguna->id)>
                    {{ $pengguna->nama }} — {{ $pengguna->username }}
                </option>
            @endforeach
        </select>

        <p class="help">
            Pilih akun yang benar. Akun terhubung ditetapkan saat pembuatan.
        </p>

        @error('user_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@endif

<div class="form-grid">
    <div class="field">
        <label for="kode_dosen">Kode dosen</label>

        <input id="kode_dosen" name="kode_dosen" type="text" value="{{ $value('kode_dosen', $dosen->kode_dosen) }}"
            maxlength="40" placeholder="Contoh: DSN-001" autocomplete="off" spellcheck="false" required
            aria-invalid="{{ $errors->has('kode_dosen') ? 'true' : 'false' }}">

        @error('kode_dosen')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="nidn">NIDN — opsional</label>

        <input id="nidn" name="nidn" type="text" inputmode="numeric"
            value="{{ $value('nidn', $dosen->nidn) }}" maxlength="40" autocomplete="off" spellcheck="false"
            aria-invalid="{{ $errors->has('nidn') ? 'true' : 'false' }}">

        <p class="help">
            Isi angka tanpa spasi. Kosongkan jika belum memiliki NIDN.
        </p>

        @error('nidn')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="field">
    <label for="gelar">Gelar — opsional</label>

    <input id="gelar" name="gelar" type="text" value="{{ $value('gelar', $dosen->gelar) }}" maxlength="100"
        placeholder="Contoh: Lc., M.A." aria-invalid="{{ $errors->has('gelar') ? 'true' : 'false' }}">

    @error('gelar')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

<div class="field">
    <label for="status">Status dosen</label>

    <select id="status" name="status" required aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}">
        @foreach ($statusOptions as $kodeStatus => $labelStatus)
            <option value="{{ $kodeStatus }}" @selected($value('status', $dosen->status) === $kodeStatus)>
                {{ $labelStatus }}
            </option>
        @endforeach
    </select>

    <p class="help">
        Pengaktifan kembali memerlukan akun aktif dengan peran Dosen.
        Status akses akun dikelola melalui menu Pengguna.
    </p>

    @error('status')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
