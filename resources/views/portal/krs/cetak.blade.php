<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak KRS — {{ $registrasi->riwayatStudi->mahasiswa->nim }}</title>
    <style>
        *,
        ::before,
        ::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            background-color: #f8fafc;
            padding: 20px;
            line-height: 1.5;
            font-size: 13px;
        }

        .cetak-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #064e3b;
        }

        .header h2 {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2px;
        }

        .header p {
            font-size: 13px;
            color: #475569;
            margin-top: 4px;
            font-weight: 500;
        }

        .biodata-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 24px;
            margin-bottom: 24px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .biodata-item dt {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .biodata-item dd {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            text-align: left;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            font-size: 12px;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            color: #334155;
        }

        tfoot th {
            background-color: #f8fafc;
            font-size: 12px;
        }

        .pengesahan-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
        }

        .pengesahan-box p {
            margin-bottom: 4px;
            color: #334155;
        }

        .pengesahan-box strong {
            color: #0f172a;
        }

        .btn-print {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .btn-print button {
            background-color: #064e3b;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-print button:hover {
            background-color: #042f2e;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .cetak-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="cetak-container">
        <header class="header">
            <h1>ILYAS INSTITUTE</h1>
            <h2>KARTU RENCANA STUDI (KRS)</h2>
            <p>Periode Akademik {{ $registrasi->periodeAkademik->kode }}</p>
        </header>

        <dl class="biodata-grid">
            <div class="biodata-item">
                <dt>Nama Mahasiswa</dt>
                <dd>{{ $registrasi->riwayatStudi->mahasiswa->user->nama }}</dd>
            </div>
            <div class="biodata-item">
                <dt>NIM</dt>
                <dd>{{ $registrasi->riwayatStudi->mahasiswa->nim }}</dd>
            </div>
            <div class="biodata-item">
                <dt>Program Studi</dt>
                <dd>{{ $registrasi->riwayatStudi->kurikulum->programStudi->nama }}</dd>
            </div>
            <div class="biodata-item">
                <dt>Semester Studi</dt>
                <dd>Semester {{ $registrasi->semester_studi }}</dd>
            </div>
            <div class="biodata-item">
                <dt>Rombel</dt>
                <dd>{{ $registrasi->rombel->kode ?? '—' }}</dd>
            </div>
            <div class="biodata-item">
                <dt>Status KRS</dt>
                <dd>{{ \App\Models\Krs::STATUS[$krs->status] ?? $krs->status }}</dd>
            </div>
        </dl>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No.</th>
                    <th style="width: 120px;">Kode Kelas</th>
                    <th>Mata Kuliah</th>
                    <th style="width: 70px; text-align: center;">SKS</th>
                </tr>
            </thead>
            <tbody>
                @if ($krs->details && $krs->details->count() > 0)
                    @foreach ($krs->details as $detail)
                        <tr>
                            <td style="text-align: center;">{{ $loop->iteration }}</td>
                            <td style="font-weight: 600;">{{ $detail->kelasKuliah->kode }}</td>
                            <td>{{ $detail->kelasKuliah->nama_mk_snapshot }}</td>
                            <td style="text-align: center; font-weight: 600;">{{ $detail->kelasKuliah->sks_snapshot }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center; color: #64748b;">Mata kuliah belum dipilih.</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align: right;">Total SKS Terambil</th>
                    <th style="text-align: center; font-weight: 800;">{{ $krs->totalSks() }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="pengesahan-grid">
            <div class="pengesahan-box">
                <p><strong>Disahkan oleh:</strong></p>
                <p>{{ $krs->pengesah?->nama ?? '—' }}</p>
                <p style="margin-top: 8px;"><strong>Tanggal Pengesahan:</strong></p>
                <p>{{ $krs->disahkan_at?->setTimezone('Asia/Makassar')->format('d-m-Y H:i') ?? '—' }} WITA</p>
            </div>
            <div class="pengesahan-box" style="text-align: right;">
                <p><strong>Waktu Cetak:</strong></p>
                <p>{{ $dicetakPada->setTimezone('Asia/Makassar')->format('d-m-Y H:i') }} WITA</p>
            </div>
        </div>

        <div class="btn-print">
            <button type="button" onclick="window.print()">
                Cetak Dokumen
            </button>
        </div>
    </div>

</body>

</html>
