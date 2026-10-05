@extends('layouts.berkas')

@section('title', 'Profil Mahasiswa')

@section('content')
<div class="heading">
    <div>
        <p>Portal mahasiswa</p>
        <h1>Profil saya</h1>
        <p>
            Informasi identitas dan riwayat akademik mahasiswa.
        </p>
    </div>

    <a href="{{ route('portal.profil.edit') }}">
        Perbarui data pribadi
    </a>
</div>

    <div class="card">
        <h2>Identitas akun</h2>

        <dl>
            <dt>Nama lengkap</dt>
            <dd>{{ $user->nama }}</dd>

            <dt>Username</dt>
            <dd>{{ $user->username }}</dd>

            <dt>Email</dt>
            <dd>{{ $user->email ?: '—' }}</dd>

            <dt>Nomor telepon</dt>
            <dd>{{ $user->telepon ?: '—' }}</dd>

            <dt>Status akun</dt>
            <dd>{{ ucfirst($user->status) }}</dd>
        </dl>
    </div>

    <div class="card">
        <h2>Identitas mahasiswa</h2>

        <dl>
            <dt>NIM</dt>
            <dd>{{ $mahasiswa->nim }}</dd>

            <dt>Tempat lahir</dt>
            <dd>{{ $mahasiswa->tempat_lahir ?: '—' }}</dd>

            <dt>Tanggal lahir</dt>
            <dd>
                @if ($mahasiswa->tanggal_lahir)
                    {{ \Illuminate\Support\Carbon::parse($mahasiswa->tanggal_lahir)->format('d-m-Y') }}
                @else
                    —
                @endif
            </dd>

            <dt>Jenis kelamin</dt>
            <dd>
                @if ($mahasiswa->jenis_kelamin === 'L')
                    Laki-laki
                @elseif ($mahasiswa->jenis_kelamin === 'P')
                    Perempuan
                @else
                    —
                @endif
            </dd>

            <dt>Alamat</dt>
            <dd>{{ $mahasiswa->alamat ?: '—' }}</dd>
        </dl>
    </div>

    <div class="card">
        <h2>Status studi aktif</h2>

        @if ($riwayatAktif)
            @php
                $kurikulum = $riwayatAktif->kurikulum;
                $programStudi = $kurikulum?->programStudi;
                $dosenPa = $riwayatAktif->dosenPa;
            @endphp

            <dl>
                <dt>Status studi</dt>
                <dd>{{ ucfirst($riwayatAktif->status) }}</dd>

                <dt>Angkatan</dt>
                <dd>{{ $riwayatAktif->angkatan }}</dd>

                <dt>Program studi</dt>
                <dd>
                    @if ($programStudi)
                        {{ $programStudi->kode }} —
                        {{ $programStudi->nama }}
                    @else
                        —
                    @endif
                </dd>

                <dt>Jenjang</dt>
                <dd>{{ $programStudi?->jenjang ?? '—' }}</dd>

                <dt>Kurikulum</dt>
                <dd>
                    @if ($kurikulum)
                        {{ $kurikulum->kode }} —
                        {{ $kurikulum->nama }}
                    @else
                        —
                    @endif
                </dd>

                <dt>Periode mulai</dt>
                <dd>
                    @if ($riwayatAktif->periodeMulai)
                        {{ $riwayatAktif->periodeMulai->kode }}
                    @else
                        —
                    @endif
                </dd>

                <dt>Dosen pembimbing akademik</dt>
                <dd>
                    @if ($dosenPa)
                        {{ $dosenPa->user?->nama ?? '—' }}

                        @if ($dosenPa->kode_dosen)
                            — {{ $dosenPa->kode_dosen }}
                        @endif
                    @else
                        —
                    @endif
                </dd>
            </dl>
        @else
            <p>
                Tidak ada riwayat studi aktif pada akun ini.
            </p>
        @endif
    </div>

    <div class="card">
        <h2>Riwayat studi</h2>

        <p>
            Jumlah registrasi semester:
            <strong>{{ number_format($jumlahRegistrasi, 0, ',', '.') }}</strong>
        </p>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Angkatan</th>
                        <th>Program studi</th>
                        <th>Kurikulum</th>
                        <th>Periode mulai</th>
                        <th>Periode akhir</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($mahasiswa->riwayatStudi as $riwayat)
                        <tr>
                            <td>{{ $riwayat->angkatan }}</td>

                            <td>
                                {{ $riwayat->kurikulum?->programStudi?->nama ?? '—' }}
                            </td>

                            <td>
                                {{ $riwayat->kurikulum?->nama ?? '—' }}
                            </td>

                            <td>
                                {{ $riwayat->periodeMulai?->kode ?? '—' }}
                            </td>

                            <td>
                                {{ $riwayat->periodeAkhir?->kode ?? '—' }}
                            </td>

                            <td>{{ ucfirst($riwayat->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                Belum ada riwayat studi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>Registrasi semester</h2>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Semester studi</th>
                        <th>Rombel</th>
                        <th>Status</th>
                        <th>Revisi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $registrasiDitemukan = false;
                    @endphp

                    @foreach ($mahasiswa->riwayatStudi as $riwayat)
                        @foreach ($riwayat->registrasiSemester as $registrasi)
                            @php
                                $registrasiDitemukan = true;
                            @endphp

                            <tr>
                                <td>
                                    {{ $registrasi->periodeAkademik?->kode ?? '—' }}
                                </td>

                                <td>
                                    {{ $registrasi->semester_studi }}
                                </td>

                                <td>
                                    {{ $registrasi->rombel?->kode ?? '—' }}
                                </td>

                                <td>
                                    {{ ucfirst($registrasi->status) }}
                                </td>

                                <td>
                                    {{ $registrasi->revisi }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach

                    @if (! $registrasiDitemukan)
                        <tr>
                            <td colspan="5">
                                Belum ada registrasi semester.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <p>
            Data akademik seperti NIM, program studi, kurikulum,
            angkatan, status studi, dan dosen pembimbing hanya dapat
            diubah oleh pengelola akademik.
        </p>
    </div>
@endsection