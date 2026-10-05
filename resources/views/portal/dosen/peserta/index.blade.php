@extends('layouts.kegiatan')

@section('title', 'Peserta Kelas')

@section('content')
    <div class="heading">
        <div>
            <p>Portal dosen</p>
            <h1>Peserta kelas</h1>

            <p>
                {{ $kelas->kode }} —
                {{ $kelas->nama_mk_snapshot }}
            </p>
        </div>

        <a href="{{ route('portal.dosen.kelas.show', $kelas->id) }}">
            Kembali ke detail kelas
        </a>
    </div>

    <div class="card">
        <h2>Ringkasan peserta</h2>

        <dl>
            <dt>Kode kelas</dt>
            <dd>{{ $kelas->kode }}</dd>

            <dt>Mata kuliah</dt>
            <dd>{{ $kelas->nama_mk_snapshot }}</dd>

            <dt>Peserta aktif saat ini</dt>
            <dd>{{ number_format($jumlahAktif, 0, ',', '.') }}</dd>

            <dt>Riwayat peserta disahkan</dt>
            <dd>{{ number_format($jumlahRiwayat, 0, ',', '.') }}</dd>
        </dl>

        <p>
            Peserta aktif mengikuti status KRS, registrasi semester,
            riwayat studi, kelas, dan periode akademik yang masih aktif.
        </p>
    </div>

    <div class="card">
        <h2>Cari peserta</h2>

        <form
            method="get"
            action="{{ route('portal.dosen.peserta.index', $kelas->id) }}"
        >
            <div>
                <label for="q">
                    NIM atau nama mahasiswa
                </label>

                <input
                    id="q"
                    type="search"
                    name="q"
                    maxlength="100"
                    value="{{ $pencarian }}"
                >
            </div>

            <button type="submit">
                Cari
            </button>

            <a href="{{ route('portal.dosen.peserta.index', $kelas->id) }}">
                Reset
            </a>
        </form>

        @if ($errors->any())
            <div role="alert">
                <strong>Periksa kembali pencarian.</strong>

                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="card">
        <h2>Daftar peserta</h2>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIM</th>
                        <th>Nama mahasiswa</th>
                        <th>Semester</th>
                        <th>Status registrasi</th>
                        <th>Status KRS</th>
                        <th>Status peserta</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($peserta as $detail)
                        @php
                            $krs = $detail->krs;
                            $registrasi = $krs?->registrasiSemester;
                            $riwayat = $registrasi?->riwayatStudi;
                            $mahasiswa = $riwayat?->mahasiswa;
                            $akun = $mahasiswa?->user;
                        @endphp

                        <tr>
                            <td>
                                {{ $peserta->firstItem() + $loop->index }}
                            </td>

                            <td>
                                {{ $mahasiswa?->nim ?? '—' }}
                            </td>

                            <td>
                                {{ $akun?->nama ?? '—' }}
                            </td>

                            <td>
                                {{ $registrasi?->semester_studi ?? '—' }}
                            </td>

                            <td>
                                @if ($registrasi)
                                    {{ \App\Models\RegistrasiSemester::STATUS[
                                        $registrasi->status
                                    ] ?? $registrasi->status }}
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                @if ($krs)
                                    {{ \App\Models\Krs::STATUS[
                                        $krs->status
                                    ] ?? $krs->status }}
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                {{ \App\Models\DetailKrs::STATUS[
                                    $detail->status
                                ] ?? $detail->status }}
                            </td>

                            <td>
                                <a
                                    href="{{ route(
                                        'portal.dosen.peserta.show',
                                        [
                                            'kelas' => $kelas->id,
                                            'peserta' => $detail->id,
                                        ]
                                    ) }}"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                Belum ada peserta disahkan yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($peserta->hasPages())
            <div>
                {{ $peserta->links() }}
            </div>
        @endif
    </div>

    <div class="card">
        <p>
            Halaman ini hanya menampilkan identitas akademik yang
            diperlukan untuk kegiatan pembelajaran. Alamat, tanggal
            lahir, nomor telepon, dan informasi pribadi lainnya tidak
            ditampilkan.
        </p>
    </div>
@endsection