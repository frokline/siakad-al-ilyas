<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="same-origin">
    <title>KRS {{ $registrasi->riwayatStudi->mahasiswa->nim }} — {{ $registrasi->periodeAkademik->kode }}</title>
    <link rel="stylesheet" href="{{ asset('css/siakad.css') }}">
    <script src="{{ asset('js/krs-print.js') }}" defer></script>
</head>

<body class="krs-print-body">
    <div class="krs-print-toolbar" aria-label="Tindakan cetak">
        <a class="button secondary" href="{{ route('admin.krs.show', $krs) }}">Kembali ke KRS</a>
        <button id="krs-print-button" class="button" type="button">Cetak / simpan PDF</button>
        <noscript>Gunakan menu cetak browser atau Ctrl+P.</noscript>
    </div>

    <main class="krs-print-sheet">
        <header class="krs-print-header">
            <p class="krs-print-institusi">ILYAS INSTITUTE</p>
            <h1>KARTU RENCANA STUDI</h1>
            <p>Periode {{ $registrasi->periodeAkademik->kode }}</p>
        </header>

        <dl class="krs-print-identitas">
            <div>
                <dt>NIM</dt>
                <dd>{{ $registrasi->riwayatStudi->mahasiswa->nim }}</dd>
            </div>
            <div>
                <dt>Nama</dt>
                <dd>{{ $registrasi->riwayatStudi->mahasiswa->user->nama }}</dd>
            </div>
            <div>
                <dt>Program studi</dt>
                <dd>{{ $registrasi->riwayatStudi->kurikulum->programStudi->nama }}</dd>
            </div>
            <div>
                <dt>Semester studi</dt>
                <dd>{{ $registrasi->semester_studi }}</dd>
            </div>
            <div>
                <dt>Rombel</dt>
                <dd>{{ $registrasi->rombel->kode }}</dd>
            </div>
            <div>
                <dt>Paket</dt>
                <dd>{{ $registrasi->rombel->paketSemester->nama }} / versi
                    {{ $registrasi->rombel->paketSemester->versi }}</dd>
            </div>
            <div>
                <dt>Nomor KRS</dt>
                <dd>#{{ $krs->id }} / versi {{ $krs->versi }}</dd>
            </div>
        </dl>

        @include('admin.krs._mata_kuliah', ['untukCetak' => true])

        <section class="krs-print-pengesahan">
            <h2>Pengesahan akademik</h2>
            <p>Status: <strong>Disahkan</strong></p>
            <p>Admin akademik: <strong>{{ $krs->pengesah->nama }}</strong></p>
            <p>Tanggal:
                {{ $krs->disahkan_at->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
            </p>
        </section>

        <footer class="krs-print-footer">
            <p>Dicetak {{ $dicetakPada->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
                · {{ config('siakad.timezone', 'Asia/Makassar') }}</p>
            <p>Salinan KRS versi {{ $krs->versi }}. Status dan versi terbaru tersedia pada SIAKAD Ilyas Institute.
            </p>
        </footer>
    </main>
</body>

</html>
