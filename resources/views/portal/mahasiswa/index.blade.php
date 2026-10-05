@extends('layouts.kegiatan')

@section('title', 'Portal Mahasiswa')

@section('content')
    <div class="heading">
        <div>
            <p>Portal mahasiswa</p>
            <h1>Selamat datang, {{ $user->nama }}</h1>
            <p>
                Ringkasan informasi akademik dan akses layanan mahasiswa.
            </p>
        </div>
    </div>

    <div class="card">
        <h2>Identitas mahasiswa</h2>

        <dl>
            <dt>Nama</dt>
            <dd>{{ $user->nama }}</dd>

            <dt>NIM</dt>
            <dd>{{ $mahasiswa->nim }}</dd>

            <dt>Status akun</dt>
            <dd>{{ ucfirst($user->status) }}</dd>

            <dt>Program studi</dt>
            <dd>
                {{ $riwayatAktif?->kurikulum?->programStudi?->nama ?? 'Belum tersedia' }}
            </dd>

            <dt>Kurikulum</dt>
            <dd>
                {{ $riwayatAktif?->kurikulum?->nama ?? 'Belum tersedia' }}
            </dd>

            <dt>Angkatan</dt>
            <dd>{{ $riwayatAktif?->angkatan ?? 'Belum tersedia' }}</dd>

            <dt>Dosen pembimbing akademik</dt>
            <dd>
                {{ $riwayatAktif?->dosenPa?->user?->nama ?? 'Belum ditentukan' }}
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Status semester</h2>

        @if ($registrasiAktif)
            <dl>
                <dt>Periode akademik</dt>
                <dd>
                    {{ $registrasiAktif->periodeAkademik?->kode ?? 'Belum tersedia' }}
                </dd>

                <dt>Semester studi</dt>
                <dd>{{ $registrasiAktif->semester_studi }}</dd>

                <dt>Status registrasi</dt>
                <dd>
                    {{ \App\Models\RegistrasiSemester::STATUS[$registrasiAktif->status] ?? $registrasiAktif->status }}
                </dd>

                <dt>Rombongan belajar</dt>
                <dd>{{ $registrasiAktif->rombel?->kode ?? 'Belum ditentukan' }}</dd>
            </dl>
        @else
            <p>Belum ada registrasi semester aktif.</p>
        @endif
    </div>

    <div class="card">
        <h2>Ringkasan akademik</h2>

        <dl>
            <dt>Jumlah riwayat studi</dt>
            <dd>{{ $jumlahRiwayat }}</dd>

            <dt>Jumlah registrasi semester</dt>
            <dd>{{ $jumlahRegistrasi }}</dd>

            <dt>Status studi aktif</dt>
            <dd>
                {{ $riwayatAktif
                    ? \App\Models\RiwayatStudi::STATUS[$riwayatAktif->status] ?? $riwayatAktif->status
                    : 'Tidak ada riwayat aktif' }}
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Layanan mahasiswa</h2>

        <nav aria-label="Layanan mahasiswa">
            @if (Route::has('portal.profil.show'))
                <p>
                    <a href="{{ route('portal.profil.show') }}">
                        Profil mahasiswa
                    </a>
                </p>
            @endif

            @if (Route::has('portal.krs.index'))
                <p>
                    <a href="{{ route('portal.krs.index') }}">
                        KRS saya
                    </a>
                </p>
            @endif

            @if (Route::has('portal.jadwal.index'))
                <p>
                    <a href="{{ route('portal.jadwal.index') }}">
                        Jadwal kuliah
                    </a>
                </p>
            @endif

            @if (Route::has('portal.presensi.index'))
                <p>
                    <a href="{{ route('portal.presensi.index') }}">
                        Presensi saya
                    </a>
                </p>
            @endif

            @if (Route::has('berkas.index'))
                <p>
                    <a href="{{ route('berkas.index') }}">
                        Berkas saya
                    </a>
                </p>
            @endif
        </nav>
    </div>
@endsection
