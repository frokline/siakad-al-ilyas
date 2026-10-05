<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Cetak KRS —
        {{ $registrasi->riwayatStudi->mahasiswa->nim }}
    </title>
</head>

<body>
    <header>
        <h1>ILYAS INSTITUTE</h1>
        <h2>Kartu Rencana Studi</h2>

        <p>
            Periode {{ $registrasi->periodeAkademik->kode }}
        </p>
    </header>

    <hr>

    <dl>
        <dt>Nama mahasiswa</dt>
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

        <dt>Semester studi</dt>
        <dd>{{ $registrasi->semester_studi }}</dd>

        <dt>Rombel</dt>
        <dd>{{ $registrasi->rombel->kode ?? '—' }}</dd>

        <dt>Status KRS</dt>
        <dd>
            {{ \App\Models\Krs::STATUS[$krs->status]
                ?? $krs->status }}
        </dd>
    </dl>

    <table border="1" cellspacing="0" cellpadding="8" width="100%">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode kelas</th>
                <th>Mata kuliah</th>
                <th>SKS</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($krs->details as $detail)
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
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <th colspan="3">Total SKS</th>
                <th>{{ $krs->totalSks() }}</th>
            </tr>
        </tfoot>
    </table>

    <p>
        Disahkan oleh:
        {{ $krs->pengesah?->nama ?? '—' }}
    </p>

    <p>
        Tanggal pengesahan:
        {{ $krs->disahkan_at
            ?->setTimezone('Asia/Makassar')
            ->format('d-m-Y H:i') ?? '—' }}
    </p>

    <p>
        Dicetak:
        {{ $dicetakPada
            ->setTimezone('Asia/Makassar')
            ->format('d-m-Y H:i') }}
    </p>

    <button type="button" onclick="window.print()">
        Cetak
    </button>
</body>
</html>