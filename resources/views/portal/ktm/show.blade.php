@extends('layouts.admin')

@section('title', 'Kartu Tanda Mahasiswa')

@section('content')
    @php
        // Aset KTM dan logo kampus.
        $backgroundKtm = 'images/ktm.png';
        $logoKampus = 'images/logo-ilyas-institut.png';

        // Identitas kampus.
        $namaKampus = 'ILYAS INSTITUT';
        $namaResmi = 'INSTITUT KULIAH ONLINE ILMU SYAR’I';
        $sloganKampus = 'Iman, Ilmu, Amal, Dakwah, Watafaqquh Fiddin';

        // Kontak resmi kampus.
        $instagramKampus = 'ilyasinstitut';
        $tiktokKampus = 'ilyasinstitute';
        $whatsappKampus = '08214834449';
        $youtubeKampus = 'IlyasinstInstitut';
        $emailKampus = 'ilyasinstitute.id@gmail.com';
        $websiteKampus = 'ilyasinstitut.com';

        $tanggalLahir = $mahasiswa->tanggal_lahir
            ? \Illuminate\Support\Carbon::parse($mahasiswa->tanggal_lahir)->translatedFormat('d M Y')
            : null;

        $tempatTanggalLahir = collect([$mahasiswa->tempat_lahir, $tanggalLahir])
            ->filter(fn($value) => filled($value))
            ->implode(' / ');

        $periodeMasuk = $riwayatAktif?->periodeMulai?->kode;
        $angkatan = $riwayatAktif?->angkatan;
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Pembungkus kartu menjadi acuan posisi untuk kartu dan layer pengesahan. */
        .ktm-print-area {
            position: relative;
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .ktm-card-wrap {
            position: relative;
            flex: 0 0 auto;
            width: 100%;
            max-width: 720px;
            aspect-ratio: 85.6 / 54;
        }

        .ktm-card {
            position: absolute;
            inset: 0;
            isolation: isolate;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #fff;
            color: #10231d;
            font-family: Arial, Helvetica, sans-serif;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .16);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .ktm-background {
            position: absolute;
            z-index: 0;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            pointer-events: none;
        }

        .ktm-layer {
            position: absolute;
            z-index: 1;
        }

        /* Header kampus. */
        .ktm-header {
            top: 3%;
            right: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.5%;
            height: 16.2%;
            padding: 0 5%;
            color: #fff;
            text-align: left;
        }

        .ktm-logo {
            display: block;
            flex: 0 0 auto;
            width: 9.5%;
            height: 88%;
            aspect-ratio: 1 / 1;
            padding: 3px;
            object-fit: contain;
            object-position: center;
            background: #fff;
            border-radius: 7px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .16);
        }

        .ktm-header-copy {
            flex: 0 1 auto;
            min-width: 0;
        }

        .ktm-campus-name {
            margin: 0;
            color: #fff;
            font-size: clamp(14px, 2.2vw, 23px);
            font-weight: 800;
            letter-spacing: .045em;
            line-height: 1.1;
            overflow-wrap: anywhere;
        }

        .ktm-campus-official {
            margin: 3px 0 0;
            color: #e8f5ee;
            font-size: clamp(8px, 1.15vw, 12px);
            font-weight: 700;
            line-height: 1.15;
        }

        .ktm-campus-slogan {
            margin: 4px 0 0;
            color: #d1e9dc;
            font-size: clamp(7px, .98vw, 10px);
            font-style: italic;
            line-height: 1.15;
        }

        /* Judul KTM. */
        .ktm-title {
            top: 24.8%;
            right: 4%;
            left: 4%;
            margin: 0;
            color: #075b43;
            font-size: clamp(10px, 1.45vw, 15px);
            font-weight: 800;
            letter-spacing: .08em;
            line-height: 1.2;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Foto mahasiswa. */
        .ktm-photo {
            top: 34%;
            left: 5.4%;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18.8%;
            height: 41%;
            overflow: hidden;
        }

        .ktm-photo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
        }

        /*
                                             * Biodata dibatasi sebelum area pengesahan agar baris terakhir tidak
                                             * tertutup tanda tangan dan nama rektor.
                                             */
        .ktm-fields {
            top: 34%;
            right: 34%;
            left: 27.8%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: clamp(3px, .65vw, 6px);
            height: 41%;
            min-width: 0;
        }

        .ktm-row {
            display: grid;
            grid-template-columns: minmax(0, 34%) 3% minmax(0, 63%);
            align-items: start;
            min-width: 0;
        }

        .ktm-label {
            color: #31594b;
            font-size: clamp(7px, 1.15vw, 12px);
            font-weight: 700;
            line-height: 1.3;
        }

        .ktm-colon {
            color: #7b9589;
            font-size: clamp(7px, 1.15vw, 12px);
            line-height: 1.3;
            text-align: center;
        }

        .ktm-value {
            display: block;
            min-width: 0;
            color: #10231d;
            font-size: clamp(8px, 1.25vw, 13px);
            font-weight: 700;
            line-height: 1.3;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .ktm-validity {
            top: 77%;
            right: 35%;
            left: 27.8%;
            margin: 0;
            color: #426557;
            font-size: clamp(7px, 1.05vw, 10px);
            font-style: italic;
            font-weight: 700;
            line-height: 1.2;
        }

        /* Footer kontak; posisi footer tetap di dalam area kartu. */
        .ktm-footer {
            position: absolute;
            z-index: 2;
            top: 84%;
            right: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            height: 16%;
            padding: 0 4%;
            border-top: 1.5px solid #d5b653;
            background: #074e3a;
            color: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .ktm-footer-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: clamp(8px, 1.15vw, 11px);
            font-weight: 700;
            white-space: nowrap;
        }

        .ktm-icon-badge {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            width: 18px;
            height: 18px;
            padding: 3px;
            border-radius: 50%;
            background: #fff;
        }

        .ktm-icon-badge img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .ktm-icon-group {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .ktm-divider {
            flex: 0 0 1px;
            width: 1px;
            height: 45%;
            background: rgba(255, 255, 255, .4);
        }

        /*
                                             * Blok pengesahan adalah sibling dari article.ktm-card, bukan anak
                                             * footer. Letaknya dihitung dari .ktm-card-wrap yang ukurannya sama
                                             * dengan kartu sehingga stabil di layar maupun saat dicetak.
                                             */
        .ktm-signature-block {
            top: 60%;
            right: 4%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            width: 30%;
            text-align: center;
            color: #10231d;
            z-index: 5;
            pointer-events: none;
        }

        .ktm-sign-title {
            display: block;
            margin: 0;
            font-size: clamp(7px, .9vw, 9px);
            font-weight: 800;
            letter-spacing: .04em;
            line-height: 1;
            white-space: nowrap;
        }

        .ktm-sign-img {
            display: block;
            width: 78%;
            max-width: 100%;
            max-height: clamp(28px, 4.3vw, 35px);
            height: auto;
            object-fit: contain;
            object-position: center;
            mix-blend-mode: multiply;
        }

        .ktm-sign-name {
            display: block;
            margin: 0;
            font-size: clamp(7px, .9vw, 9px);
            font-weight: 800;
            line-height: 1.1;
            white-space: nowrap;
        }

        .ktm-note {
            max-width: 720px;
        }

        /* Responsif untuk pratinjau pada layar kecil. */
        @media (max-width: 520px) {
            .ktm-campus-name {
                font-size: clamp(10px, 2.7vw, 15px);
            }

            .ktm-campus-official {
                font-size: clamp(6px, 1.45vw, 9px);
            }

            .ktm-campus-slogan {
                font-size: clamp(5px, 1.2vw, 7px);
            }

            .ktm-title {
                font-size: clamp(7px, 1.8vw, 10px);
            }

            .ktm-label,
            .ktm-colon {
                font-size: clamp(5.5px, 1.3vw, 8px);
            }

            .ktm-value {
                font-size: clamp(6px, 1.5vw, 9px);
            }

            .ktm-fields {
                gap: 3px;
            }

            .ktm-footer {
                column-gap: 2%;
                padding-right: 3%;
                padding-left: 3%;
            }

            .ktm-footer-item {
                gap: 4px;
                font-size: clamp(6px, 1.1vw, 8.5px);
            }

            .ktm-icon-badge {
                width: 14px;
                height: 14px;
                padding: 2px;
            }

            .ktm-signature-block {
                gap: 1.5px;
            }

            .ktm-sign-title,
            .ktm-sign-name {
                font-size: clamp(5px, 1vw, 7px);
            }

            .ktm-sign-img {
                max-height: 28px;
            }
        }

        /* Ukuran dan posisi presisi untuk hasil cetak 85,6 x 54 mm. */
        @media print {
            @page {
                size: 85.6mm 54mm;
                margin: 0;
            }

            html,
            body {
                width: 85.6mm !important;
                height: 54mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                background: #fff !important;
            }

            body * {
                visibility: hidden !important;
            }

            .ktm-print-area,
            .ktm-print-area * {
                visibility: visible !important;
            }

            .ktm-print-area {
                position: fixed !important;
                inset: 0 auto auto 0 !important;
                display: block !important;
                width: 85.6mm !important;
                height: 54mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .ktm-card-wrap {
                position: relative !important;
                width: 85.6mm !important;
                height: 54mm !important;
                max-width: none !important;
                margin: 0 !important;
                aspect-ratio: auto !important;
            }

            .ktm-card {
                position: absolute !important;
                inset: 0 !important;
                width: 85.6mm !important;
                height: 54mm !important;
                max-width: none !important;
                aspect-ratio: auto !important;
                overflow: hidden !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .ktm-campus-name {
                font-size: 8.2pt !important;
            }

            .ktm-campus-official {
                font-size: 4.6pt !important;
            }

            .ktm-campus-slogan {
                font-size: 3.9pt !important;
            }

            .ktm-title {
                font-size: 5.4pt !important;
            }

            .ktm-label,
            .ktm-colon {
                font-size: 4.5pt !important;
            }

            .ktm-value {
                font-size: 5pt !important;
            }

            .ktm-fields {
                gap: 1.1mm !important;
            }

            .ktm-validity {
                font-size: 4.1pt !important;
            }

            .ktm-footer {
                padding: 0 4mm !important;
                border-top-width: .35mm !important;
                background: #074e3a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .ktm-footer-item {
                gap: 1.2mm !important;
                font-size: 5pt !important;
            }

            .ktm-icon-badge {
                width: 3.5mm !important;
                height: 3.5mm !important;
                padding: .6mm !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .ktm-divider {
                height: 3.8mm !important;
                background: rgba(255, 255, 255, .5) !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Area tanda tangan tepat di atas garis emas footer. */
            .ktm-signature-block {
                top: 60% !important;
                right: 4% !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: .35mm !important;
                width: 30% !important;
                color: #10231d !important;
                text-align: center !important;
                z-index: 5 !important;
                visibility: visible !important;
            }

            .ktm-sign-title {
                font-size: 3.5pt !important;
                font-weight: 700 !important;
                line-height: 1 !important;
            }

            .ktm-sign-img {
                display: block !important;
                width: 78% !important;
                max-width: 100% !important;
                max-height: 7mm !important;
                height: auto !important;
                object-fit: contain !important;
                mix-blend-mode: multiply !important;
            }

            .ktm-sign-name {
                font-size: 3.5pt !important;
                font-weight: 750 !important;
                line-height: 1.05 !important;
                white-space: nowrap !important;
            }

            .ktm-background,
            .ktm-logo,
            .ktm-photo img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

    <div class="mx-auto w-full max-w-5xl space-y-6 pb-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Portal Mahasiswa</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Kartu Tanda Mahasiswa</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Periksa data pada kartu sebelum mencetaknya.</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('portal.biodata.show') }}"
                    class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Kembali ke Biodata
                </a>
                <button type="button" onclick="window.print()"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    Cetak KTM
                </button>
            </div>
        </header>

        @if (session('info'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900" role="status">
                {{ session('info') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-col gap-1">
                <h2 class="text-base font-bold text-slate-900">Pratinjau KTM</h2>
                <p class="text-sm leading-6 text-slate-500">
                    Ukuran kartu 85,6 × 54 mm. Saat mencetak, gunakan skala 100% dan matikan header/footer browser jika
                    tersedia.
                </p>
            </div>

            <div class="ktm-print-area">
                <div class="ktm-card-wrap">
                    <article class="ktm-card" aria-label="Kartu Tanda Mahasiswa {{ $user->nama ?? '' }}">
                        <img class="ktm-background" src="{{ asset($backgroundKtm) }}" alt="" aria-hidden="true"
                            loading="eager">

                        <div class="ktm-layer ktm-header">
                            <img class="ktm-logo" src="{{ asset($logoKampus) }}" alt="Logo Ilyas Institut" loading="eager"
                                onerror="this.style.display='none'">
                            <div class="ktm-header-copy">
                                <h2 class="ktm-campus-name">{{ $namaKampus }}</h2>
                                <p class="ktm-campus-official">{{ $namaResmi }}</p>
                                <p class="ktm-campus-slogan">{{ $sloganKampus }}</p>
                            </div>
                        </div>

                        <h3 class="ktm-layer ktm-title">Kartu Tanda Mahasiswa</h3>

                        <div class="ktm-layer ktm-photo">
                            <img src="{{ route('portal.profil.foto') }}" alt="Foto {{ $user->nama ?? 'mahasiswa' }}"
                                loading="eager" onerror="this.style.display='none'">
                        </div>

                        <div class="ktm-layer ktm-fields">
                            <div class="ktm-row">
                                <span class="ktm-label">Nama</span>
                                <span class="ktm-colon">:</span>
                                <span class="ktm-value">{{ $user->nama ?: '—' }}</span>
                            </div>
                            <div class="ktm-row">
                                <span class="ktm-label">NIM</span>
                                <span class="ktm-colon">:</span>
                                <span class="ktm-value">{{ $mahasiswa->nim ?: '—' }}</span>
                            </div>
                            <div class="ktm-row">
                                <span class="ktm-label">Program Studi</span>
                                <span class="ktm-colon">:</span>
                                <span class="ktm-value">{{ $programStudi?->nama ?? '—' }}</span>
                            </div>
                            <div class="ktm-row">
                                <span class="ktm-label">Tempat/Tgl. Lahir</span>
                                <span class="ktm-colon">:</span>
                                <span
                                    class="ktm-value">{{ filled($tempatTanggalLahir) ? $tempatTanggalLahir : '—' }}</span>
                            </div>
                            <div class="ktm-row">
                                <span class="ktm-label">Periode Masuk</span>
                                <span class="ktm-colon">:</span>
                                <span class="ktm-value">{{ $periodeMasuk ?: '—' }}</span>
                            </div>
                        </div>

                        <p class="ktm-layer ktm-validity">Berlaku selama menjadi mahasiswa</p>

                        <footer class="ktm-layer ktm-footer" aria-label="Kontak resmi Ilyas Institute">
                            <div class="ktm-footer-item">
                                <div class="ktm-icon-badge">
                                    <img src="{{ asset('images/website.svg') }}" alt="Website">
                                </div>
                                <span>{{ $websiteKampus }}</span>
                            </div>

                            <div class="ktm-divider" aria-hidden="true"></div>

                            <div class="ktm-footer-item">
                                <div class="ktm-icon-badge">
                                    <img src="{{ asset('images/whatsapp.svg') }}" alt="WhatsApp">
                                </div>
                                <span>{{ $whatsappKampus }}</span>
                            </div>

                            <div class="ktm-divider" aria-hidden="true"></div>

                            <div class="ktm-footer-item">
                                <div class="ktm-icon-group">
                                    <div class="ktm-icon-badge">
                                        <img src="{{ asset('images/instagram.svg') }}" alt="Instagram">
                                    </div>
                                    <div class="ktm-icon-badge">
                                        <img src="{{ asset('images/tiktok.svg') }}" alt="TikTok">
                                    </div>
                                </div>
                                <span>&#64;{{ $instagramKampus }}</span>
                            </div>
                        </footer>
                    </article>

                    {{-- Layer pengesahan sengaja berada di luar article dan footer. --}}
                    <div class="ktm-layer ktm-signature-block" aria-label="Pengesahan Rektor">
                        <span class="ktm-sign-title">Direktur</span>
                        <img src="{{ asset('images/ttd.png') }}" alt="Tanda Tangan Rektor" class="ktm-sign-img"
                            loading="eager">
                        <span class="ktm-sign-name">H. Muhammad Riza, S.Pd., M.Pd., M.A.</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
