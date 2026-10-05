@extends('layouts.berkas')

@section('title', 'Detail KRS')

@section('content')
    <div class="heading">
        <div>
            <h1>Detail KRS</h1>
            <p>
                {{ $registrasi->periodeAkademik->kode }}
                · Semester {{ $registrasi->semester_studi }}
            </p>
        </div>

        <a href="{{ route('portal.krs.index') }}">
            Kembali ke daftar
        </a>
    </div>

    <div class="card">
        <h2>Informasi mahasiswa</h2>

        <dl>
            <dt>Nama</dt>
            <dd>
                {{ $registrasi->riwayatStudi->mahasiswa->user->nama }}
            </dd>

            <dt>NIM</dt>
            <dd>
                {{ $registrasi->riwayatStudi->mahasiswa->nim }}
            </dd>

            <dt>Program studi</dt>
            <dd>
                {{ $registrasi->riwayatStudi
                    ->kurikulum->programStudi->nama }}
            </dd>

            <dt>Kurikulum</dt>
            <dd>
                {{ $registrasi->riwayatStudi->kurikulum->nama }}
            </dd>

            <dt>Periode</dt>
            <dd>
                {{ $registrasi->periodeAkademik->kode }}
            </dd>

            <dt>Semester studi</dt>
            <dd>
                {{ $registrasi->semester_studi }}
            </dd>

            <dt>Rombel</dt>
            <dd>
                {{ $registrasi->rombel->kode ?? '—' }}
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Status KRS</h2>

        <dl>
            <dt>Status</dt>
            <dd>
                {{ \App\Models\Krs::STATUS[$krs->status]
                    ?? $krs->status }}
            </dd>

            <dt>Versi</dt>
            <dd>{{ $krs->versi }}</dd>

            <dt>Total SKS</dt>
            <dd>{{ $krs->totalSks() }} SKS</dd>

            <dt>Diajukan</dt>
            <dd>
                {{ $krs->diajukan_at
                    ?->setTimezone('Asia/Makassar')
                    ->format('d-m-Y H:i') ?? '—' }}
            </dd>

            <dt>Disahkan</dt>
            <dd>
                {{ $krs->disahkan_at
                    ?->setTimezone('Asia/Makassar')
                    ->format('d-m-Y H:i') ?? '—' }}
            </dd>

            <dt>Pengesah</dt>
            <dd>
                {{ $krs->pengesah?->nama ?? '—' }}
            </dd>

            <dt>Catatan</dt>
            <dd>
                {{ $krs->catatan ?: '—' }}
            </dd>
        </dl>

        @if ($bolehCetak)
            <p>
                <a
                    href="{{ route('portal.krs.cetak', $krs) }}"
                    target="_blank"
                    rel="noopener"
                >
                    Cetak KRS
                </a>
            </p>
        @endif
    </div>

    <div class="card">
        <h2>Mata kuliah</h2>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode kelas</th>
                    <th>Mata kuliah</th>
                    <th>SKS</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($krs->details as $detail)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            {{ $detail->kelasKuliah->kode }}
                        </td>

                        <td>
                            {{ $detail->kelasKuliah->nama_mk_snapshot }}
                        </td>

                        <td>
                            {{ $detail->kelasKuliah->sks_snapshot }}
                        </td>

                        <td>
                            {{ \App\Models\DetailKrs::STATUS[$detail->status]
                                ?? $detail->status }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Detail mata kuliah belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>{{ $krs->totalSks() }} SKS</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection