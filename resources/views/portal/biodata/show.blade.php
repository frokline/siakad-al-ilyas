@extends('layouts.admin')

@section('content')

    <div x-data="{ activeTab: 'umum' }">

        <div class="mx-auto w-full max-w-screen-2xl space-y-5 px-0 py-2 sm:space-y-6">

            {{-- HEADER --}}

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="bg-[#065f46] px-5 py-6 text-white sm:px-8 sm:py-7">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <div class="mb-3 flex items-center gap-2">

                                <span
                                    class="inline-flex rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-[11px] font-semibold tracking-wider">

                                    PORTAL MAHASISWA

                                </span>

                            </div>

                            <h1 class="mb-2 text-2xl font-bold sm:text-3xl">Biodata Mahasiswa</h1>

                            <p class="text-sm leading-6 text-emerald-50 sm:text-base">

                                Kelola informasi pribadi, alamat, keluarga, dan pendidikan asal Anda.

                            </p>

                        </div>


                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            {{-- Tombol Edit Biodata --}}
                            <a href="{{ route('portal.biodata.edit') }}"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:bg-emerald-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-800">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                Edit Biodata
                            </a>

                            {{-- Dropdown Aksi --}}
                            <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false"
                                class="relative">

                                <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                                    aria-haspopup="true"
                                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-white/30 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:w-auto">

                                    <i class="bi bi-gear" aria-hidden="true"></i>
                                    Aksi

                                    <i class="bi bi-chevron-down text-xs transition-transform"
                                        :class="{ 'rotate-180': open }" aria-hidden="true">
                                    </i>
                                </button>

                                {{-- Menu dropdown --}}
                                <div x-cloak x-show="open" x-transition.origin.top.right role="menu"
                                    class="absolute right-0 z-50 mt-2 w-full min-w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg sm:w-56">

                                    {{-- Cetak Biodata --}}
                                    @if (Route::has('portal.biodata.cetak'))
                                        <a href="{{ route('portal.biodata.cetak') }}" role="menuitem"
                                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-900">
                                            <i class="bi bi-printer text-base text-emerald-700" aria-hidden="true"></i>
                                            <span>Cetak Biodata</span>
                                        </a>
                                    @else
                                        <div role="menuitem" aria-disabled="true"
                                            class="flex cursor-not-allowed items-center gap-3 px-4 py-3 text-sm text-slate-400"
                                            title="Fitur cetak biodata belum diaktifkan">
                                            <i class="bi bi-printer text-base" aria-hidden="true"></i>
                                            <span class="flex-1">Cetak Biodata</span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wide">
                                                Segera
                                            </span>
                                        </div>
                                    @endif

                                    <div class="mx-3 border-t border-slate-100"></div>

                                    {{-- KTM --}}
                                    @if (Route::has('portal.ktm.show'))
                                        <a href="{{ route('portal.ktm.show') }}" role="menuitem"
                                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-emerald-50 hover:text-emerald-900">
                                            <i class="bi bi-person-vcard text-base text-emerald-700" aria-hidden="true"></i>
                                            <span>Kartu Tanda Mahasiswa (KTM)</span>
                                        </a>
                                    @else
                                        <div role="menuitem" aria-disabled="true"
                                            class="flex cursor-not-allowed items-center gap-3 px-4 py-3 text-sm text-slate-400"
                                            title="Fitur KTM belum diaktifkan">
                                            <i class="bi bi-person-vcard text-base" aria-hidden="true"></i>
                                            <span class="flex-1">Cetak KTM</span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wide">
                                                Segera
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>


                    </div>

                </div>

                <div class="grid grid-cols-1 gap-0 lg:grid-cols-[240px_minmax(0,1fr)]">

                    {{-- Kolom kiri: foto dan menu --}}

                    <aside class="min-w-0 overflow-hidden border-b border-slate-200 bg-white lg:border-b-0 lg:border-r">

                        <div
                            class="relative flex min-h-[220px] items-center justify-center bg-slate-50 px-3 py-4 sm:min-h-[280px] lg:min-h-0">

                            <img src="{{ route('portal.profil.foto') }}"
                                alt="Foto {{ $mahasiswa->user->nama ?? 'mahasiswa' }}"
                                class="h-auto max-h-[420px] w-full max-w-[250px] object-contain object-center">

                            <a href="{{ route('portal.profil.edit') }}"
                                class="absolute right-3 top-3 inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800">

                                Ganti Foto

                            </a>

                        </div>

                        <nav class="divide-y divide-slate-200 text-sm">

                            <a href="#biodata"
                                class="block border-l-2 border-emerald-700 bg-emerald-50 px-3 py-3 font-semibold text-emerald-800">

                                Biodata

                            </a>

                            <a href="{{ route('portal.profil.show') }}"
                                class="block px-3 py-3 text-slate-600 hover:bg-slate-50">

                                Riwayat Pendaftaran

                            </a>

                        </nav>

                    </aside>

                    {{-- Kolom kanan: tabel biodata --}}

                    <section id="biodata" class="min-w-0 overflow-hidden border border-slate-200 bg-white">

                        <div class="grid grid-cols-1 md:grid-cols-2 md:divide-x md:divide-slate-200">

                            {{-- Kelompok kolom kiri --}}

                            <dl class="divide-y divide-slate-200">

                                @php

                                    $barisKiri = [
                                        'NIM' => $mahasiswa->nim,

                                        'Nama Mahasiswa' => $user->nama ?? ($mahasiswa->user->nama ?? null),

                                        'Program Studi' => $riwayatAktif?->kurikulum?->programStudi?->nama,

                                        'Konsentrasi' => $mahasiswa->konsentrasi,

                                        'Periode Masuk' => $riwayatAktif?->periodeMulai?->kode,

                                        'Tahun Masuk' => $riwayatAktif?->angkatan,

                                        'Tahun Kurikulum' => $riwayatAktif?->kurikulum?->tahun_berlaku,

                                        'Sistem Kuliah' => $mahasiswa->sistem_kuliah,

                                        'Kelas / Kelompok' => $riwayatAktif?->registrasiSemester

                                            ?->filter(
                                                fn($registrasi) => in_array(
                                                    $registrasi->status,

                                                    ['terdaftar', 'aktif'],

                                                    true,
                                                ),
                                            )

                                            ->sortByDesc('semester_studi')

                                            ->first()?->rombel?->kode,

                                        'Jenis Pendaftaran' => $mahasiswa->jenis_pendaftaran,
                                    ];

                                @endphp

                                @foreach ($barisKiri as $label => $nilai)
                                    <div
                                        class="grid grid-cols-1 gap-1 px-4 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3 sm:py-3.5">

                                        <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                        <dd class="break-words text-slate-900">{{ filled($nilai) ? $nilai : '—' }}</dd>

                                    </div>
                                @endforeach

                            </dl>

                            {{-- Kelompok kolom kanan --}}

                            <dl class="divide-y divide-slate-200">

                                @php

                                    $barisKanan = [
                                        'Jalur Pendaftaran' => $mahasiswa->jalur_pendaftaran,

                                        'Gelombang' => $mahasiswa->gelombang,

                                        'Tanggal Awal Masuk' => $mahasiswa->tanggal_awal_masuk
                                            ? \Illuminate\Support\Carbon::parse(
                                                $mahasiswa->tanggal_awal_masuk,
                                            )->translatedFormat('j F Y')
                                            : null,

                                        'Tanggal Daftar Ulang' => $mahasiswa->tanggal_daftar_ulang
                                            ? \Illuminate\Support\Carbon::parse(
                                                $mahasiswa->tanggal_daftar_ulang,
                                            )->translatedFormat('j F Y')
                                            : null,

                                        'Status Mahasiswa' => $user->status === 'aktif' ? 'Aktif' : 'Tidak Aktif',
                                    ];

                                @endphp

                                @foreach ($barisKanan as $label => $nilai)
                                    <div
                                        class="grid grid-cols-1 gap-1 px-4 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3 sm:py-3.5">

                                        <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                        <dd class="break-words text-slate-900">{{ filled($nilai) ? $nilai : '—' }}</dd>

                                    </div>
                                @endforeach

                            </dl>

                        </div>

                    </section>

                </div>

            </div>

            {{-- NOTIFIKASI --}}

            @if (session('info'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 shadow-sm">

                    <i class="bi bi-check-circle-fill mr-2"></i>

                    {{ session('info') }}

                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-900">

                    <strong>Periksa kembali data Anda.</strong>

                    <ul class="mb-0 mt-2 list-inside list-disc">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>
            @endif

            @php

                $keluarga = $dataKeluarga->keyBy('jenis_hubungan');

                $umum = [
                    'Tempat Lahir' => $mahasiswa->tempat_lahir,

                    'Tanggal Lahir' => $mahasiswa->tanggal_lahir?->format('d-m-Y'),

                    'Jenis Kelamin' => $mahasiswa->labelJenisKelamin(),

                    'Agama' => $biodata?->agama,

                    'Suku' => $biodata?->suku,

                    'Kewarganegaraan' => $biodata?->kewarganegaraan,

                    'NIK' => $biodata?->nik,

                    'Nomor Kartu Keluarga' => $biodata?->nomor_kk,

                    'NPWP' => $biodata?->npwp,

                    'Nomor KPS' => $biodata?->nomor_kps,

                    'Golongan Darah' => $biodata?->golongan_darah,

                    'Berat Badan' => $biodata?->berat_badan ? $biodata->berat_badan . ' kg' : null,

                    'Tinggi Badan' => $biodata?->tinggi_badan ? $biodata->tinggi_badan . ' cm' : null,

                    'Transportasi' => $biodata?->transportasi,

                    'Status Pernikahan' => $biodata?->status_nikah,

                    'Ukuran Jas Almamater' => $biodata?->ukuran_jas_almamater,

                    'Nomor HP' => $biodata?->nomor_hp,

                    'Email Pribadi' => $biodata?->email_pribadi,

                    'Pekerjaan' => $biodata?->pekerjaan,

                    'Instansi Pekerjaan' => $biodata?->instansi_pekerjaan,

                    'Penghasilan' => $biodata?->penghasilan
                        ? 'Rp ' . number_format($biodata->penghasilan, 0, ',', '.')
                        : null,

                    'Nomor Rekening' => $biodata?->nomor_rekening,

                    'Nama Pemilik Rekening' => $biodata?->nama_rekening,

                    'Nama Bank' => $biodata?->nama_bank,
                ];

                $domisili = [
                    'Alamat KTP' => $alamatBiodata?->alamat_ktp,

                    'RT KTP' => $alamatBiodata?->rt_ktp,

                    'RW KTP' => $alamatBiodata?->rw_ktp,

                    'Dusun KTP' => $alamatBiodata?->dusun_ktp,

                    'Desa/Kelurahan KTP' => $alamatBiodata?->desa_kelurahan_ktp,

                    'Kecamatan KTP' => $alamatBiodata?->kecamatan_ktp,

                    'Kota/Kabupaten KTP' => $alamatBiodata?->kota_kabupaten_ktp,

                    'Provinsi KTP' => $alamatBiodata?->provinsi_ktp,

                    'Kode Pos KTP' => $alamatBiodata?->kode_pos_ktp,

                    'Domisili Sama dengan KTP' => $alamatBiodata?->domisili_sama_dengan_ktp ? 'Ya' : 'Tidak',

                    'Alamat Domisili' => $alamatBiodata?->alamat_domisili,

                    'RT Domisili' => $alamatBiodata?->rt_domisili,

                    'RW Domisili' => $alamatBiodata?->rw_domisili,

                    'Dusun Domisili' => $alamatBiodata?->dusun_domisili,

                    'Desa/Kelurahan Domisili' => $alamatBiodata?->desa_kelurahan_domisili,

                    'Kecamatan Domisili' => $alamatBiodata?->kecamatan_domisili,

                    'Kota/Kabupaten Domisili' => $alamatBiodata?->kota_kabupaten_domisili,

                    'Provinsi Domisili' => $alamatBiodata?->provinsi_domisili,

                    'Kode Pos Domisili' => $alamatBiodata?->kode_pos_domisili,

                    'Status Tinggal' => $alamatBiodata?->status_tinggal,
                ];

                $pendidikan = [
                    'Jenis Pendidikan Asal' => $pendidikanAsal?->jenis_pendidikan_asal,

                    'Perguruan Tinggi Asal' => $pendidikanAsal?->perguruan_tinggi,

                    'Program Studi Asal' => $pendidikanAsal?->program_studi_asal,

                    'IPK' => $pendidikanAsal?->ipk,

                    'SKS Diakui' => $pendidikanAsal?->sks_diakui,

                    'Nomor Ijazah' => $pendidikanAsal?->nomor_ijazah,

                    'NISN' => $pendidikanAsal?->nisn,
                ];

                $formatNilai = static fn($nilai) => filled($nilai) ? $nilai : '-';

            @endphp

            {{-- NAVIGASI TAB --}}

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="p-4 sm:p-5">

                    <div class="mb-3 flex items-center gap-2">

                        <i class="text-xl text-emerald-700"></i>

                        <h2 class="text-base font-bold text-slate-900">Informasi Biodata</h2>

                    </div>

                    <ul class="flex flex-nowrap gap-2 overflow-x-auto pb-1" id="biodataTabs" role="tablist"
                        style="scrollbar-width:thin;">

                        @foreach ([
            'umum' => 'Informasi Umum',

            'domisili' => 'Domisili',

            'orang-tua' => 'Orang Tua',

            'wali' => 'Wali',

            'pendidikan-asal' => 'Perguruan Tinggi Asal',
        ] as $id => $judul)
                            <li class="shrink-0" role="presentation">

                                <button class="rounded-xl px-4 py-3 text-sm font-semibold transition"
                                    :class="activeTab === '{{ $id }}' ? 'bg-[#065f46] text-white shadow-sm' :
                                    
                                        'bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-800'"
                                    id="{{ $id }}-tab" @click="activeTab = '{{ $id }}'" role="tab"
                                    aria-controls="{{ $id }}"
                                    :aria-selected="activeTab === '{{ $id }}' ? 'true' : 'false'">

                                    {{ $judul }}

                                </button>

                            </li>
                        @endforeach

                    </ul>

                </div>

            </div>

            <div id="biodataTabsContent" class="space-y-5">

                {{-- INFORMASI UMUM --}}

                <div x-show="activeTab === 'umum'" x-cloak id="umum" role="tabpanel" aria-labelledby="umum-tab"
                    tabindex="0" class="space-y-5">

                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        <div class="grid grid-cols-1 lg:grid-cols-2">

                            {{-- KOLOM KIRI --}}

                            <div class="min-w-0 p-4 sm:p-5 lg:border-r lg:border-slate-200">

                                <section>

                                    <h3 class="mb-3 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                        Umum</h3>

                                    <dl class="divide-y divide-slate-100">

                                        @foreach ([
            'Jenis Kelamin' => 'Jenis Kelamin',

            'Tempat Lahir' => 'Tempat Lahir',

            'Tanggal Lahir' => 'Tanggal Lahir',

            'Agama' => 'Agama',

            'Suku' => 'Suku',

            'Berat Badan (Kg)' => 'Berat Badan',

            'Tinggi Badan (cm)' => 'Tinggi Badan',

            'Golongan Darah' => 'Golongan Darah',

            'Transportasi' => 'Transportasi',
        ] as $label => $key)
                                            <div
                                                class="grid grid-cols-1 gap-1 px-1 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3">

                                                <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                                <dd class="break-words text-slate-900">

                                                    {{ $formatNilai($umum[$key] ?? null) }}</dd>

                                            </div>
                                        @endforeach

                                    </dl>

                                </section>

                                <section class="mt-6">

                                    <h3 class="mb-3 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                        Kontak</h3>

                                    <dl class="divide-y divide-slate-100">

                                        @foreach ([
            'No. HP' => 'Nomor HP',

            'Email Pribadi' => 'Email Pribadi',
        ] as $label => $key)
                                            <div
                                                class="grid grid-cols-1 gap-1 px-1 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3">

                                                <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                                <dd class="break-words text-slate-900">

                                                    {{ $formatNilai($umum[$key] ?? null) }}</dd>

                                            </div>
                                        @endforeach

                                    </dl>

                                </section>

                            </div>

                            {{-- KOLOM KANAN --}}

                            <div class="min-w-0 p-4 sm:p-5">

                                <section>

                                    <h3 class="mb-3 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                        Administrasi</h3>

                                    <dl class="divide-y divide-slate-100">

                                        @foreach ([
            'Kewarganegaraan' => 'Kewarganegaraan',

            'NIK / No. KTP' => 'NIK',

            'No. KK' => 'Nomor Kartu Keluarga',

            'NPWP' => 'NPWP',

            'No. KPS' => 'Nomor KPS',

            'Status Nikah' => 'Status Pernikahan',

            'Ukuran Jas Almamater' => 'Ukuran Jas Almamater',
        ] as $label => $key)
                                            <div
                                                class="grid grid-cols-1 gap-1 px-1 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3">

                                                <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                                <dd class="break-words text-slate-900">

                                                    {{ $formatNilai($umum[$key] ?? null) }}</dd>

                                            </div>
                                        @endforeach

                                    </dl>

                                </section>

                                <section class="mt-6">

                                    <h3 class="mb-3 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                        Pekerjaan</h3>

                                    <dl class="divide-y divide-slate-100">

                                        @foreach ([
            'Pekerjaan' => 'Pekerjaan',

            'Instansi Pekerjaan' => 'Instansi Pekerjaan',

            'Penghasilan' => 'Penghasilan',
        ] as $label => $key)
                                            <div
                                                class="grid grid-cols-1 gap-1 px-1 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3">

                                                <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                                <dd class="break-words text-slate-900">

                                                    {{ $formatNilai($umum[$key] ?? null) }}</dd>

                                            </div>
                                        @endforeach

                                    </dl>

                                </section>

                                <section class="mt-6">

                                    <h3 class="mb-3 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                        Bank</h3>

                                    <dl class="divide-y divide-slate-100">

                                        @foreach ([
            'No. Rekening' => 'Nomor Rekening',

            'Nama Rekening' => 'Nama Pemilik Rekening',

            'Nama Bank' => 'Nama Bank',
        ] as $label => $key)
                                            <div
                                                class="grid grid-cols-1 gap-1 px-1 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3">

                                                <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                                <dd class="break-words text-slate-900">

                                                    {{ $formatNilai($umum[$key] ?? null) }}</dd>

                                            </div>
                                        @endforeach

                                    </dl>

                                </section>

                            </div>

                        </div>

                    </div>

                    {{-- BERKAS YANG SUDAH DIUNGGAH --}}

                    @php

                        $berkasList = $berkasBiodata ?? collect();

                    @endphp

                    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        <header class="flex items-center gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">

                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">

                                <i class="bi bi-folder2-open text-xl"></i>

                            </span>

                            <div>

                                <h2 class="text-base font-bold text-slate-900">Berkas yang Sudah Diunggah</h2>

                                <p class="text-sm text-slate-500">Daftar dokumen pendukung biodata mahasiswa.</p>

                            </div>

                        </header>

                        <div class="space-y-3 p-5 sm:p-6">

                            @forelse ($berkasList as $berkas)
                                <div
                                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 p-4">

                                    <div class="flex items-center gap-3">

                                        <i class="bi bi-file-earmark-text text-xl text-slate-400"></i>

                                        <div>

                                            <p class="font-semibold text-slate-800">

                                                {{ str($berkas->jenis_berkas)->replace('_', ' ')->title() }}</p>

                                            <p class="mt-1 text-xs text-slate-500">Dokumen biodata mahasiswa</p>

                                        </div>

                                    </div>

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $berkas->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">

                                        {{ $berkas->aktif ? 'Aktif' : 'Tidak aktif' }}

                                    </span>

                                </div>

                            @empty

                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center">

                                    <i class="bi bi-folder2 text-3xl text-slate-400"></i>

                                    <p class="mt-2 text-sm font-medium text-slate-700">Belum ada berkas yang diunggah.</p>

                                    <p class="mt-1 text-xs text-slate-500">Unggah dokumen melalui halaman Edit Biodata.</p>

                                </div>
                            @endforelse

                        </div>

                    </section>

                </div>

                {{-- DOMISILI --}}

                <div x-show="activeTab === 'domisili'" x-cloak id="domisili" role="tabpanel"
                    aria-labelledby="domisili-tab" tabindex="0">

                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">

                            <div class="flex items-center gap-3">

                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">

                                    <i class="bi bi-geo-alt text-xl"></i>

                                </span>

                                <div>

                                    <h2 class="text-lg font-bold text-slate-900">Alamat dan Domisili</h2>

                                    <p class="text-sm text-slate-500">Informasi alamat sesuai KTP dan tempat tinggal saat

                                        ini.</p>

                                </div>

                            </div>

                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2">

                            {{-- ALAMAT KTP --}}

                            <section class="min-w-0 p-4 sm:p-5 lg:border-r lg:border-slate-200">

                                <h3 class="mb-3 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                    Alamat sesuai KTP</h3>

                                <dl class="divide-y divide-slate-100">

                                    @foreach ([
            'Alamat KTP' => 'Alamat KTP',

            'RT' => 'RT KTP',

            'RW' => 'RW KTP',

            'Dusun' => 'Dusun KTP',

            'Desa/Kelurahan' => 'Desa/Kelurahan KTP',

            'Kecamatan' => 'Kecamatan KTP',

            'Kota/Kabupaten' => 'Kota/Kabupaten KTP',

            'Provinsi' => 'Provinsi KTP',

            'Kode Pos' => 'Kode Pos KTP',
        ] as $label => $key)
                                        <div
                                            class="grid grid-cols-1 gap-1 px-1 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3">

                                            <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                            <dd class="break-words text-slate-900">

                                                {{ $formatNilai($domisili[$key] ?? null) }}</dd>

                                        </div>
                                    @endforeach

                                </dl>

                            </section>

                            {{-- ALAMAT DOMISILI --}}

                            <section class="min-w-0 p-4 sm:p-5">

                                <h3 class="mb-3 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                    Alamat Domisili</h3>

                                <dl class="divide-y divide-slate-100">

                                    @foreach ([
            'Sama dengan KTP' => 'Domisili Sama dengan KTP',

            'Alamat Domisili' => 'Alamat Domisili',

            'RT' => 'RT Domisili',

            'RW' => 'RW Domisili',

            'Dusun' => 'Dusun Domisili',

            'Desa/Kelurahan' => 'Desa/Kelurahan Domisili',

            'Kecamatan' => 'Kecamatan Domisili',

            'Kota/Kabupaten' => 'Kota/Kabupaten Domisili',

            'Provinsi' => 'Provinsi Domisili',

            'Kode Pos' => 'Kode Pos Domisili',

            'Status Tinggal' => 'Status Tinggal',
        ] as $label => $key)
                                        <div
                                            class="grid grid-cols-1 gap-1 px-1 py-3 text-sm sm:grid-cols-[minmax(125px,40%)_minmax(0,1fr)] sm:gap-3">

                                            <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                            <dd class="break-words text-slate-900">

                                                {{ $formatNilai($domisili[$key] ?? null) }}</dd>

                                        </div>
                                    @endforeach

                                </dl>

                            </section>

                        </div>

                    </div>

                </div>

                {{-- ORANG TUA --}}

                <div x-show="activeTab === 'orang-tua'" x-cloak id="orang-tua" role="tabpanel"
                    aria-labelledby="orang-tua-tab" tabindex="0">

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

                        @foreach (['ayah' => 'Ayah', 'ibu' => 'Ibu'] as $jenis => $judul)
                            @php

                                $orang = $keluarga->get($jenis);

                            @endphp

                            <section class="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                                <header class="px-5 pb-3 pt-5 sm:px-6">

                                    <div class="mb-3 flex items-center gap-3">

                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">

                                            <i class="bi bi-person-heart text-xl"></i>

                                        </span>

                                        <div>

                                            <h2 class="text-lg font-bold text-slate-900">Data {{ $judul }}</h2>

                                            <p class="text-sm text-slate-500">Informasi orang tua mahasiswa.</p>

                                        </div>

                                    </div>

                                    <div class="border-b-2 border-emerald-600"></div>

                                </header>

                                <dl class="divide-y divide-slate-100 px-5 pb-4 sm:px-6">

                                    @foreach ([
            'Nama Lengkap' => $orang?->nama_lengkap,

            'NIK' => $orang?->nik,

            'Tanggal Lahir' => $orang?->tanggal_lahir?->format('d-m-Y'),

            'Status Hidup' => $orang?->status_hidup,

            'Pendidikan Terakhir' => $orang?->pendidikan_terakhir,

            'Pekerjaan' => $orang?->pekerjaan,

            'Penghasilan' => $orang?->penghasilan !== null ? 'Rp ' . number_format((float) $orang->penghasilan, 0, ',', '.') : null,

            'Nomor Telepon' => $orang?->nomor_telepon,

            'Email' => $orang?->email,
        ] as $label => $nilai)
                                        <div
                                            class="grid grid-cols-1 gap-1 py-3 text-sm sm:grid-cols-[42%_minmax(0,1fr)] sm:gap-3">

                                            <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                            <dd class="break-words text-slate-900">{{ $formatNilai($nilai) }}</dd>

                                        </div>
                                    @endforeach

                                </dl>

                            </section>
                        @endforeach

                    </div>

                </div>

                {{-- WALI --}}

                <div x-show="activeTab === 'wali'" x-cloak id="wali" role="tabpanel" aria-labelledby="wali-tab"
                    tabindex="0">

                    @php

                        $wali = $keluarga->get('wali');

                    @endphp

                    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        <header class="px-5 pb-3 pt-5 sm:px-6">

                            <div class="mb-3 flex items-center gap-3">

                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">

                                    <i class="bi bi-people text-xl"></i>

                                </span>

                                <div>

                                    <h2 class="text-lg font-bold text-slate-900">Informasi Wali</h2>

                                    <p class="text-sm text-slate-500">Data wali mahasiswa, jika ada.</p>

                                </div>

                            </div>

                            <div class="border-b-2 border-emerald-600"></div>

                        </header>

                        <dl class="grid grid-cols-1 gap-x-8 px-5 pb-5 sm:grid-cols-2 sm:px-6">

                            @foreach ([
            'Nama Lengkap' => $wali?->nama_lengkap,

            'NIK' => $wali?->nik,

            'Tanggal Lahir' => $wali?->tanggal_lahir?->format('d-m-Y'),

            'Status Hidup' => $wali?->status_hidup,

            'Status Kekerabatan' => $wali?->status_kekerabatan,

            'Pendidikan Terakhir' => $wali?->pendidikan_terakhir,

            'Pekerjaan' => $wali?->pekerjaan,

            'Penghasilan' => $wali?->penghasilan !== null ? 'Rp ' . number_format((float) $wali->penghasilan, 0, ',', '.') : null,

            'Nomor Telepon' => $wali?->nomor_telepon,

            'Email' => $wali?->email,

            'Alamat' => $wali?->alamat,
        ] as $label => $nilai)
                                <div
                                    class="grid grid-cols-1 gap-1 border-b border-slate-100 py-3 text-sm sm:grid-cols-[42%_minmax(0,1fr)] sm:gap-3">

                                    <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                    <dd class="break-words text-slate-900">{{ $formatNilai($nilai) }}</dd>

                                </div>
                            @endforeach

                        </dl>

                    </section>

                </div>

                {{-- PERGURUAN TINGGI ASAL --}}

                <div x-show="activeTab === 'pendidikan-asal'" x-cloak id="pendidikan-asal" role="tabpanel"
                    aria-labelledby="pendidikan-asal-tab" tabindex="0">

                    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        <header class="px-5 pb-3 pt-5 sm:px-6">

                            <div class="mb-3 flex items-center gap-3">

                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">

                                    <i class="bi bi-mortarboard text-xl"></i>

                                </span>

                                <div>

                                    <h2 class="text-lg font-bold text-slate-900">Perguruan Tinggi Asal</h2>

                                    <p class="text-sm text-slate-500">Informasi pendidikan sebelum masuk perguruan tinggi

                                        ini.</p>

                                </div>

                            </div>

                            <div class="border-b-2 border-emerald-600"></div>

                        </header>

                        <div class="p-5 sm:p-6">

                            <dl class="grid grid-cols-1 gap-x-8 sm:grid-cols-2">

                                @foreach ($pendidikan as $label => $nilai)
                                    <div
                                        class="grid grid-cols-1 gap-1 border-b border-slate-100 py-3 text-sm sm:grid-cols-[42%_minmax(0,1fr)] sm:gap-3">

                                        <dt class="font-bold text-emerald-800">{{ $label }}</dt>

                                        <dd class="break-words text-slate-900">{{ $formatNilai($nilai) }}</dd>

                                    </div>
                                @endforeach

                            </dl>

                            <div class="my-6 border-t border-slate-200"></div>

                            <div class="mb-4 flex items-center gap-3">

                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">

                                    <i class="bi bi-folder2-open text-xl"></i>

                                </span>

                                <div>

                                    <h3 class="text-base font-bold text-slate-900">Berkas Biodata</h3>

                                    <p class="text-sm text-slate-500">Daftar dokumen pendukung yang telah diunggah.</p>

                                </div>

                            </div>

                            @forelse (($berkasBiodata ?? collect()) as $berkas)
                                <div
                                    class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 p-4">

                                    <div class="flex items-center gap-3">

                                        <i class="bi bi-file-earmark-text text-xl text-slate-400"></i>

                                        <span
                                            class="font-semibold text-slate-800">{{ str($berkas->jenis_berkas)->replace('_', ' ')->title() }}</span>

                                    </div>

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $berkas->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">

                                        {{ $berkas->aktif ? 'Aktif' : 'Tidak aktif' }}

                                    </span>

                                </div>

                            @empty

                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center">

                                    <i class="bi bi-folder2 text-3xl text-slate-400"></i>

                                    <p class="mt-2 text-sm text-slate-500">Belum ada berkas biodata.</p>

                                </div>
                            @endforelse

                        </div>

                    </section>

                </div>

                <div class="py-3 text-center text-sm text-slate-500">

                    <i class="mr-1"></i>

                    Informasi ini merupakan data pribadi mahasiswa.

                </div>

            </div>

        </div>

    @endsection
