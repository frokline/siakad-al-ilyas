@extends('layouts.siakad')

@section('title', 'Detail Pengguna')

@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}">Pengguna</a>
    <span> / {{ $user->nama }}</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $user->nama }}</h1>
            <p class="subtitle">Detail akun pengguna.</p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.users.edit', $user) }}" class="button">
                Edit Pengguna
            </a>

            <a href="{{ route('admin.users.index') }}" class="button button-secondary">
                Kembali
            </a>
        </div>
    </div>

    <section class="card user-form">
        <dl class="detail-grid">
            <dt>Nama lengkap</dt>
            <dd>{{ $user->nama }}</dd>

            <dt>Username</dt>
            <dd>{{ $user->username }}</dd>

            <dt>Email</dt>
            <dd>{{ $user->email }}</dd>

            <dt>Nomor telepon</dt>
            <dd>{{ $user->telepon ?: '—' }}</dd>

            <dt>Status</dt>
            <dd>
                <span class="badge {{ $user->isAktif() ? 'badge-active' : 'badge-inactive' }}">
                    {{ $user->isAktif() ? 'Aktif' : 'Nonaktif' }}
                </span>
            </dd>

            <dt>Peran</dt>
            <dd>
                {{ $user->roles->pluck('nama')->implode(', ') ?: 'Belum ditetapkan' }}
            </dd>

            <dt>Dibuat</dt>
            <dd>{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>

            <dt>Diperbarui</dt>
            <dd>{{ $user->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        </dl>
    </section>

    @if ($user->isAktif() && !$user->is(auth('web')->user()))
        <section class="card form-card user-form deactivate-panel">
            <h2>Nonaktifkan akun</h2>

            <p class="help">
                Akses akun akan dinonaktifkan. Data pengguna tetap tersimpan.
            </p>

            @error('version')
                <p class="error">
                    {{ $message }}
                    <a href="{{ route('admin.users.show', $user) }}">
                        Muat ulang data terbaru
                    </a>
                </p>
            @enderror

            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                @csrf
                @method('DELETE')

                <input type="hidden" name="version" value="{{ $version }}">

                <div class="field">
                    <label for="current_password">
                        Kata sandi Anda sebagai admin
                    </label>

                    <input type="password" id="current_password" name="current_password" maxlength="72"
                        autocomplete="current-password" required>

                    @error('current_password')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="button button-danger">
                    Nonaktifkan Akun
                </button>
            </form>
        </section>
    @endif
@endsection
