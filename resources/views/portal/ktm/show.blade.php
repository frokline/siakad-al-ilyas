@extends('layouts.admin')

@section('title', 'Kartu Tanda Mahasiswa')

@section('content')
    @php
        // Sesuaikan nilai ini dengan nama logo yang benar di public/images/.
        $logoKampus = 'images/logo-revome.png';
        $namaKampus = 'ILYAS INSTITUTE';
        $namaResmi = 'INSTITUT KULIAH ONLINE ILMU SYAR’I';
        $sloganKampus = 'Iman, Ilmu, Amal, Dakwah, Watafaqquh Fiddin';
        $alamatKampus =
            'Jl. Trans Kalimantan, Komp. Bumi Angkasa Mandiri RT. 05, Kel. Handil Bakti, Kec. Alalak, Kabupaten Barito Kuala, Kalimantan Selatan';
        $emailKampus = 'ilyasinstitute.id@gmail.com';
        $teleponKampus = '0821-483-4449';
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
        }

        .ktm-card {
            position: relative;
            isolation: isolate;
            width: 100%;
            max-width: 560px;
            aspect-ratio: 85.6 / 54;
            overflow: hidden;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .14);
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
        }

        .ktm-card-header {
            display: flex;
            height: 29%;
            align-items: center;
            gap: 10px;
            padding: 3.5% 4%;
            background: #065f46;
            color: #fff;
        }

        .ktm-logo-frame {
            display: flex;
            width: 15%;
            height: 100%;
            flex: 0 0 15%;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 8px;
            background: #fff;
            padding: 4px;
        }

        .ktm-logo-frame img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .ktm-card-body {
            display: grid;
            height: 57%;
            grid-template-columns: minmax(0, 1fr) 23%;
            align-items: center;
            gap: 3%;
            padding: 3.5% 4%;
        }

        .ktm-field-label {
            width: 31%;
            flex: 0 0 31%;
            color: #64748b;
            font-size: clamp(7px, 1.65vw, 10px);
            line-height: 1.45;
        }

        .ktm-field-value {
            min-width: 0;
            color: #0f172a;
            font-size: clamp(8px, 1.9vw, 11px);
            font-weight: 700;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .ktm-photo {
            width: 100%;
            aspect-ratio: 3 / 4;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            border-radius: 7px;
            background: #f1f5f9;
        }

        .ktm-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ktm-card-footer {
            display: flex;
            height: 14%;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-top: 1px solid #d1fae5;
            background: #f0fdf4;
            padding: 0 4%;
            color: #065f46;
            font-size: clamp(6px, 1.35vw, 8px);
        }

        .ktm-card-footer span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

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
                width: 85.6mm !important;
                height: 54mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .ktm-card {
                width: 85.6mm !important;
                height: 54mm !important;
                max-width: none !important;
                aspect-ratio: auto !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .ktm-card-header {
                gap: 2mm !important;
            }

            .ktm-card-header h2 {
                font-size: 9pt !important;
            }

            .ktm-card-header p {
                font-size: 5pt !important;
            }

            .ktm-card-header .ktm-slogan {
                font-size: 4.2pt !important;
            }

            .ktm-field-label {
                font-size: 5.2pt !important;
            }

            .ktm-field-value {
                font-size: 6pt !important;
            }

            .ktm-card-footer {
                font-size: 4.3pt !important;
            }
        }
    </style>

    <div class="mx-auto w-full max-w-5xl space-y-6 pb-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Portal Mahasiswa</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Kartu Tanda Mahasiswa</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Periksa data pada kartu sebelum mencetaknya.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('portal.biodata.show') }}"
                    class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali
                    ke Biodata</a>
                <button type="button" onclick="window.print()"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 focus-visible:ring-offset-2">
                    <i class="bi bi-printer" aria-hidden="true"></i> Cetak KTM
                </button>
            </div>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-col gap-1">
                <h2 class="text-base font-bold text-slate-900">Pratinjau kartu</h2>
                <p class="text-sm text-slate-500">Ukuran cetak kartu 85,6 × 54 mm. Pada dialog cetak, pilih skala 100% dan
                    nonaktifkan header/footer browser bila tersedia.</p>
            </div>

            <div class="ktm-print-area flex w-full justify-center">
                <article class="ktm-card" aria-label="Kartu Tanda Mahasiswa {{ $user->nama ?? '' }}">
                    <div class="ktm-card-header">
                        <div class="ktm-logo-frame">
                            <img src="{{ asset($logoKampus) }}" alt="Logo Ilyas Institute"
                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden')">
                            <span
                                class="hidden text-center text-[7px] font-bold leading-tight text-emerald-900">ILYAS<br>INSTITUTE</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-extrabold tracking-wide sm:text-base">{{ $namaKampus }}</h2>
                            <p
                                class="ktm-slogan mt-0.5 text-[9px] font-semibold leading-tight text-emerald-100 sm:text-[10px]">
                                {{ $namaResmi }}</p>
                            <p class="ktm-slogan mt-1 text-[8px] italic leading-tight text-emerald-100/90">
                                {{ $sloganKampus }}</p>
                        </div>
                    </div>

                    <div class="ktm-card-body">
                        <div class="min-w-0 space-y-1.5 sm:space-y-2">
                            <div
                                class="mb-1.5 text-[9px] font-bold uppercase tracking-[0.14em] text-emerald-800 sm:text-[10px]">
                                Kartu Tanda Mahasiswa</div>
                            <div class="flex min-w-0 gap-1.5">
                                <span class="ktm-field-label">Nama</span>
                                <span class="ktm-field-value">{{ $user->nama ?: '—' }}</span>
                            </div>
                            <div class="flex min-w-0 gap-1.5">
                                <span class="ktm-field-label">NIM</span>
                                <span class="ktm-field-value font-mono">{{ $mahasiswa->nim ?: '—' }}</span>
                            </div>
                            <div class="flex min-w-0 gap-1.5">
                                <span class="ktm-field-label">Program Studi</span>
                                <span class="ktm-field-value">{{ $programStudi?->nama ?? '—' }}</span>
                            </div>
                            <div class="flex min-w-0 gap-1.5">
                                <span class="ktm-field-label">Angkatan</span>
                                <span class="ktm-field-value">{{ $riwayatAktif?->angkatan ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="ktm-photo self-center">
                            <img src="{{ route('portal.profil.foto') }}" alt="Foto mahasiswa"
                                onerror="this.style.display='none'">
                        </div>
                    </div>

                    <div class="ktm-card-footer">
                        <span>{{ $emailKampus }} · {{ $teleponKampus }}</span>
                        <span class="font-bold tracking-wide">KTM</span>
                    </div>
                </article>
            </div>

            <p class="mt-5 text-xs leading-5 text-slate-500">Catatan: kartu ini menggunakan data yang tersedia di sistem.
                Jika nama, NIM, program studi, angkatan, atau foto belum benar, perbarui data melalui menu yang sesuai
                sebelum mencetak.</p>
        </section>
    </div>
@endsection
