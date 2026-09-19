@php
    $editing = $user->exists;

    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };

    $previousRoles = old('roles', $editing ? $user->roles->modelKeys() : []);

    $selectedRoles = collect(is_array($previousRoles) ? $previousRoles : [])
        ->filter(fn($id) => is_string($id) || is_int($id))
        ->map(fn($id) => (string) $id)
        ->all();
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version) }}">

    @error('version')
        <p class="error">
            {{ $message }}
            <a href="{{ route('admin.users.edit', $user) }}">
                Muat ulang data terbaru
            </a>
        </p>
    @enderror
@endif

<div class="form-grid">
    <div class="field">
        <label for="nama">Nama lengkap</label>
        <input type="text" id="nama" name="nama" value="{{ $value('nama', $user->nama) }}" maxlength="150"
            autocomplete="off" required>
        @error('nama')
            <p class="error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="{{ $value('username', $user->username) }}"
            minlength="3" maxlength="100" autocomplete="off" autocapitalize="none" spellcheck="false" required>
        @error('username')
            <p class="error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ $value('email', $user->email) }}" maxlength="190"
            autocomplete="off" required>
        @error('email')
            <p class="error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="telepon">Nomor telepon</label>
        <input type="tel" id="telepon" name="telepon" value="{{ $value('telepon', $user->telepon) }}"
            maxlength="30" autocomplete="off">
        @error('telepon')
            <p class="error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="field">
    <label for="status">Status akun</label>

    <select id="status" name="status" required>
        <option value="aktif" @selected($value('status', $user->status) === 'aktif')>
            Aktif
        </option>

        <option value="nonaktif" @selected($value('status', $user->status) === 'nonaktif')>
            Nonaktif
        </option>
    </select>

    @error('status')
        <p class="error">{{ $message }}</p>
    @enderror
</div>

<fieldset class="role-options">
    <legend>Peran pengguna</legend>

    @foreach ($roles as $role)
        <label class="checkbox-label" for="role-{{ $role->id }}">
            <input type="checkbox" id="role-{{ $role->id }}" name="roles[]" value="{{ $role->id }}"
                @checked(in_array((string) $role->id, $selectedRoles, true))>

            <span>{{ $role->nama }}</span>
        </label>
    @endforeach

    <p class="help">Pilih minimal satu peran sesuai tugas pengguna.</p>

    @error('roles')
        <p class="error">{{ $message }}</p>
    @enderror

    @foreach ($errors->get('roles.*') as $messages)
        @foreach ($messages as $message)
            <p class="error">{{ $message }}</p>
        @endforeach
    @endforeach
</fieldset>

@if ($editing && $user->is(auth('web')->user()))
    <p class="help">
        Akun sendiri harus tetap aktif dan memiliki peran Admin Akademik.
    </p>
@endif

<div class="form-grid">
    <div class="field">
        <label for="password">
            {{ $editing ? 'Kata sandi baru pengguna' : 'Kata sandi pengguna' }}
        </label>

        <input type="password" id="password" name="password" minlength="12" maxlength="72" autocomplete="new-password"
            @required(!$editing)>

        @if ($editing)
            <p class="help">
                Kosongkan jika kata sandi tidak diubah.
            </p>
        @endif

        @error('password')
            <p class="error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="password_confirmation">Ulangi kata sandi pengguna</label>

        <input type="password" id="password_confirmation" name="password_confirmation" maxlength="72"
            autocomplete="new-password" @required(!$editing)>
    </div>
</div>

<p class="help">
    Kata sandi pengguna minimal 12 karakter, mengandung huruf besar,
    huruf kecil, angka, dan simbol; maksimal 72 byte.
</p>

<div class="field admin-confirmation">
    <label for="current_password">Kata sandi Anda sebagai admin</label>

    <input type="password" id="current_password" name="current_password" maxlength="72" autocomplete="current-password"
        required>

    <p class="help">
        Masukkan kata sandi akun admin yang sedang digunakan untuk menyetujui perubahan.
    </p>

    @error('current_password')
        <p class="error">{{ $message }}</p>
    @enderror
</div>
