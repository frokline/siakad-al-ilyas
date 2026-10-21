@extends('layouts.admin')

@section('title', 'Detail Mahasiswa')

@section('content')

    @php
        $tabs = [
            'identitas' => 'Identitas',
            'biodata' => 'Biodata Tambahan',
            'alamat' => 'Alamat & Domisili',
            'pendidikan' => 'Pendidikan Asal',
            'keluarga' => 'Keluarga & Wali',
            'berkas' => 'Berkas',
            'riwayat' => 'Riwayat Studi',
        ];

        $tabDiperbolehkan = array_keys($tabs);
        $tabAktif = request('tab');
        $tabAktif = in_array($tabAktif, $tabDiperbolehkan, true) ? $tabAktif : 'identitas';

        $labelClass = 'text-xs font-bold text-slate-400 uppercase tracking-wider';
        $nilaiClass = 'mt-1 text-slate-700 break-words';

        $tombolEdit = static function (string $tab) use ($mahasiswa) {
            $url = route('admin.mahasiswa.edit', ['mahasiswa' => $mahasiswa, 'tab' => $tab]);

            return <<<HTML
            <a href="{$url}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            HTML;
        };

        $panelJudul = static function (string $judul, string $subjudul, ?string $tombol = null) {
            return <<<HTML
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">{$judul}</h2>
                    <p class="mt-1 text-xs text-slate-500">{$subjudul}</p>
                </div>
                {$tombol}
            </div>
            HTML;
        };
    @endphp

    @push('styles')
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush

    <div x-data="{ activeTab: '{{ $tabAktif }}' }" class="w-full space-y-6 py-2">

        <!-- PAGE HEADER -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
            <div class="bg-gradient-to-r from-emerald-900 via-emerald-700 to-emerald-500 px-6 py-5 text-white">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold">{{ $mahasiswa->user?->nama ?? 'Detail Mahasiswa' }}</h1>
                        <p class="mt-1 text-sm text-emerald-50">
                            NIM {{ $mahasiswa->nim }}
                            @if ($mahasiswa->user?->status)
                                · Akun {{ $mahasiswa->user->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 shadow-sm transition hover:bg-emerald-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Data
                        </a>

                        <a href="{{ route('admin.mahasiswa.index') }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/20 transition-colors shadow-sm">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- TAB NAV -->
            <nav class="border-b border-slate-200 bg-white px-4 py-2" aria-label="Bagian data mahasiswa">
                <ul class="flex flex-nowrap gap-1 overflow-x-auto pb-1" role="tablist" style="scrollbar-width:thin;">
                    @foreach ($tabs as $id => $judul)
                        <li class="shrink-0" role="presentation">
                            <button class="rounded-lg px-3.5 py-2 text-sm font-semibold transition"
                                :class="activeTab === '{{ $id }}' ? 'bg-emerald-700 text-white shadow-sm' :
                                    'text-slate-600 hover:bg-emerald-50 hover:text-emerald-800'"
                                id="{{ $id }}-tab" @click="activeTab = '{{ $id }}'" type="button"
                                role="tab" aria-controls="{{ $id }}"
                                :aria-selected="activeTab === '{{ $id }}' ? 'true' : 'false'">
                                {{ $judul }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">
                {{ session('success') }}
            </div>
        @endif

        {{-- TAB: IDENTITAS --}}
        <section x-show="activeTab === 'identitas'" x-cloak id="identitas" role="tabpanel" aria-labelledby="identitas-tab"
            class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            {!! $panelJudul('Identitas Mahasiswa', 'Data utama akun dan pendaftaran mahasiswa.', $tombolEdit('identitas')) !!}

            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                    <div>
                        <dt class="{{ $labelClass }}">NIM</dt>
                        <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $mahasiswa->nim }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $labelClass }}">Nama Lengkap</dt>
                        <dd class="mt-1 font-bold text-slate-800 text-base">{{ $mahasiswa->user?->nama ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $labelClass }}">Email</dt>
                        <dd class="{{ $nilaiClass }}">{{ $mahasiswa->user?->email ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $labelClass }}">Nomor Telepon</dt>
                        <dd class="{{ $nilaiClass }}">{{ $mahasiswa->user?->telepon ?? 'Belum diisi' }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $labelClass }}">Tempat Lahir</dt>
                        <dd class="{{ $nilaiClass }}">{{ $mahasiswa->tempat_lahir ?? 'Belum diisi' }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $labelClass }}">Tanggal Lahir</dt>
                        <dd class="{{ $nilaiClass }}">
                            {{ $mahasiswa->tanggal_lahir?->format('d-m-Y') ?? 'Belum diisi' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="{{ $labelClass }}">Jenis Kelamin</dt>
                        <dd class="mt-1 font-semibold text-slate-700">{{ $mahasiswa->labelJenisKelamin() }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $labelClass }}">Status Akun</dt>
                        <dd class="mt-1">
                            @php
                                $isAktif = $mahasiswa->user?->status === 'aktif';
                                $badgeClass = $isAktif
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                    : 'bg-rose-50 text-rose-700 border-rose-100';
                                $dotClass = $isAktif ? 'bg-emerald-500' : 'bg-rose-500';
                            @endphp
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                {{ $isAktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </dd>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4 border-t border-slate-100 pt-4">
                        <dt class="{{ $labelClass }}">Alamat</dt>
                        <dd class="mt-1 text-slate-700 leading-relaxed">{{ $mahasiswa->alamat ?? 'Belum diisi' }}</dd>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4 border-t border-slate-100 pt-4">
                        <dt class="{{ $labelClass }}">Kebutuhan Khusus</dt>
                        <dd class="mt-1 text-slate-700 leading-relaxed">
                            {{ filled($mahasiswa->kebutuhan_khusus) ? $mahasiswa->kebutuhan_khusus : '—' }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Informasi Pendaftaran</h3>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                        @foreach ([
            'konsentrasi' => 'Konsentrasi',
            'sistem_kuliah' => 'Sistem Kuliah',
            'jenis_pendaftaran' => 'Jenis Pendaftaran',
            'jalur_pendaftaran' => 'Jalur Pendaftaran',
            'gelombang' => 'Gelombang',
        ] as $kolom => $judul)
                            <div>
                                <dt class="{{ $labelClass }}">{{ $judul }}</dt>
                                <dd class="{{ $nilaiClass }}">
                                    {{ filled($mahasiswa->{$kolom}) ? $mahasiswa->{$kolom} : '—' }}
                                </dd>
                            </div>
                        @endforeach

                        <div>
                            <dt class="{{ $labelClass }}">Tanggal Awal Masuk</dt>
                            <dd class="{{ $nilaiClass }}">
                                {{ $mahasiswa->tanggal_awal_masuk?->format('d-m-Y') ?? '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="{{ $labelClass }}">Tanggal Daftar Ulang</dt>
                            <dd class="{{ $nilaiClass }}">
                                {{ $mahasiswa->tanggal_daftar_ulang?->format('d-m-Y') ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                @can('kelola-pengguna')
                    <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-end">
                        <a href="{{ route('admin.users.show', $mahasiswa->user_id) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                            Lihat Akun Pengguna
                        </a>
                    </div>
                @endcan
            </div>
        </section>

        {{-- TAB: BIODATA TAMBAHAN --}}
        <section x-show="activeTab === 'biodata'" x-cloak id="biodata" role="tabpanel" aria-labelledby="biodata-tab"
            class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            {!! $panelJudul('Biodata Tambahan', 'Data pribadi tambahan milik mahasiswa.', $tombolEdit('biodata')) !!}

            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                    @php
                        $kolomBiodata = [
                            'agama' => 'Agama',
                            'suku' => 'Suku',
                            'kewarganegaraan' => 'Kewarganegaraan',
                            'nik' => 'NIK',
                            'nomor_kk' => 'Nomor KK',
                            'npwp' => 'NPWP',
                            'nomor_kps' => 'Nomor KPS',
                            'golongan_darah' => 'Golongan Darah',
                            'status_nikah' => 'Status Pernikahan',
                            'ukuran_jas_almamater' => 'Ukuran Jas Almamater',
                            'nomor_hp' => 'Nomor HP',
                            'email_pribadi' => 'Email Pribadi',
                            'pekerjaan' => 'Pekerjaan',
                            'instansi_pekerjaan' => 'Instansi Pekerjaan',
                            'nomor_rekening' => 'Nomor Rekening',
                            'nama_rekening' => 'Nama Pemilik Rekening',
                            'nama_bank' => 'Nama Bank',
                            'berat_badan' => 'Berat Badan',
                            'tinggi_badan' => 'Tinggi Badan',
                            'penghasilan' => 'Penghasilan',
                            'transportasi' => 'Transportasi',
                        ];
                    @endphp

                    @foreach ($kolomBiodata as $kolom => $judul)
                        <div>
                            <dt class="{{ $labelClass }}">{{ $judul }}</dt>
                            <dd class="{{ $nilaiClass }}">
                                {{ filled($biodata?->{$kolom}) ? $biodata->{$kolom} : '—' }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- TAB: ALAMAT --}}
        <section x-show="activeTab === 'alamat'" x-cloak id="alamat" role="tabpanel" aria-labelledby="alamat-tab"
            class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            {!! $panelJudul(
                'Alamat KTP dan Domisili',
                'Alamat sesuai KTP dan tempat tinggal sekarang.',
                $tombolEdit('alamat'),
            ) !!}

            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                    @php
                        $kolomAlamat = [
                            'alamat_ktp' => 'Alamat KTP',
                            'rt_ktp' => 'RT KTP',
                            'rw_ktp' => 'RW KTP',
                            'dusun_ktp' => 'Dusun KTP',
                            'desa_kelurahan_ktp' => 'Desa/Kelurahan KTP',
                            'provinsi_ktp' => 'Provinsi KTP',
                            'kota_kabupaten_ktp' => 'Kota/Kabupaten KTP',
                            'kecamatan_ktp' => 'Kecamatan KTP',
                            'kode_pos_ktp' => 'Kode Pos KTP',
                            'alamat_domisili' => 'Alamat Domisili',
                            'rt_domisili' => 'RT Domisili',
                            'rw_domisili' => 'RW Domisili',
                            'dusun_domisili' => 'Dusun Domisili',
                            'desa_kelurahan_domisili' => 'Desa/Kelurahan Domisili',
                            'provinsi_domisili' => 'Provinsi Domisili',
                            'kota_kabupaten_domisili' => 'Kota/Kabupaten Domisili',
                            'kecamatan_domisili' => 'Kecamatan Domisili',
                            'kode_pos_domisili' => 'Kode Pos Domisili',
                            'status_tinggal' => 'Status Tinggal',
                        ];
                    @endphp

                    @foreach ($kolomAlamat as $kolom => $judul)
                        <div>
                            <dt class="{{ $labelClass }}">{{ $judul }}</dt>
                            <dd class="{{ $nilaiClass }}">
                                {{ filled($alamatBiodata?->{$kolom}) ? $alamatBiodata->{$kolom} : '—' }}
                            </dd>
                        </div>
                    @endforeach

                    <div>
                        <dt class="{{ $labelClass }}">Domisili Sama dengan KTP</dt>
                        <dd class="{{ $nilaiClass }}">
                            @if ($alamatBiodata?->domisili_sama_dengan_ktp === null)
                                —
                            @else
                                {{ $alamatBiodata->domisili_sama_dengan_ktp ? 'Ya' : 'Tidak' }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- TAB: PENDIDIKAN ASAL --}}
        <section x-show="activeTab === 'pendidikan'" x-cloak id="pendidikan" role="tabpanel"
            aria-labelledby="pendidikan-tab"
            class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            {!! $panelJudul(
                'Pendidikan Asal',
                'Riwayat pendidikan sebelum masuk perguruan tinggi ini.',
                $tombolEdit('pendidikan'),
            ) !!}

            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                    @php
                        $kolomPendidikan = [
                            'jenis_pendidikan_asal' => 'Jenis Pendidikan Asal',
                            'perguruan_tinggi' => 'Perguruan Tinggi',
                            'program_studi_asal' => 'Program Studi Asal',
                            'ipk' => 'IPK',
                            'sks_diakui' => 'SKS Diakui',
                            'nomor_ijazah' => 'Nomor Ijazah',
                            'nisn' => 'NISN',
                        ];
                    @endphp

                    @foreach ($kolomPendidikan as $kolom => $judul)
                        <div>
                            <dt class="{{ $labelClass }}">{{ $judul }}</dt>
                            <dd class="{{ $nilaiClass }}">
                                {{ filled($pendidikanAsal?->{$kolom}) ? $pendidikanAsal->{$kolom} : '—' }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- TAB: KELUARGA & WALI --}}
        <section x-show="activeTab === 'keluarga'" x-cloak id="keluarga" role="tabpanel" aria-labelledby="keluarga-tab"
            class="space-y-5">
            @foreach (['ayah' => 'Data Ayah', 'ibu' => 'Data Ibu', 'wali' => 'Data Wali'] as $jenis => $judul)
                @php $anggota = $dataKeluarga->firstWhere('jenis_hubungan', $jenis); @endphp

                <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
                    {!! $panelJudul(
                        $judul,
                        $jenis === 'wali' ? 'Data wali mahasiswa, jika ada.' : 'Informasi orang tua mahasiswa.',
                        $tombolEdit('keluarga'),
                    ) !!}

                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-sm">
                            @php
                                $kolomKeluarga = [
                                    'nama_lengkap' => 'Nama Lengkap',
                                    'nik' => 'NIK',
                                    'tanggal_lahir' => 'Tanggal Lahir',
                                    'status_hidup' => 'Status Hidup',
                                    'status_kekerabatan' => 'Status Kekerabatan',
                                    'pendidikan_terakhir' => 'Pendidikan Terakhir',
                                    'pekerjaan' => 'Pekerjaan',
                                    'penghasilan' => 'Penghasilan',
                                    'alamat' => 'Alamat',
                                    'nomor_telepon' => 'Nomor Telepon',
                                    'email' => 'Email',
                                ];
                            @endphp

                            @foreach ($kolomKeluarga as $kolom => $judulKolom)
                                <div>
                                    <dt class="{{ $labelClass }}">{{ $judulKolom }}</dt>
                                    <dd class="{{ $nilaiClass }}">
                                        @if ($kolom === 'tanggal_lahir' && $anggota?->{$kolom})
                                            {{ \Illuminate\Support\Carbon::parse($anggota->{$kolom})->format('d-m-Y') }}
                                        @else
                                            {{ filled($anggota?->{$kolom}) ? $anggota->{$kolom} : '—' }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- TAB: BERKAS --}}
        <section x-show="activeTab === 'berkas'" x-cloak id="berkas" role="tabpanel" aria-labelledby="berkas-tab"
            class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            {!! $panelJudul('Berkas Biodata', 'Dokumen pendukung yang telah diunggah.', $tombolEdit('berkas')) !!}

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Jenis Berkas</th>
                            <th class="px-6 py-4 font-semibold">Nama Berkas</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($berkasBiodata as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    {{ ucwords(str_replace('_', ' ', $item->jenis_berkas)) }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->berkas?->label ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item->aktif)
                                        <span
                                            class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            Tidak aktif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-500">
                                    Belum ada berkas biodata.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- TAB: RIWAYAT STUDI --}}
        <section x-show="activeTab === 'riwayat'" x-cloak id="riwayat" role="tabpanel" aria-labelledby="riwayat-tab"
            class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Riwayat Studi Mahasiswa</h2>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                    {{ $riwayatDaftar->total() }} riwayat
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Angkatan</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Program Studi</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Kurikulum</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Status Studi</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($riwayatDaftar as $riwayat)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-slate-800 text-xs">{{ $riwayat->angkatan }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $riwayat->kurikulum?->programStudi?->nama ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $riwayat->kurikulum?->nama ?? '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $rStatus = strtolower($riwayat->status);
                                        $rBadge =
                                            $rStatus === 'aktif'
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                                : 'bg-slate-100 text-slate-600 border-slate-200';
                                    @endphp
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $rBadge }}">
                                        {{ ucfirst($riwayat->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('kelola-riwayat-studi')
                                        <a href="{{ route('admin.riwayat-studi.show', $riwayat) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                                            Detail Riwayat
                                        </a>
                                    @else
                                        —
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Mahasiswa ini belum mempunyai riwayat studi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                @include('mahasiswa._pagination', ['paginator' => $riwayatDaftar])
            </div>
        </section>

    </div>
@endsection
