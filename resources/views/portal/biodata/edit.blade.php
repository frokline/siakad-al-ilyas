@extends('layouts.admin')

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

        // Pilihan dropdown di sisi frontend. Nilainya tetap berupa teks biasa.

        $selectOptions = [
            'agama' => [
                'Islam',

                'Kristen',

                'Katolik',

                'Hindu',

                'Buddha',

                'Konghucu',

                'Kepercayaan terhadap Tuhan YME',

                'Lainnya',
            ],

            'golongan_darah' => ['A', 'B', 'AB', 'O', 'Tidak tahu'],

            'transportasi' => ['Jalan kaki', 'Sepeda', 'Motor', 'Mobil', 'Angkutan umum', 'Perahu/Klotok', 'Lainnya'],

            'kewarganegaraan' => ['Indonesia', 'WNI', 'WNA'],

            'status_nikah' => ['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati'],

            'ukuran_jas_almamater' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],

            'pekerjaan' => [
                'Tidak Bekerja',

                'Pelajar/Mahasiswa',

                'PNS/ASN',

                'TNI/Polri',

                'Karyawan Swasta',

                'Wirausaha',

                'Guru/Dosen',

                'Petani/Pekebun',

                'Nelayan',

                'Buruh',

                'Ibu Rumah Tangga',

                'Lainnya',
            ],

            'status_hidup' => ['Hidup', 'Meninggal'],

            'status_kekerabatan' => [
                'Ayah',

                'Ibu',

                'Kakek/Nenek',

                'Paman/Bibi',

                'Kakak/Adik',

                'Saudara',

                'Kerabat lainnya',

                'Bukan kerabat',

                'Lainnya',
            ],

            'pendidikan_terakhir' => [
                'Belum/Tidak Sekolah',

                'SD/Sederajat',

                'SMP/Sederajat',

                'SMA/SMK/MA',

                'D1',

                'D2',

                'D3',

                'D4/S1',

                'S2',

                'S3',
            ],

            'jenis_pendidikan_asal' => ['SMA/MA', 'SMK', 'Paket C', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3', 'Lainnya'],

            'status_tinggal' => [
                'Bersama orang tua',

                'Rumah sendiri',

                'Kost',

                'Kontrak',

                'Asrama',

                'Rumah keluarga/kerabat',

                'Lainnya',
            ],
        ];

        // Daftar suku tetap dapat menerima isian khusus agar pilihan tidak membatasi mahasiswa.

        $sukuOptions = [
            'Banjar',

            'Jawa',

            'Sunda',

            'Bugis',

            'Dayak',

            'Madura',

            'Batak',

            'Betawi',

            'Minangkabau',

            'Melayu',

            'Bali',

            'Aceh',

            'Sasak',

            'Makassar',

            'Toraja',

            'Palembang',

            'Lampung',

            'Ambon',

            'Bima',

            'Gorontalo',

            'Minahasa',

            'Papua',

            'Tionghoa',

            'Kutai',

            'Mbojo',
        ];

        // Pengelompokan tampilan mengikuti contoh: dua kolom dengan judul bagian.

        $biodataGroupsLeft = [
            'Umum' => ['agama', 'suku', 'berat_badan', 'tinggi_badan', 'golongan_darah', 'transportasi'],

            'Kontak' => ['nomor_hp', 'email_pribadi'],
        ];

        $biodataGroupsRight = [
            'Administrasi' => [
                'kewarganegaraan',

                'nik',

                'nomor_kk',

                'npwp',

                'nomor_kps',

                'status_nikah',

                'ukuran_jas_almamater',
            ],

            'Pekerjaan' => ['pekerjaan', 'instansi_pekerjaan', 'penghasilan'],

            'Bank' => ['nomor_rekening', 'nama_rekening', 'nama_bank'],
        ];

        $tabs = [
            'umum' => 'Informasi umum',

            'domisili' => 'Alamat & domisili',

            'orang-tua' => 'Orang tua',

            'wali' => 'Wali',

            'pendidikan-asal' => 'Pendidikan asal',
            'berkas' => 'Berkas',
        ];

        $fieldClass =
            'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100';

        $selectClass =
            'block w-full appearance-none rounded-lg border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm text-slate-800 shadow-sm outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100';

        $labelClass = 'block text-sm font-semibold text-emerald-900';

    @endphp

    <div x-data="{ activeTab: 'umum' }" class="mx-auto w-full max-w-7xl space-y-6 py-3">

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="h-1.5 bg-emerald-800"></div>

            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">

                <div>

                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Portal mahasiswa</p>

                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Edit Biodata Mahasiswa</h1>

                    <p class="mt-1 text-sm text-slate-500">Perbarui data pribadi, alamat, keluarga, dan pendidikan asal.</p>

                </div>

                <a href="{{ route('portal.biodata.show') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">

                    Batal / Kembali

                </a>

            </div>

            <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-lg font-bold text-white">

                        {{ mb_strtoupper(mb_substr($mahasiswa->user->nama ?? 'M', 0, 1)) }}

                    </div>

                    <div class="min-w-0">

                        <p class="truncate font-semibold text-slate-900">{{ $mahasiswa->user->nama ?? '-' }}</p>

                        <p class="mt-0.5 text-sm text-slate-500">NIM: {{ $mahasiswa->nim ?: '-' }}</p>

                    </div>

                    <span
                        class="ml-auto hidden rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 sm:inline-flex">

                        Data identitas akademik tidak diedit di sini

                    </span>

                </div>

            </div>

        </section>

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

        @if (session('info'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">

                {{ session('info') }}

            </div>
        @endif

        <form action="{{ route('portal.biodata.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">

            @csrf

            @method('PATCH')

            <nav class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm" aria-label="Bagian formulir biodata">

                <ul class="flex flex-nowrap gap-2 overflow-x-auto pb-1" role="tablist">

                    @foreach ($tabs as $id => $judul)
                        <li class="shrink-0" role="presentation">

                            <button type="button" role="tab" id="{{ $id }}-tab"
                                aria-controls="{{ $id }}"
                                :aria-selected="activeTab === '{{ $id }}' ? 'true' : 'false'"
                                @click="activeTab = '{{ $id }}'"
                                :class="activeTab === '{{ $id }}' ? 'bg-emerald-800 text-white shadow-sm' :
                                
                                    'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-800'"
                                class="rounded-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">

                                {{ $judul }}

                            </button>

                        </li>
                    @endforeach

                </ul>

            </nav>

            {{-- TAB 1: INFORMASI UMUM --}}

            <section x-show="activeTab === 'umum'" x-cloak id="umum" role="tabpanel" aria-labelledby="umum-tab"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <header class="border-b border-slate-200 px-5 py-4 sm:px-6">

                    <div class="flex items-center gap-3">

                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">

                            <i class="bi bi-person-vcard text-xl"></i>

                        </span>

                        <div>

                            <h2 class="text-lg font-bold text-slate-900">Informasi Umum</h2>

                            <p class="mt-1 text-sm text-slate-500">Lengkapi identitas, administrasi, kontak, pekerjaan, dan

                                rekening Anda.</p>

                        </div>

                    </div>

                </header>

                <div class="grid grid-cols-1 gap-6 p-4 sm:p-6 lg:grid-cols-2 lg:gap-8">

                    {{-- KOLOM KIRI --}}

                    <div class="min-w-0 space-y-6">

                        @foreach ($biodataGroupsLeft as $groupTitle => $fields)
                            <section>

                                <h3 class="mb-2 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                    {{ $groupTitle }}</h3>

                                <div class="divide-y divide-slate-100">

                                    @foreach ($fields as $field)
                                        @php

                                            [$label, $type, $maxlength] = $biodataFields[$field];

                                            $nilaiField = old($field, data_get($biodata, $field, ''));

                                        @endphp

                                        <div
                                            class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-[42%_minmax(0,1fr)] sm:items-center sm:gap-3">

                                            <label class="{{ $labelClass }}"
                                                for="{{ $field }}">{{ $label }}</label>

                                            <div class="min-w-0">

                                                @if ($field === 'suku')
                                                    @php

                                                        $sukuValue = (string) $nilaiField;

                                                        $sukuCustom =
                                                            $sukuValue !== '' &&
                                                            !in_array($sukuValue, $sukuOptions, true);

                                                    @endphp

                                                    <div x-data="{ pilihanSuku: @js($sukuCustom ? '__custom__' : $sukuValue) }" class="space-y-2">

                                                        <div class="relative">

                                                            <select id="suku_pilihan" x-model="pilihanSuku"
                                                                :name="pilihanSuku === '__custom__' ? null : 'suku'"
                                                                class="{{ $selectClass }}">

                                                                <option value="">Pilih suku</option>

                                                                @foreach ($sukuOptions as $option)
                                                                    @if ($option !== 'Lainnya')
                                                                        <option value="{{ $option }}">

                                                                            {{ $option }}</option>
                                                                    @endif
                                                                @endforeach

                                                                <option value="__custom__">Suku lainnya (isi sendiri)

                                                                </option>

                                                            </select>

                                                            <i
                                                                class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500"></i>

                                                        </div>

                                                        <input x-cloak x-show="pilihanSuku === '__custom__'"
                                                            :name="pilihanSuku === '__custom__' ? 'suku' : null"
                                                            :disabled="pilihanSuku !== '__custom__'" type="text"
                                                            maxlength="100" value="{{ $sukuCustom ? $sukuValue : '' }}"
                                                            placeholder="Tuliskan nama suku" class="{{ $fieldClass }}">

                                                    </div>
                                                @elseif (isset($selectOptions[$field]))
                                                    <div class="relative">

                                                        <select id="{{ $field }}" name="{{ $field }}"
                                                            class="{{ $selectClass }}">

                                                            <option value="">Pilih {{ strtolower($label) }}</option>

                                                            @if (filled($nilaiField) && !in_array((string) $nilaiField, $selectOptions[$field], true))
                                                                <option value="{{ $nilaiField }}" selected>

                                                                    {{ $nilaiField }}</option>
                                                            @endif

                                                            @foreach ($selectOptions[$field] as $option)
                                                                <option value="{{ $option }}"
                                                                    @selected((string) $nilaiField === $option)>{{ $option }}

                                                                </option>
                                                            @endforeach

                                                        </select>

                                                        <i
                                                            class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500"></i>

                                                    </div>
                                                @else
                                                    <input id="{{ $field }}" name="{{ $field }}"
                                                        type="{{ $type }}" value="{{ $nilaiField }}"
                                                        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                                        @if ($type === 'number') min="0" @endif
                                                        @if ($field === 'berat_badan') max="255" step="1" @endif
                                                        @if ($field === 'tinggi_badan') max="65535" step="1" @endif
                                                        @if ($field === 'penghasilan') step="1" @endif
                                                        class="{{ $fieldClass }}">
                                                @endif

                                            </div>

                                        </div>
                                    @endforeach

                                </div>

                            </section>
                        @endforeach

                    </div>

                    {{-- KOLOM KANAN --}}

                    <div class="min-w-0 space-y-6">

                        @foreach ($biodataGroupsRight as $groupTitle => $fields)
                            <section>

                                <h3 class="mb-2 border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                    {{ $groupTitle }}</h3>

                                <div class="divide-y divide-slate-100">

                                    @foreach ($fields as $field)
                                        @php

                                            [$label, $type, $maxlength] = $biodataFields[$field];

                                            $nilaiField = old($field, data_get($biodata, $field, ''));

                                        @endphp

                                        <div
                                            class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-[42%_minmax(0,1fr)] sm:items-center sm:gap-3">

                                            <label class="{{ $labelClass }}"
                                                for="{{ $field }}">{{ $label }}</label>

                                            <div class="min-w-0">

                                                @if (isset($selectOptions[$field]))
                                                    <div class="relative">

                                                        <select id="{{ $field }}" name="{{ $field }}"
                                                            class="{{ $selectClass }}">

                                                            <option value="">Pilih {{ strtolower($label) }}</option>

                                                            @if (filled($nilaiField) && !in_array((string) $nilaiField, $selectOptions[$field], true))
                                                                <option value="{{ $nilaiField }}" selected>

                                                                    {{ $nilaiField }}</option>
                                                            @endif

                                                            @foreach ($selectOptions[$field] as $option)
                                                                <option value="{{ $option }}"
                                                                    @selected((string) $nilaiField === $option)>{{ $option }}

                                                                </option>
                                                            @endforeach

                                                        </select>

                                                        <i
                                                            class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500"></i>

                                                    </div>
                                                @else
                                                    <input id="{{ $field }}" name="{{ $field }}"
                                                        type="{{ $type }}" value="{{ $nilaiField }}"
                                                        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                                        @if ($type === 'number') min="0" @endif
                                                        @if ($field === 'penghasilan') step="1" @endif
                                                        class="{{ $fieldClass }}">
                                                @endif

                                            </div>

                                        </div>
                                    @endforeach

                                </div>

                            </section>
                        @endforeach

                    </div>

                </div>

            </section>

            {{-- TAB 2: ALAMAT --}}

            <section x-show="activeTab === 'domisili'" x-cloak id="domisili" role="tabpanel"
                aria-labelledby="domisili-tab" class="space-y-5">

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">

                        <h2 class="border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">Alamat sesuai KTP

                        </h2>

                    </div>

                    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">

                        @foreach ($alamatKtpFields as $field => [$label, $type, $maxlength])
                            <div class="{{ $type === 'textarea' ? 'sm:col-span-2 lg:col-span-3' : '' }}">

                                <label class="{{ $labelClass }}"
                                    for="{{ $field }}">{{ $label }}</label>

                                @if ($type === 'textarea')
                                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" class="{{ $fieldClass }}">{{ old($field, data_get($alamatBiodata, $field, '')) }}</textarea>
                                @else
                                    <input id="{{ $field }}" name="{{ $field }}"
                                        type="{{ $type }}"
                                        value="{{ old($field, data_get($alamatBiodata, $field, '')) }}"
                                        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                        class="{{ $fieldClass }}">
                                @endif

                            </div>
                        @endforeach

                    </div>

                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">

                        <h2 class="border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">Tempat tinggal

                            saat ini</h2>

                    </div>

                    <div class="p-5 sm:p-6">

                        <label class="flex items-start gap-3 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-900">

                            <input type="hidden" name="domisili_sama_dengan_ktp" value="0">

                            <input type="checkbox" name="domisili_sama_dengan_ktp" value="1"
                                @checked(old('domisili_sama_dengan_ktp', data_get($alamatBiodata, 'domisili_sama_dengan_ktp', false)))
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">

                            <span>

                                <span class="block font-semibold">Alamat domisili sama dengan alamat KTP</span>

                                <span class="mt-1 block text-emerald-800/80">Centang bila kamu tinggal di alamat yang

                                    sama.</span>

                            </span>

                        </label>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            @foreach ($alamatDomisiliFields as $field => [$label, $type, $maxlength])
                                <div class="{{ $type === 'textarea' ? 'sm:col-span-2 lg:col-span-3' : '' }}">

                                    <label class="{{ $labelClass }}"
                                        for="{{ $field }}">{{ $label }}</label>

                                    @if ($type === 'textarea')
                                        <textarea id="{{ $field }}" name="{{ $field }}" rows="3" class="{{ $fieldClass }}">{{ old($field, data_get($alamatBiodata, $field, '')) }}</textarea>
                                    @else
                                        <input id="{{ $field }}" name="{{ $field }}"
                                            type="{{ $type }}"
                                            value="{{ old($field, data_get($alamatBiodata, $field, '')) }}"
                                            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                            class="{{ $fieldClass }}">
                                    @endif

                                </div>
                            @endforeach

                            <div>

                                <label class="{{ $labelClass }}" for="status_tinggal">Status tinggal</label>

                                @php $statusTinggalValue = old('status_tinggal', data_get($alamatBiodata, 'status_tinggal', '')); @endphp

                                <div class="relative">

                                    <select id="status_tinggal" name="status_tinggal" class="{{ $selectClass }}">

                                        <option value="">Pilih status tinggal</option>

                                        @if (filled($statusTinggalValue) && !in_array((string) $statusTinggalValue, $selectOptions['status_tinggal'], true))
                                            <option value="{{ $statusTinggalValue }}" selected>{{ $statusTinggalValue }}

                                            </option>
                                        @endif

                                        @foreach ($selectOptions['status_tinggal'] as $option)
                                            <option value="{{ $option }}" @selected((string) $statusTinggalValue === $option)>

                                                {{ $option }}</option>
                                        @endforeach

                                    </select>

                                    <i
                                        class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

            {{-- TAB 3: ORANG TUA --}}

            <section x-show="activeTab === 'orang-tua'" x-cloak id="orang-tua" role="tabpanel"
                aria-labelledby="orang-tua-tab" class="grid grid-cols-1 gap-5 xl:grid-cols-2">

                @foreach (['ayah' => 'Data Ayah', 'ibu' => 'Data Ibu'] as $jenis => $judul)
                    @php $orang = $keluargaByJenis->get($jenis); @endphp

                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                        <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">

                            <h2 class="border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">

                                {{ $judul }}</h2>

                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">

                            @foreach ($familyFields as $field => [$label, $type, $maxlength])
                                <div class="{{ in_array($field, ['alamat', 'email'], true) ? 'sm:col-span-2' : '' }}">

                                    <label class="{{ $labelClass }}"
                                        for="keluarga_{{ $jenis }}_{{ $field }}">{{ $label }}</label>

                                    @php

                                        $familyValue = old("keluarga.$jenis.$field", data_get($orang, $field, ''));

                                        if ($field === 'tanggal_lahir' && $familyValue instanceof \DateTimeInterface) {
                                            $familyValue = $familyValue->format('Y-m-d');
                                        }

                                    @endphp

                                    @if ($type === 'textarea')
                                        <textarea id="keluarga_{{ $jenis }}_{{ $field }}"
                                            name="keluarga[{{ $jenis }}][{{ $field }}]" rows="3" class="{{ $fieldClass }}">{{ $familyValue }}</textarea>
                                    @elseif (isset($selectOptions[$field]))
                                        <div class="relative">

                                            <select id="keluarga_{{ $jenis }}_{{ $field }}"
                                                name="keluarga[{{ $jenis }}][{{ $field }}]"
                                                class="{{ $selectClass }}">

                                                <option value="">Pilih {{ strtolower($label) }}</option>

                                                @if (filled($familyValue) && !in_array((string) $familyValue, $selectOptions[$field], true))
                                                    <option value="{{ $familyValue }}" selected>{{ $familyValue }}

                                                    </option>
                                                @endif

                                                @foreach ($selectOptions[$field] as $option)
                                                    <option value="{{ $option }}" @selected((string) $familyValue === $option)>

                                                        {{ $option }}</option>
                                                @endforeach

                                            </select>

                                            <i
                                                class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500"></i>

                                        </div>
                                    @else
                                        <input id="keluarga_{{ $jenis }}_{{ $field }}"
                                            name="keluarga[{{ $jenis }}][{{ $field }}]"
                                            type="{{ $type }}" value="{{ $familyValue }}"
                                            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                            @if ($type === 'number') min="0" step="1" @endif
                                            class="{{ $fieldClass }}">
                                    @endif

                                </div>
                            @endforeach

                        </div>

                    </div>
                @endforeach

            </section>

            {{-- TAB 4: WALI --}}

            <section x-show="activeTab === 'wali'" x-cloak id="wali" role="tabpanel" aria-labelledby="wali-tab"
                class="space-y-5">

                @php $wali = $keluargaByJenis->get('wali'); @endphp

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">

                        <h2 class="border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">Data wali</h2>

                        <p class="mt-1 text-sm text-slate-500">Isi jika mahasiswa memiliki data wali.</p>

                    </div>

                    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">

                        @foreach ($familyFields as $field => [$label, $type, $maxlength])
                            <div
                                class="{{ in_array($field, ['alamat', 'email'], true) ? 'sm:col-span-2 lg:col-span-3' : '' }}">

                                <label class="{{ $labelClass }}"
                                    for="keluarga_wali_{{ $field }}">{{ $label }}</label>

                                @php

                                    $familyValue = old("keluarga.wali.$field", data_get($wali, $field, ''));

                                    if ($field === 'tanggal_lahir' && $familyValue instanceof \DateTimeInterface) {
                                        $familyValue = $familyValue->format('Y-m-d');
                                    }

                                @endphp

                                @if ($type === 'textarea')
                                    <textarea id="keluarga_wali_{{ $field }}" name="keluarga[wali][{{ $field }}]" rows="3"
                                        class="{{ $fieldClass }}">{{ $familyValue }}</textarea>
                                @elseif (isset($selectOptions[$field]))
                                    <div class="relative">

                                        <select id="keluarga_wali_{{ $field }}"
                                            name="keluarga[wali][{{ $field }}]" class="{{ $selectClass }}">

                                            <option value="">Pilih {{ strtolower($label) }}</option>

                                            @if (filled($familyValue) && !in_array((string) $familyValue, $selectOptions[$field], true))
                                                <option value="{{ $familyValue }}" selected>{{ $familyValue }}

                                                </option>
                                            @endif

                                            @foreach ($selectOptions[$field] as $option)
                                                <option value="{{ $option }}" @selected((string) $familyValue === $option)>

                                                    {{ $option }}</option>
                                            @endforeach

                                        </select>

                                        <i
                                            class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500"></i>

                                    </div>
                                @else
                                    <input id="keluarga_wali_{{ $field }}"
                                        name="keluarga[wali][{{ $field }}]" type="{{ $type }}"
                                        value="{{ $familyValue }}"
                                        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                        @if ($type === 'number') min="0" step="1" @endif
                                        class="{{ $fieldClass }}">
                                @endif

                            </div>
                        @endforeach

                    </div>

                </div>

            </section>

            {{-- TAB 5: PENDIDIKAN ASAL --}}

            <section x-show="activeTab === 'pendidikan-asal'" x-cloak id="pendidikan-asal" role="tabpanel"
                aria-labelledby="pendidikan-asal-tab" class="space-y-5">

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">

                        <h2 class="border-b-2 border-emerald-600 pb-2 text-lg font-bold text-emerald-700">Pendidikan

                            sebelum masuk kampus ini</h2>

                    </div>

                    <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">

                        @foreach ($pendidikanFields as $field => [$label, $type, $maxlength])
                            <div
                                class="{{ in_array($field, ['perguruan_tinggi', 'program_studi_asal'], true) ? 'sm:col-span-2' : '' }}">

                                <label class="{{ $labelClass }}"
                                    for="{{ $field }}">{{ $label }}</label>

                                @php $nilaiPendidikan = old($field, data_get($pendidikanAsal, $field, '')); @endphp

                                @if (isset($selectOptions[$field]))
                                    <div class="relative">

                                        <select id="{{ $field }}" name="{{ $field }}"
                                            class="{{ $selectClass }}">

                                            <option value="">Pilih {{ strtolower($label) }}</option>

                                            @if (filled($nilaiPendidikan) && !in_array((string) $nilaiPendidikan, $selectOptions[$field], true))
                                                <option value="{{ $nilaiPendidikan }}" selected>{{ $nilaiPendidikan }}

                                                </option>
                                            @endif

                                            @foreach ($selectOptions[$field] as $option)
                                                <option value="{{ $option }}" @selected((string) $nilaiPendidikan === $option)>

                                                    {{ $option }}</option>
                                            @endforeach

                                        </select>

                                        <i
                                            class="bi bi-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500"></i>

                                    </div>
                                @else
                                    <input id="{{ $field }}" name="{{ $field }}"
                                        type="{{ $type }}" value="{{ $nilaiPendidikan }}"
                                        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                        @if ($field === 'ipk') min="0" max="4" step="0.01" @endif
                                        @if ($field === 'sks_diakui') min="0" max="65535" step="1" @endif
                                        class="{{ $fieldClass }}">
                                @endif

                            </div>
                        @endforeach

                    </div>

                </div>

            </section>

            {{-- TAB 6: BERKAS BIODATA --}}
            @php
                $berkasTersimpan = ($berkasBiodata ?? collect())->keyBy('jenis_berkas');
                $jenisBerkasUpload = [
                    'ijazah_terakhir' => 'Ijazah terakhir',
                    'transkrip' => 'Transkrip nilai',
                    'ktp' => 'Kartu Tanda Penduduk (KTP)',
                    'kartu_keluarga' => 'Kartu Keluarga (KK)',
                    'akta_kelahiran' => 'Akta kelahiran',
                    'pas_foto_3x4' => 'Pas foto 3 × 4',
                ];
                $maksBerkasKib = config('berkas.maks_kib');
            @endphp

            <section x-show="activeTab === 'berkas'" x-cloak id="berkas" role="tabpanel" aria-labelledby="berkas-tab"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-200 px-5 py-5 sm:px-6">
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-800">
                            <i class="bi bi-folder2-open text-xl" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-slate-900">Berkas pendukung</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Unggah atau ganti dokumen yang diperlukan. Berkas baru dikirim ketika kamu menekan
                                <strong class="font-semibold text-slate-800">Simpan perubahan</strong>.
                            </p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Format dokumen: PDF, JPG, JPEG, atau PNG. Pas foto hanya menerima JPG, JPEG, atau PNG.
                                @if ($maksBerkasKib)
                                    Ukuran maksimal {{ number_format($maksBerkasKib / 1024, 1, ',', '.') }} MB per berkas.
                                @endif
                            </p>
                        </div>
                    </div>
                </header>

                <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:gap-5 sm:p-6 xl:grid-cols-3">
                    @foreach ($jenisBerkasUpload as $jenis => $judul)
                        @php
                            $berkasAda = $berkasTersimpan->get($jenis);
                            $fieldBerkas = 'berkas_' . $jenis;
                            $errorBerkas = $errors->first($fieldBerkas);
                            $khususFoto = $jenis === 'pas_foto_3x4';
                        @endphp

                        <article x-data="{ fileName: '' }"
                            class="flex min-w-0 flex-col rounded-xl border border-slate-200 bg-white p-4 transition hover:border-emerald-300 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $khususFoto ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-700' }}">
                                    <i class="{{ $khususFoto ? 'bi bi-person-bounding-box' : 'bi bi-file-earmark-text' }} text-lg"
                                        aria-hidden="true"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-bold leading-5 text-slate-900">{{ $judul }}</h3>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ $khususFoto ? 'Foto formal ukuran 3 × 4.' : 'Dokumen pendukung biodata.' }}
                                    </p>
                                </div>
                                @if ($berkasAda)
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-700/15">
                                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                        Tersimpan
                                    </span>
                                @else
                                    <span
                                        class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">
                                        Belum ada
                                    </span>
                                @endif
                            </div>

                            <div
                                class="mt-4 flex-1 rounded-lg border border-dashed {{ $errorBerkas ? 'border-rose-300 bg-rose-50/50' : 'border-slate-300 bg-slate-50/70' }} p-3">
                                <label for="{{ $fieldBerkas }}"
                                    class="mb-2 block text-xs font-semibold text-slate-700">
                                    {{ $berkasAda ? 'Pilih berkas pengganti' : 'Pilih berkas dari perangkat' }}
                                </label>
                                <input id="{{ $fieldBerkas }}" name="{{ $fieldBerkas }}" type="file"
                                    accept="{{ $khususFoto ? '.jpg,.jpeg,.png' : '.pdf,.jpg,.jpeg,.png' }}"
                                    @change="fileName = $event.target.files && $event.target.files.length ? $event.target.files[0].name : ''"
                                    aria-describedby="{{ $fieldBerkas }}-help {{ $fieldBerkas }}-filename {{ $fieldBerkas }}-error"
                                    class="block w-full min-w-0 cursor-pointer text-xs text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-800 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                                <p id="{{ $fieldBerkas }}-help" class="mt-2 text-[11px] leading-5 text-slate-500">
                                    {{ $khususFoto ? 'JPG, JPEG, PNG' : 'PDF, JPG, JPEG, PNG' }}
                                    @if ($maksBerkasKib)
                                        · Maks. {{ number_format($maksBerkasKib / 1024, 1, ',', '.') }} MB
                                    @endif
                                </p>
                                <p id="{{ $fieldBerkas }}-filename" x-show="fileName" x-cloak
                                    class="mt-2 flex items-start gap-2 break-all text-xs font-medium text-emerald-800">
                                    <i class="bi bi-paperclip mt-0.5 shrink-0" aria-hidden="true"></i>
                                    <span x-text="fileName"></span>
                                </p>
                                @error('berkas_' . $jenis)
                                    <p id="{{ $fieldBerkas }}-error" class="mt-2 text-xs font-medium text-rose-700"
                                        role="alert">{{ $message }}</p>
                                @else
                                    <span id="{{ $fieldBerkas }}-error" class="hidden"></span>
                                @enderror
                            </div>

                            @if ($berkasAda)
                                <p class="mt-3 flex items-start gap-2 text-xs leading-5 text-emerald-800">
                                    <i class="bi bi-info-circle mt-0.5 shrink-0" aria-hidden="true"></i>
                                    <span>Dokumen lama tetap digunakan jika kamu tidak memilih berkas pengganti.</span>
                                </p>
                            @else
                                <p class="mt-3 text-xs leading-5 text-slate-500">Belum ada dokumen tersimpan untuk kategori
                                    ini.</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <div
                class="flex flex-col-reverse gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">

                <a href="{{ route('portal.biodata.show') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">

                    Batal

                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">

                    Simpan perubahan

                </button>

            </div>

        </form>

    </div>

@endsection
