@extends('layouts.presensi')
@section('title', 'Login dosen')
@section('content')
    <section class="card login-card">
        <h1>Login dosen</h1>
        <p class="muted">Gunakan akun dosen yang diberikan bagian akademik.</p>
        @auth('web')
            <p>Akun yang sedang masuk belum mempunyai akses presensi. Gunakan tombol Keluar sebelum mengganti akun.</p>
        @else
            <form action="{{ route('presensi.login.store') }}" method="post" class="stack">
                @csrf
                <label for="username">Username</label>
                <input id="username" name="username" autocomplete="username" required maxlength="100"
                    value="{{ is_string(old('username')) ? old('username') : '' }}" autofocus>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required maxlength="1024">
                <button type="submit">Masuk</button>
            </form>
        @endauth
    </section>
@endsection
