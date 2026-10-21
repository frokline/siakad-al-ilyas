<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biodata Mahasiswa - {{ $mahasiswa->nim ?: 'Mahasiswa' }}</title>
    <style>
        :root {
            --green: #065f46;
            --green-dark: #064e3b;
            --green-soft: #ecfdf5;
            --ink: #17212b;
            --muted: #536170;
            --line: #cbd5df;
            --line-soft: #e5eaf0;
        }

        * {
            box-sizing: border-box;
        }

        html {
            background: #eef2f5;
        }

        body {
            margin: 0;
            color: var(--ink);
            font: 11px/1.45 Arial, Helvetica, sans-serif;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            padding: 14px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 9px 15px;
            border: 1px solid var(--green);
            border-radius: 6px;
            background: var(--green);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            border-color: #cbd5e1;
            background: #fff;
            color: #334155;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 24px;
            padding: 12mm 13mm 13mm;
            background: #fff;
            box-shadow: 0 3px 18px #0f172a12;
        }

        /* Kop surat resmi: logo dan identitas institusi saja; foto mahasiswa tidak berada di bagian ini. */
        .letterhead {
            display: grid;
            grid-template-columns: 23mm minmax(0, 1fr) 23mm;
            align-items: center;
            gap: 4mm;
            padding: 0 0 5mm;
            border-bottom: 3px double #334155;
        }

        .logo-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22mm;
            height: 22mm;
        }

        .logo-box img {
            display: block;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .logo-fallback {
            display: none;
            text-align: center;
            color: var(--green);
            font-size: 8px;
            font-weight: 700;
        }

        .institution {
            text-align: center;
            min-width: 0;
        }

        .institution-name {
            margin: 0;
            color: #111827;
            font-size: 16px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: .25px;
            text-transform: uppercase;
        }

        .institution-motto {
            margin: 3px 0 0;
            color: var(--green-dark);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .45px;
        }

        .institution-address {
            margin: 4px 0 0;
            color: #334155;
            font-size: 9px;
            line-height: 1.45;
        }

        .institution-contact {
            margin: 2px 0 0;
            color: #334155;
            font-size: 9px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .letterhead-spacer {
            width: 22mm;
        }

        .document-title {
            margin: 5mm 0 1.5mm;
            text-align: center;
        }

        .document-title h1 {
            margin: 0;
            font-size: 15px;
            line-height: 1.3;
            font-weight: 800;
            letter-spacing: .45px;
        }

        .document-title p {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: 9px;
        }

        .meta-line {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 4px 16px;
            margin: 0 0 4mm;
            color: #334155;
            font-size: 9px;
        }

        .meta-line strong {
            color: #111827;
        }

        .section {
            margin-top: 4mm;
            break-inside: avoid;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 2mm;
            padding: 6px 8px;
            border: 1px solid #b7d8cb;
            border-left: 3px solid var(--green);
            background: #f0fdf7;
            color: var(--green-dark);
            font-size: 10.5px;
            font-weight: 800;
        }

        .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 17px;
            height: 17px;
            flex: 0 0 17px;
            border-radius: 50%;
            background: var(--green);
            color: #fff;
            font-size: 9px;
        }

        .identity-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 31mm;
            gap: 5mm;
            align-items: start;
        }

        .portrait-frame {
            width: 29mm;
            height: 38mm;
            margin: 0 auto;
            padding: 1mm;
            border: 1px solid #94a3b8;
            background: #f8fafc;
        }

        .portrait-frame img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .portrait-caption {
            margin-top: 2px;
            text-align: center;
            color: #64748b;
            font-size: 8px;
        }

        .fields {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            column-gap: 7mm;
        }

        .field {
            display: grid;
            grid-template-columns: minmax(31mm, 42%) minmax(0, 1fr);
            gap: 2mm;
            min-width: 0;
            padding: 4px 1px;
            border-bottom: 1px solid var(--line-soft);
            break-inside: avoid;
        }

        .field.wide {
            grid-column: 1 / -1;
        }

        .label {
            min-width: 0;
            color: var(--muted);
            font-weight: 400;
        }

        .value {
            min-width: 0;
            color: #111827;
            font-weight: 600;
            overflow-wrap: anywhere;
            white-space: pre-line;
        }

        .empty {
            color: #64748b;
            font-weight: 400;
            font-style: italic;
        }

        .family-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 3mm;
        }

        .family-card {
            min-width: 0;
            padding: 3mm;
            border: 1px solid var(--line);
            border-radius: 3px;
            break-inside: avoid;
        }

        .family-card h3 {
            margin: 0 0 2mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid var(--line);
            color: var(--green-dark);
            font-size: 10px;
        }

        .family-card .field {
            grid-template-columns: minmax(25mm, 40%) minmax(0, 1fr);
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-top: 6mm;
            padding-top: 2mm;
            border-top: 1px solid var(--line);
            color: #64748b;
            font-size: 8.5px;
        }

        .footer-note {
            max-width: 75%;
        }

        .signature-area {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20mm;
            margin: 10mm 8mm 0;
            break-inside: avoid;
        }

        .signature {
            text-align: center;
            color: #334155;
            font-size: 9px;
        }

        .signature-space {
            height: 18mm;
        }

        .signature-line {
            width: 48mm;
            max-width: 100%;
            margin: 0 auto;
            border-bottom: 1px solid #64748b;
        }

        .toolbar-note {
            width: 100%;
            text-align: center;
            color: #64748b;
            font-size: 11px;
        }

        @media (max-width: 740px) {
            .sheet {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 18px 15px;
                box-shadow: none;
            }

            .letterhead {
                grid-template-columns: 18mm minmax(0, 1fr);
                gap: 10px;
            }

            .logo-box {
                width: 17mm;
                height: 17mm;
            }

            .letterhead-spacer {
                display: none;
            }

            .institution-name {
                font-size: 13px;
            }

            .institution-motto,
            .institution-address,
            .institution-contact {
                font-size: 8px;
            }

            .fields,
            .family-grid {
                grid-template-columns: 1fr;
            }

            .identity-layout {
                grid-template-columns: minmax(0, 1fr) 27mm;
                gap: 3mm;
            }

            .portrait-frame {
                width: 26mm;
                height: 34mm;
            }

            .field.wide {
                grid-column: auto;
            }

            .family-card .field {
                grid-template-columns: minmax(31mm, 42%) minmax(0, 1fr);
            }

            .signature-area {
                gap: 8mm;
                margin-left: 0;
                margin-right: 0;
            }

            .toolbar {
                position: sticky;
                top: 0;
                z-index: 5;
                background: #eef2f5;
            }
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {

            html,
            body {
                background: #fff;
            }

            body {
                font-size: 9.5px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .letterhead {
                padding-bottom: 4mm;
            }

            .section {
                margin-top: 3.2mm;
            }

            .section-title {
                margin-bottom: 1.5mm;
                padding: 5px 7px;
            }

            .field {
                padding: 3px 1px;
            }

            .portrait-frame {
                width: 27mm;
                height: 35mm;
            }

            .footer {
                margin-top: 4mm;
            }

            .signature-area {
                margin-top: 8mm;
            }

            a {
                color: inherit;
                text-decoration: none;
            }
        }
    </style>
</head>

<body>
    @php
        $keluargaByJenis = ($dataKeluarga ?? collect())->keyBy('jenis_hubungan');
        $tanggal = static function ($value): string {
            if (!filled($value)) {
                return '—';
            }
            try {
                return \Illuminate\Support\Carbon::parse($value)->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                return '—';
            }
        };
        $nilai = static fn($value): string => filled($value) ? (string) $value : '—';
        $jenisKelamin = match ($mahasiswa->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '—',
        };
        $registrasiAktif = $riwayatAktif?->registrasiSemester
            ?->filter(fn($r) => in_array($r->status, ['terdaftar', 'aktif'], true))
            ->sortByDesc('semester_studi')
            ->first();

        // Ganti nama file ini sesuai logo yang benar-benar tersimpan di public/images/.
        $logoKampus = 'images/logo-revome.png';

        $identitas = [
            'NIM' => $mahasiswa->nim,
            'Nama mahasiswa' => $user->nama ?? null,
            'Program studi' => $riwayatAktif?->kurikulum?->programStudi?->nama,
            'Konsentrasi' => $mahasiswa->konsentrasi,
            'Jenis kelamin' => $jenisKelamin,
            'Tempat lahir' => $mahasiswa->tempat_lahir,
            'Tanggal lahir' => $tanggal($mahasiswa->tanggal_lahir),
            'Sistem kuliah' => $mahasiswa->sistem_kuliah,
            'Jenis pendaftaran' => $mahasiswa->jenis_pendaftaran,
            'Jalur pendaftaran' => $mahasiswa->jalur_pendaftaran,
            'Gelombang' => $mahasiswa->gelombang,
            'Tanggal awal masuk' => $tanggal($mahasiswa->tanggal_awal_masuk),
            'Tanggal daftar ulang' => $tanggal($mahasiswa->tanggal_daftar_ulang),
            'Periode masuk' => $riwayatAktif?->periodeMulai?->kode,
            'Angkatan' => $riwayatAktif?->angkatan,
            'Tahun kurikulum' => $riwayatAktif?->kurikulum?->tahun_berlaku,
            'Kelas / kelompok' => $registrasiAktif?->rombel?->kode,
            'Status studi' => $riwayatAktif ? ucfirst((string) $riwayatAktif->status) : 'Belum ada studi aktif',
            'Alamat pada data mahasiswa' => $mahasiswa->alamat,
        ];
        $pribadi = [
            'Agama' => $biodata?->agama,
            'Suku' => $biodata?->suku,
            'Kewarganegaraan' => $biodata?->kewarganegaraan,
            'NIK' => $biodata?->nik,
            'Nomor kartu keluarga' => $biodata?->nomor_kk,
            'NPWP' => $biodata?->npwp,
            'Nomor KPS' => $biodata?->nomor_kps,
            'Golongan darah' => $biodata?->golongan_darah,
            'Berat badan' => filled($biodata?->berat_badan) ? $biodata->berat_badan . ' kg' : null,
            'Tinggi badan' => filled($biodata?->tinggi_badan) ? $biodata->tinggi_badan . ' cm' : null,
            'Transportasi' => $biodata?->transportasi,
            'Status pernikahan' => $biodata?->status_nikah,
            'Ukuran jas almamater' => $biodata?->ukuran_jas_almamater,
            'Nomor HP' => $biodata?->nomor_hp,
            'Email pribadi' => $biodata?->email_pribadi,
            'Pekerjaan' => $biodata?->pekerjaan,
            'Instansi pekerjaan' => $biodata?->instansi_pekerjaan,
            'Penghasilan' => filled($biodata?->penghasilan)
                ? 'Rp ' . number_format((float) $biodata->penghasilan, 0, ',', '.')
                : null,
            'Nomor rekening' => $biodata?->nomor_rekening,
            'Nama pemilik rekening' => $biodata?->nama_rekening,
            'Nama bank' => $biodata?->nama_bank,
        ];
        $alamatKtp = [
            'Alamat KTP' => $alamatBiodata?->alamat_ktp,
            'RT KTP' => $alamatBiodata?->rt_ktp,
            'RW KTP' => $alamatBiodata?->rw_ktp,
            'Dusun KTP' => $alamatBiodata?->dusun_ktp,
            'Desa / kelurahan KTP' => $alamatBiodata?->desa_kelurahan_ktp,
            'Kecamatan KTP' => $alamatBiodata?->kecamatan_ktp,
            'Kota / kabupaten KTP' => $alamatBiodata?->kota_kabupaten_ktp,
            'Provinsi KTP' => $alamatBiodata?->provinsi_ktp,
            'Kode pos KTP' => $alamatBiodata?->kode_pos_ktp,
        ];
        $alamatDomisili = [
            'Sama dengan alamat KTP' =>
                $alamatBiodata === null ? null : ($alamatBiodata->domisili_sama_dengan_ktp ? 'Ya' : 'Tidak'),
            'Alamat domisili' => $alamatBiodata?->alamat_domisili,
            'RT domisili' => $alamatBiodata?->rt_domisili,
            'RW domisili' => $alamatBiodata?->rw_domisili,
            'Dusun domisili' => $alamatBiodata?->dusun_domisili,
            'Desa / kelurahan domisili' => $alamatBiodata?->desa_kelurahan_domisili,
            'Kecamatan domisili' => $alamatBiodata?->kecamatan_domisili,
            'Kota / kabupaten domisili' => $alamatBiodata?->kota_kabupaten_domisili,
            'Provinsi domisili' => $alamatBiodata?->provinsi_domisili,
            'Kode pos domisili' => $alamatBiodata?->kode_pos_domisili,
            'Status tinggal' => $alamatBiodata?->status_tinggal,
        ];
        $pendidikan = [
            'Jenis pendidikan asal' => $pendidikanAsal?->jenis_pendidikan_asal,
            'Perguruan tinggi asal' => $pendidikanAsal?->perguruan_tinggi,
            'Program studi asal' => $pendidikanAsal?->program_studi_asal,
            'IPK' => $pendidikanAsal?->ipk,
            'SKS diakui' => $pendidikanAsal?->sks_diakui,
            'Nomor ijazah' => $pendidikanAsal?->nomor_ijazah,
            'NISN' => $pendidikanAsal?->nisn,
        ];
        $keluargaFields = [
            'Nama lengkap' => 'nama_lengkap',
            'NIK' => 'nik',
            'Tanggal lahir' => 'tanggal_lahir',
            'Status hidup' => 'status_hidup',
            'Status kekerabatan' => 'status_kekerabatan',
            'Pendidikan terakhir' => 'pendidikan_terakhir',
            'Pekerjaan' => 'pekerjaan',
            'Penghasilan' => 'penghasilan',
            'Nomor telepon' => 'nomor_telepon',
            'Email' => 'email',
            'Alamat' => 'alamat',
        ];
        $judulKeluarga = ['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'];
        $berkasList = ($berkasBiodata ?? collect())->where('aktif', true);
    @endphp

    <div class="toolbar" aria-label="Aksi dokumen">
        <button type="button" class="button" onclick="window.print()">Cetak Biodata</button>
        <a class="button secondary" href="{{ route('portal.biodata.show') }}">Kembali ke Biodata</a>
        <p class="toolbar-note">Gunakan kertas A4 dan nonaktifkan header/footer browser bila ingin hasil cetak lebih
            bersih.</p>
    </div>

    <main class="sheet">
        <header class="letterhead">
            <div class="logo-box">
                <img src="{{ asset($logoKampus) }}" alt="Logo Ilyas Institute"
                    onerror="this.hidden=true; this.nextElementSibling.style.display='block'">
                <span class="logo-fallback">ILYAS<br>INSTITUTE</span>
            </div>
            <div class="institution">
                <h1 class="institution-name">Ilyas Institut Kuliah Online Ilmu Syar’i</h1>
                <p class="institution-motto">IMAN, ILMU, AMAL, DAKWAH, WATAFAQQUH FIDDIN</p>
                <p class="institution-address">Jl. Trans Kalimantan, Komp. Bumi Angkasa Mandiri RT. 05, Kel. Handil
                    Bakti,<br>
                    Kec. Alalak, Kabupaten Barito Kuala, Kalimantan Selatan</p>
                <p class="institution-contact">E-mail: ilyasinstitute.id@gmail.com &nbsp; | &nbsp; Telp: 0821-483-4449
                </p>
            </div>
            <div class="letterhead-spacer" aria-hidden="true"></div>
        </header>

        <div class="document-title">
            <h1>BIODATA MAHASISWA</h1>
            <p>Data mahasiswa berdasarkan informasi yang tercatat pada sistem akademik.</p>
        </div>
        <div class="meta-line">
            <span><strong>NIM:</strong> {{ $nilai($mahasiswa->nim) }}</span>
            <span><strong>Dicetak:</strong> {{ now()->translatedFormat('d F Y') }}</span>
        </div>

        <section class="section">
            <h2 class="section-title"><span class="section-number">1</span> Identitas Mahasiswa</h2>
            <div class="identity-layout">
                <div class="fields">
                    @foreach ($identitas as $label => $value)
                        <div
                            class="field {{ in_array($label, ['Nama mahasiswa', 'Program studi', 'Alamat pada data mahasiswa'], true) ? 'wide' : '' }}">
                            <div class="label">{{ $label }}</div>
                            <div class="value">{{ $nilai($value) }}</div>
                        </div>
                    @endforeach
                </div>
                <div>
                    <div class="portrait-frame">
                        <img src="{{ route('portal.profil.foto') }}" alt="Foto {{ $user->nama ?? 'mahasiswa' }}"
                            onerror="this.hidden=true">
                    </div>
                    <p class="portrait-caption">Foto mahasiswa</p>
                </div>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title"><span class="section-number">2</span> Data Pribadi, Kontak, dan Administrasi</h2>
            <div class="fields">
                @foreach ($pribadi as $label => $value)
                    <div class="field">
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $nilai($value) }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="section">
            <h2 class="section-title"><span class="section-number">3</span> Alamat dan Domisili</h2>
            <div class="fields">
                @foreach ($alamatKtp as $label => $value)
                    <div class="field {{ $label === 'Alamat KTP' ? 'wide' : '' }}">
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $nilai($value) }}</div>
                    </div>
                @endforeach
                @foreach ($alamatDomisili as $label => $value)
                    <div class="field {{ $label === 'Alamat domisili' ? 'wide' : '' }}">
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $nilai($value) }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="section">
            <h2 class="section-title"><span class="section-number">4</span> Data Orang Tua dan Wali</h2>
            <div class="family-grid">
                @foreach ($judulKeluarga as $jenis => $judul)
                    @php $orang = $keluargaByJenis->get($jenis); @endphp
                    <article class="family-card">
                        <h3>Data {{ $judul }}</h3>
                        @foreach ($keluargaFields as $label => $field)
                            @php
                                $value = data_get($orang, $field);
                                if ($field === 'tanggal_lahir') {
                                    $value = $tanggal($value);
                                }
                                if ($field === 'penghasilan' && filled($value)) {
                                    $value = 'Rp ' . number_format((float) $value, 0, ',', '.');
                                }
                            @endphp
                            <div class="field {{ $field === 'alamat' ? 'wide' : '' }}">
                                <div class="label">{{ $label }}</div>
                                <div class="value">{{ $nilai($value) }}</div>
                            </div>
                        @endforeach
                    </article>
                @endforeach
            </div>
        </section>

        <section class="section">
            <h2 class="section-title"><span class="section-number">5</span> Pendidikan Asal</h2>
            <div class="fields">
                @foreach ($pendidikan as $label => $value)
                    <div
                        class="field {{ in_array($label, ['Perguruan tinggi asal', 'Program studi asal'], true) ? 'wide' : '' }}">
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $nilai($value) }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="section">
            <h2 class="section-title"><span class="section-number">6</span> Kelengkapan Berkas Biodata</h2>
            <div class="fields">
                @forelse ($berkasList as $berkas)
                    <div class="field">
                        <div class="label">{{ str((string) $berkas->jenis_berkas)->replace('_', ' ')->title() }}</div>
                        <div class="value">Tercatat</div>
                    </div>
                @empty
                    <div class="field wide">
                        <div class="label">Status berkas</div>
                        <div class="value empty">Belum ada berkas aktif yang tercatat.</div>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="signature-area">
            <div class="signature">
                <div>Mahasiswa yang bersangkutan,</div>
                <div class="signature-space"></div>
                <div class="signature-line"></div>
                <div style="margin-top:3px;font-weight:700">{{ $nilai($user->nama ?? null) }}</div>
                <div>NIM. {{ $nilai($mahasiswa->nim) }}</div>
            </div>
            <div class="signature">
                <div>Mengetahui,</div>
                <div class="signature-space"></div>
                <div class="signature-line"></div>
                <div style="margin-top:3px">Petugas Akademik</div>
            </div>
        </div>

        <footer class="footer">
            <span class="footer-note">Dokumen ini dicetak dari sistem akademik Ilyas Institute berdasarkan data yang
                tersedia saat pencetakan. Periksa kembali data sebelum digunakan untuk keperluan resmi.</span>
            <span style="white-space:nowrap">NIM: {{ $nilai($mahasiswa->nim) }}</span>
        </footer>
    </main>
</body>

</html>
