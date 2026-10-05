@extends('layouts.kegiatan')

@section('title', 'Detail Peserta Kelas')

@section('content')
    @php
        $krs = $peserta->krs;
        $registrasi = $krs?->registrasiSemester;
        $periode = $registrasi?->periodeAkademik;
        $riwayat = $registrasi?->riwayatStudi;
        $mahasiswa = $riwayat?->mahasiswa;
        $akun = $mahasiswa?->user;

        $jumlahPresensi = $rekapPresensi['jumlah'];
        $riwayatPresensi = $rekapPresensi['riwayat'];
    @endphp

    <div class="heading">
        <div>
            <p>Portal dosen</p>
            <h1>Detail peserta</h1>

            <p>
                {{ $kelas->kode }} —
                {{ $kelas->nama_mk_snapshot }}
            </p>
        </div>

        <a
            href="{{ route(
                'portal.dosen.peserta.index',
                $kelas->id
            ) }}"
        >
            Kembali ke daftar peserta
        </a>
    </div>

    <div class="card">
        <h2>Identitas akademik</h2>

        <dl>
            <dt>Nama mahasiswa</dt>
            <dd>{{ $akun?->nama ?? '—' }}</dd>

            <dt>NIM</dt>
            <dd>{{ $mahasiswa?->nim ?? '—' }}</dd>

            <dt>Kelas</dt>
            <dd>{{ $kelas->kode }}</dd>

            <dt>Mata kuliah</dt>
            <dd>{{ $kelas->nama_mk_snapshot }}</dd>

            <dt>Jumlah SKS</dt>
            <dd>{{ $kelas->sks_snapshot }}</dd>

            <dt>Periode akademik</dt>
            <dd>{{ $periode?->kode ?? '—' }}</dd>

            <dt>Semester studi</dt>
            <dd>{{ $registrasi?->semester_studi ?? '—' }}</dd>

            <dt>Status registrasi</dt>
            <dd>
                @if ($registrasi)
                    {{ \App\Models\RegistrasiSemester::STATUS[
                        $registrasi->status
                    ] ?? $registrasi->status }}
                @else
                    —
                @endif
            </dd>

            <dt>Status KRS</dt>
            <dd>
                @if ($krs)
                    {{ \App\Models\Krs::STATUS[
                        $krs->status
                    ] ?? $krs->status }}
                @else
                    —
                @endif
            </dd>

            <dt>Status peserta</dt>
            <dd>
                {{ \App\Models\DetailKrs::STATUS[
                    $peserta->status
                ] ?? $peserta->status }}
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Ringkasan presensi</h2>

        <dl>
            <dt>Total pertemuan tercatat</dt>
            <dd>{{ $rekapPresensi['total'] }}</dd>

            <dt>Sudah dicatat</dt>
            <dd>{{ $rekapPresensi['total_tercatat'] }}</dd>

            <dt>Hadir</dt>
            <dd>{{ $jumlahPresensi['hadir'] ?? 0 }}</dd>

            <dt>Izin</dt>
            <dd>{{ $jumlahPresensi['izin'] ?? 0 }}</dd>

            <dt>Sakit</dt>
            <dd>{{ $jumlahPresensi['sakit'] ?? 0 }}</dd>

            <dt>Alpa</dt>
            <dd>{{ $jumlahPresensi['alpa'] ?? 0 }}</dd>

            <dt>Belum dicatat</dt>
            <dd>
                {{ $jumlahPresensi[
                    \App\Models\Presensi::BELUM
                ] ?? 0 }}
            </dd>

            <dt>Persentase hadir</dt>
            <dd>
                {{ number_format(
                    $rekapPresensi['persentase_hadir'],
                    2,
                    ',',
                    '.'
                ) }}%
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Riwayat presensi</h2>

        @if ($riwayatPresensi->isEmpty())
            <p>
                Belum ada daftar presensi yang dibuat untuk peserta
                pada kelas ini.
            </p>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Pertemuan</th>
                            <th>Topik</th>
                            <th>Waktu rencana</th>
                            <th>Status</th>
                            <th>Dicatat pada</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($riwayatPresensi as $presensi)
                            @php
                                $pertemuan =
                                    $presensi->daftar?->pertemuan;
                            @endphp

                            <tr>
                                <td>
                                    {{ $pertemuan?->nomor
                                        ? 'Pertemuan '
                                            . $pertemuan->nomor
                                        : '—' }}
                                </td>

                                <td>
                                    {{ $pertemuan?->topik ?? '—' }}
                                </td>

                                <td>
                                    {{ $pertemuan?->mulai_rencana
                                        ? $pertemuan->mulai_rencana
                                            ->format('d-m-Y H:i')
                                        : '—' }}
                                </td>

                                <td>
                                    {{ \App\Models\Presensi::STATUS[
                                        $presensi->status
                                    ] ?? $presensi->status }}
                                </td>

                                <td>
                                    {{ $presensi->dicatat_at
                                        ? $presensi->dicatat_at
                                            ->format('d-m-Y H:i')
                                        : '—' }}
                                </td>

                                <td>
                                    {{ $presensi->catatan ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <p>
            Rekap hanya berasal dari presensi pada kelas ini.
            Data pribadi seperti alamat, telepon, tempat lahir,
            tanggal lahir, dan informasi peserta kelas lain tidak
            ditampilkan.
        </p>
    </div>
@endsection