@extends('layouts.berkas')
@section('title', 'Masuk ke Berkas Saya')
@section('content')
    <section class="card narrow">
        <h1>Masuk ke Berkas Saya</h1>
        <p>Gunakan akun SIAKAD yang diberikan bagian akademik.</p>
        @auth('web')
            <p>Akun yang sedang masuk belum memiliki akses. Pilih Keluar terlebih dahulu untuk mengganti akun.</p>
        @else
            <form method="post" action="{{ route('berkas.login.store') }}" class="stack">
                @csrf
                <label for="username">Username</label>
                <input id="username" name="username" required maxlength="100" autocomplete="username" autofocus
                    value="{{ is_string(old('username')) ? old('username') : '' }}">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required maxlength="1024" autocomplete="current-password">
                <button type="submit">Masuk</button>
            </form>
        @endauth
    </section>
@endsection
