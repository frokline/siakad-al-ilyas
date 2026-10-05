@extends('layouts.berkas')

@section('title', 'Perbarui Profil Mahasiswa')

@section('content')
    <div class="heading">
        <div>
            <p>Portal mahasiswa</p>
            <h1>Perbarui data pribadi</h1>
            <p>
                Anda hanya dapat memperbarui nomor telepon dan alamat.
            </p>
        </div>

        <a href="{{ route('portal.profil.show') }}">
            Kembali ke profil
        </a>
    </div>

    <div class="card">
        <h2>Identitas mahasiswa</h2>

        <dl>
            <dt>Nama lengkap</dt>
            <dd>{{ $user->nama }}</dd>

            <dt>NIM</dt>
            <dd>{{ $mahasiswa->nim }}</dd>

            <dt>Email</dt>
            <dd>{{ $user->email ?: '—' }}</dd>
        </dl>

        <p>
            Nama, NIM, email, program studi, kurikulum, angkatan,
            status akademik, dan dosen pembimbing tidak dapat
            diperbarui melalui formulir ini.
        </p>
    </div>

    <div class="card">
        <h2>Data yang dapat diperbarui</h2>

        <form
            method="post"
            action="{{ route('portal.profil.update') }}"
        >
            @csrf
            @method('patch')

            <div>
                <label for="telepon">
                    Nomor telepon
                </label>

                <input
                    id="telepon"
                    type="tel"
                    name="telepon"
                    maxlength="25"
                    autocomplete="tel"
                    inputmode="tel"
                    value="{{ old('telepon', $user->telepon) }}"
                    aria-describedby="bantuan-telepon"
                >

                <p id="bantuan-telepon">
                    Contoh: 0812-3456-7890 atau +62 812 3456 7890.
                </p>

                @error('telepon')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="alamat">
                    Alamat
                </label>

                <textarea
                    id="alamat"
                    name="alamat"
                    rows="6"
                    maxlength="1000"
                    autocomplete="street-address"
                    aria-describedby="bantuan-alamat"
                >{{ old('alamat', $mahasiswa->alamat) }}</textarea>

                <p id="bantuan-alamat">
                    Masukkan alamat tempat tinggal yang dapat
                    digunakan untuk kebutuhan administrasi.
                </p>

                @error('alamat')
                    <p role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit">
                Simpan perubahan
            </button>

            <a href="{{ route('portal.profil.show') }}">
                Batal
            </a>
        </form>
    </div>

    <div class="card">
        <h2>Keamanan data</h2>

        <p>
            Sistem hanya menerima nomor telepon dan alamat dari
            formulir ini. Data akademik tidak akan diperbarui
            meskipun dikirim melalui manipulasi formulir.
        </p>
    </div>
@endsection