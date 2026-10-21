@extends('layouts.admin')

@section('title', 'Edit Mahasiswa')

@section('content')

    @php
        $keluargaByJenis = ($dataKeluarga ?? collect())->keyBy('jenis_hubungan');

        $biodataFields = [
            'agama' => ['Agama', 'text', 30],
            'suku' => ['Suku', 'text', 100],
            'berat_badan' => ['Berat badan (kg)', 'number', null],
            'tinggi_badan' => ['Tinggi badan (cm)', 'number', null],
            'golongan_darah' => ['Golongan darah', 'text', 3],
            'transportasi' => ['Transportasi', 'text', 50],
            'kewarganegaraan' => ['Kewarganegaraan', 'text', 60],
            'nik' => ['NIK', 'text', 32],
            'nomor_kk' => ['Nomor kartu keluarga', 'text', 32],
            'npwp' => ['NPWP', 'text', 32],
            'nomor_kps' => ['Nomor KPS', 'text', 32],
            'status_nikah' => ['Status pernikahan', 'text', 30],
            'ukuran_jas_almamater' => ['Ukuran jas almamater', 'text', 10],
            'nomor_hp' => ['Nomor HP', 'text', 30],
            'email_pribadi' => ['Email pribadi', 'email', 190],
            'pekerjaan' => ['Pekerjaan', 'text', 100],
            'instansi_pekerjaan' => ['Instansi pekerjaan', 'text', 190],
            'penghasilan' => ['Penghasilan (Rp)', 'number', null],
            'nomor_rekening' => ['Nomor rekening', 'text', 50],
            'nama_rekening' => ['Nama pemilik rekening', 'text', 150],
            'nama_bank' => ['Nama bank', 'text', 100],
        ];

        $alamatKtpFields = [
            'alamat_ktp' => ['Alamat sesuai KTP', 'textarea', null],
            'rt_ktp' => ['RT KTP', 'text', 5],
            'rw_ktp' => ['RW KTP', 'text', 5],
            'dusun_ktp' => ['Dusun KTP', 'text', 100],
            'desa_kelurahan_ktp' => ['Desa/Kelurahan KTP', 'text', 100],
            'kecamatan_ktp' => ['Kecamatan KTP', 'text', 100],
            'kota_kabupaten_ktp' => ['Kota/Kabupaten KTP', 'text', 100],
            'provinsi_ktp' => ['Provinsi KTP', 'text', 100],
            'kode_pos_ktp' => ['Kode pos KTP', 'text', 10],
        ];

        $alamatDomisiliFields = [
            'alamat_domisili' => ['Alamat domisili', 'textarea', null],
            'rt_domisili' => ['RT domisili', 'text', 5],
            'rw_domisili' => ['RW domisili', 'text', 5],
            'dusun_domisili' => ['Dusun domisili', 'text', 100],
            'desa_kelurahan_domisili' => ['Desa/Kelurahan domisili', 'text', 100],
            'kecamatan_domisili' => ['Kecamatan domisili', 'text', 100],
            'kota_kabupaten_domisili' => ['Kota/Kabupaten domisili', 'text', 100],
            'provinsi_domisili' => ['Provinsi domisili', 'text', 100],
            'kode_pos_domisili' => ['Kode pos domisili', 'text', 10],
        ];

        $familyFields = [
            'nama_lengkap' => ['Nama lengkap', 'text', 150],
            'nik' => ['NIK', 'text', 32],
            'tanggal_lahir' => ['Tanggal lahir', 'date', null],
            'status_hidup' => ['Status hidup', 'text', 30],
            'status_kekerabatan' => ['Status kekerabatan', 'text', 60],
            'pendidikan_terakhir' => ['Pendidikan terakhir', 'text', 100],
            'pekerjaan' => ['Pekerjaan', 'text', 100],
            'penghasilan' => ['Penghasilan (Rp)', 'number', null],
            'nomor_telepon' => ['Nomor telepon', 'text', 30],
            'email' => ['Email', 'email', 190],
            'alamat' => ['Alamat', 'textarea', null],
        ];

        $pendidikanFields = [
            'jenis_pendidikan_asal' => ['Jenis pendidikan asal', 'text', 100],
            'perguruan_tinggi' => ['Perguruan tinggi asal', 'text', 190],
            'program_studi_asal' => ['Program studi asal', 'text', 190],
            'ipk' => ['IPK', 'number', null],
            'sks_diakui' => ['SKS diakui', 'number', null],
            'nomor_ijazah' => ['Nomor ijazah', 'text', 100],
            'nisn' => ['NISN', 'text', 20],
        ];

        $jenisBerkasUpload = [
            'ijazah_terakhir' => 'Scan Ijazah Asli',
            'transkrip' => 'Scan Transkrip Nilai',
            'ktp' => 'Scan KTP',
            'kartu_keluarga' => 'Scan Kartu Keluarga (KK)',
            'akta_kelahiran' => 'Scan Akta Kelahiran',
            'pas_foto_3x4' => 'Pas Foto 3x4',
        ];

        $tabs = [
            'identitas' => 'Identitas & Pendaftaran',
            'biodata' => 'Biodata Tambahan',
            'alamat' => 'Alamat & Domisili',
            'pendidikan' => 'Pendidikan Asal',
            'keluarga' => 'Keluarga & Wali',
            'berkas' => 'Berkas',
        ];

        $tabErrorMap = [
            'identitas' => [
                'nim',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat',
                'kebutuhan_khusus',
                'konsentrasi',
                'sistem_kuliah',
                'jenis_pendaftaran',
                'jalur_pendaftaran',
                'gelombang',
                'tanggal_awal_masuk',
                'tanggal_daftar_ulang',
                'versi',
                'user_id',
            ],
            'biodata' => array_keys($biodataFields),
            'alamat' => [
                ...array_keys($alamatKtpFields),
                ...array_keys($alamatDomisiliFields),
                'domisili_sama_dengan_ktp',
                'status_tinggal',
            ],
            'pendidikan' => array_keys($pendidikanFields),
            'keluarga' => ['keluarga'],
            'berkas' => array_map(static fn($jenis) => 'berkas_' . $jenis, array_keys($jenisBerkasUpload)),
        ];

        $tabAktif = in_array($tab ?? '', array_keys($tabs), true) ? $tab : 'identitas';

        if ($errors->any()) {
            foreach ($errors->keys() as $errorKey) {
                foreach ($tabErrorMap as $id => $daftarKolom) {
                    foreach ($daftarKolom as $kolom) {
                        if ($errorKey === $kolom || str_starts_with($errorKey, $kolom . '.')) {
                            $tabAktif = $id;
                            break 3;
                        }
                    }
                }
            }
        }

        $fieldClass =
            'mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-emerald-100';
        $labelClass = 'block text-sm font-medium text-slate-700';
    @endphp

    @push('styles')
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush

    <div x-data="{ activeTab: '{{ $tabAktif }}' }" class="w-full space-y-5 py-2">
        <!-- PAGE HEADER -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Edit Mahasiswa</h1>
                <p class="text-sm text-slate-500 mt-1">{{ $mahasiswa->nim }} — {{ $akun->nama }}</p>
            </div>

            <a href="{{ route('admin.mahasiswa.show', $mahasiswa) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors self-start shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900" role="alert">
                <p class="font-semibold">Periksa kembali data berikut.</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORM CARD -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Formulir Perubahan Data Mahasiswa</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Semua perubahan (identitas, biodata, alamat, pendidikan, keluarga, dan berkas) disimpan
                    bersamaan saat menekan tombol Simpan.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.mahasiswa.update', $mahasiswa) }}"
                enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PATCH')

                <!-- TAB NAV -->
                <nav class="rounded-xl border border-slate-200 bg-slate-50 p-1.5"
                    aria-label="Bagian formulir mahasiswa">
                    <ul class="flex flex-nowrap gap-1 overflow-x-auto pb-1" role="tablist">
                        @foreach ($tabs as $id => $judul)
                            <li class="shrink-0" role="presentation">
                                <button type="button" role="tab" id="{{ $id }}-tab"
                                    aria-controls="{{ $id }}"
                                    :aria-selected="activeTab === '{{ $id }}' ? 'true' : 'false'"
                                    @click="activeTab = '{{ $id }}'"
                                    :class="activeTab === '{{ $id }}' ? 'bg-siakad-dark text-white shadow-sm' :
                                        'text-slate-600 hover:bg-white hover:text-slate-900'"
                                    class="rounded-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-siakad-dark">
                                    {{ $judul }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                {{-- TAB: IDENTITAS --}}
                <section x-show="activeTab === 'identitas'" x-cloak id="identitas" role="tabpanel"
                    aria-labelledby="identitas-tab" class="space-y-6">
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                        <strong class="font-bold text-slate-800 text-base block">{{ $akun->nama }}</strong>
                        <span class="text-xs text-slate-500 block mt-0.5">{{ $akun->email }}</span>
                        <p class="text-xs text-slate-400 mt-2">
                            Nama, email, dan status akun dikelola melalui menu Pengguna.
                        </p>
                    </div>

                    @include('mahasiswa._identitas_fields')
                </section>

                {{-- TAB: BIODATA TAMBAHAN --}}
                <section x-show="activeTab === 'biodata'" x-cloak id="biodata" role="tabpanel"
                    aria-labelledby="biodata-tab" class="rounded-xl border border-slate-200">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-slate-900">Biodata tambahan mahasiswa</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                        @foreach ($biodataFields as $field => [$label, $type, $maxlength])
                            <div
                                class="{{ in_array($field, ['email_pribadi', 'instansi_pekerjaan', 'nama_rekening'], true) ? 'sm:col-span-2' : '' }}">
                                <label class="{{ $labelClass }}" for="{{ $field }}">{{ $label }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                    value="{{ old($field, data_get($biodata, $field, '')) }}"
                                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                    @if ($type === 'number') min="0" step="1" @endif
                                    class="{{ $fieldClass }} @error($field) border-rose-500 @enderror">
                                @error($field)
                                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- TAB: ALAMAT --}}
                <section x-show="activeTab === 'alamat'" x-cloak id="alamat" role="tabpanel"
                    aria-labelledby="alamat-tab" class="space-y-5">
                    <div class="rounded-xl border border-slate-200">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-semibold text-slate-900">Alamat sesuai KTP</h2>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                            @foreach ($alamatKtpFields as $field => [$label, $type, $maxlength])
                                <div class="{{ $type === 'textarea' ? 'sm:col-span-2 lg:col-span-3' : '' }}">
                                    <label class="{{ $labelClass }}" for="{{ $field }}">{{ $label }}</label>
                                    @if ($type === 'textarea')
                                        <textarea id="{{ $field }}" name="{{ $field }}" rows="3"
                                            class="{{ $fieldClass }} @error($field) border-rose-500 @enderror">{{ old($field, data_get($alamatBiodata, $field, '')) }}</textarea>
                                    @else
                                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                            value="{{ old($field, data_get($alamatBiodata, $field, '')) }}"
                                            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                            class="{{ $fieldClass }} @error($field) border-rose-500 @enderror">
                                    @endif
                                    @error($field)
                                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-semibold text-slate-900">Tempat tinggal saat ini</h2>
                        </div>
                        <div class="p-5 sm:p-6">
                            <label class="flex items-start gap-3 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-900">
                                <input type="hidden" name="domisili_sama_dengan_ktp" value="0">
                                <input type="checkbox" name="domisili_sama_dengan_ktp" value="1"
                                    @checked(old('domisili_sama_dengan_ktp', data_get($alamatBiodata, 'domisili_sama_dengan_ktp', false)))
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                                <span>
                                    <span class="block font-semibold">Alamat domisili sama dengan alamat KTP</span>
                                    <span class="mt-1 block text-emerald-800/80">Centang bila mahasiswa tinggal di
                                        alamat yang sama.</span>
                                </span>
                            </label>

                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($alamatDomisiliFields as $field => [$label, $type, $maxlength])
                                    <div class="{{ $type === 'textarea' ? 'sm:col-span-2 lg:col-span-3' : '' }}">
                                        <label class="{{ $labelClass }}" for="{{ $field }}">{{ $label }}</label>
                                        @if ($type === 'textarea')
                                            <textarea id="{{ $field }}" name="{{ $field }}" rows="3"
                                                class="{{ $fieldClass }} @error($field) border-rose-500 @enderror">{{ old($field, data_get($alamatBiodata, $field, '')) }}</textarea>
                                        @else
                                            <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                                value="{{ old($field, data_get($alamatBiodata, $field, '')) }}"
                                                @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                                class="{{ $fieldClass }} @error($field) border-rose-500 @enderror">
                                        @endif
                                        @error($field)
                                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                                <div>
                                    <label class="{{ $labelClass }}" for="status_tinggal">Status tinggal</label>
                                    <input id="status_tinggal" name="status_tinggal" type="text" maxlength="50"
                                        value="{{ old('status_tinggal', data_get($alamatBiodata, 'status_tinggal', '')) }}"
                                        class="{{ $fieldClass }} @error('status_tinggal') border-rose-500 @enderror">
                                    @error('status_tinggal')
                                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- TAB: PENDIDIKAN ASAL --}}
                <section x-show="activeTab === 'pendidikan'" x-cloak id="pendidikan" role="tabpanel"
                    aria-labelledby="pendidikan-tab" class="rounded-xl border border-slate-200">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-slate-900">Pendidikan sebelum masuk kampus ini</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                        @foreach ($pendidikanFields as $field => [$label, $type, $maxlength])
                            <div
                                class="{{ in_array($field, ['perguruan_tinggi', 'program_studi_asal'], true) ? 'sm:col-span-2' : '' }}">
                                <label class="{{ $labelClass }}" for="{{ $field }}">{{ $label }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                    value="{{ old($field, data_get($pendidikanAsal, $field, '')) }}"
                                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                    @if ($field === 'ipk') min="0" max="4" step="0.01" @endif
                                    @if ($field === 'sks_diakui') min="0" max="65535" step="1" @endif
                                    class="{{ $fieldClass }} @error($field) border-rose-500 @enderror">
                                @error($field)
                                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- TAB: KELUARGA & WALI --}}
                <section x-show="activeTab === 'keluarga'" x-cloak id="keluarga" role="tabpanel"
                    aria-labelledby="keluarga-tab" class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                        @foreach (['ayah' => 'Data Ayah', 'ibu' => 'Data Ibu'] as $jenis => $judul)
                            @php $orang = $keluargaByJenis->get($jenis); @endphp
                            <div class="rounded-xl border border-slate-200">
                                <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                                    <h2 class="font-semibold text-slate-900">{{ $judul }}</h2>
                                </div>
                                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                                    @foreach ($familyFields as $field => [$label, $type, $maxlength])
                                        @php
                                            $errorKey = "keluarga.$jenis.$field";
                                            $familyValue = old($errorKey, data_get($orang, $field, ''));
                                            if ($field === 'tanggal_lahir' && $familyValue instanceof \DateTimeInterface) {
                                                $familyValue = $familyValue->format('Y-m-d');
                                            }
                                        @endphp
                                        <div
                                            class="{{ in_array($field, ['alamat', 'email'], true) ? 'sm:col-span-2' : '' }}">
                                            <label class="{{ $labelClass }}"
                                                for="keluarga_{{ $jenis }}_{{ $field }}">{{ $label }}</label>
                                            @if ($type === 'textarea')
                                                <textarea id="keluarga_{{ $jenis }}_{{ $field }}"
                                                    name="keluarga[{{ $jenis }}][{{ $field }}]" rows="2"
                                                    class="{{ $fieldClass }} @error($errorKey) border-rose-500 @enderror">{{ $familyValue }}</textarea>
                                            @else
                                                <input id="keluarga_{{ $jenis }}_{{ $field }}"
                                                    name="keluarga[{{ $jenis }}][{{ $field }}]"
                                                    type="{{ $type }}" value="{{ $familyValue }}"
                                                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                                    @if ($type === 'number') min="0" step="1" @endif
                                                    class="{{ $fieldClass }} @error($errorKey) border-rose-500 @enderror">
                                            @endif
                                            @error($errorKey)
                                                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @php $wali = $keluargaByJenis->get('wali'); @endphp
                    <div class="rounded-xl border border-slate-200">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-semibold text-slate-900">Data Wali</h2>
                            <p class="mt-1 text-sm text-slate-500">Isi jika mahasiswa memiliki data wali.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                            @foreach ($familyFields as $field => [$label, $type, $maxlength])
                                @php
                                    $errorKey = "keluarga.wali.$field";
                                    $familyValue = old($errorKey, data_get($wali, $field, ''));
                                    if ($field === 'tanggal_lahir' && $familyValue instanceof \DateTimeInterface) {
                                        $familyValue = $familyValue->format('Y-m-d');
                                    }
                                @endphp
                                <div
                                    class="{{ in_array($field, ['alamat', 'email'], true) ? 'sm:col-span-2 lg:col-span-3' : '' }}">
                                    <label class="{{ $labelClass }}"
                                        for="keluarga_wali_{{ $field }}">{{ $label }}</label>
                                    @if ($type === 'textarea')
                                        <textarea id="keluarga_wali_{{ $field }}" name="keluarga[wali][{{ $field }}]"
                                            rows="3"
                                            class="{{ $fieldClass }} @error($errorKey) border-rose-500 @enderror">{{ $familyValue }}</textarea>
                                    @else
                                        <input id="keluarga_wali_{{ $field }}"
                                            name="keluarga[wali][{{ $field }}]" type="{{ $type }}"
                                            value="{{ $familyValue }}"
                                            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                            @if ($type === 'number') min="0" step="1" @endif
                                            class="{{ $fieldClass }} @error($errorKey) border-rose-500 @enderror">
                                    @endif
                                    @error($errorKey)
                                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- TAB: BERKAS --}}
                @php
                    $berkasTersimpan = ($berkasBiodata ?? collect())->keyBy('jenis_berkas');
                @endphp
                <section x-show="activeTab === 'berkas'" x-cloak id="berkas" role="tabpanel"
                    aria-labelledby="berkas-tab" class="rounded-xl border border-slate-200">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-slate-900">Berkas Biodata</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Unggah dokumen pendukung. Berkas baru disimpan bersamaan saat menekan tombol
                            <span class="font-semibold text-slate-700">Simpan Perubahan</span>.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                        @foreach ($jenisBerkasUpload as $jenis => $judul)
                            @php $berkasAda = $berkasTersimpan->get($jenis); @endphp
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="mb-3 flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-semibold text-slate-800">{{ $judul }}</h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $jenis === 'pas_foto_3x4' ? 'Format: JPG, JPEG, atau PNG.' : 'Format: PDF, JPG, JPEG, atau PNG.' }}
                                        </p>
                                        @if ($berkasAda)
                                            <p class="mt-1 text-xs text-slate-400">
                                                Saat ini: {{ $berkasAda->berkas?->label ?? 'tersimpan' }}
                                            </p>
                                        @endif
                                    </div>
                                    @if ($berkasAda)
                                        <span
                                            class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                            Tersimpan
                                        </span>
                                    @endif
                                </div>

                                @error('berkas_' . $jenis)
                                    <p class="mb-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                @enderror

                                <input id="berkas_{{ $jenis }}" name="berkas_{{ $jenis }}" type="file"
                                    accept="{{ $jenis === 'pas_foto_3x4' ? '.jpg,.jpeg,.png' : '.pdf,.jpg,.jpeg,.png' }}"
                                    class="block w-full rounded-lg border border-slate-300 p-2 text-sm">
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- FOOTER AKSI -->
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.mahasiswa.show', $mahasiswa) }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" @disabled($errors->has('versi'))
                        class="inline-flex items-center justify-center rounded-lg bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 disabled:opacity-50">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
