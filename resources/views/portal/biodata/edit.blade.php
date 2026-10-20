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

        $tabs = [
            'umum' => 'Informasi umum',
            'domisili' => 'Alamat & domisili',
            'orang-tua' => 'Orang tua',
            'wali' => 'Wali',
            'pendidikan-asal' => 'Pendidikan asal',
        ];

        $fieldClass =
            'mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100';
        $labelClass = 'block text-sm font-medium text-slate-700';
    @endphp

    <div x-data="{ activeTab: 'umum' }" class="w-full space-y-5 py-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-1.5 bg-emerald-700"></div>
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Portal mahasiswa</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">Edit Biodata Mahasiswa</h1>
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

        <form action="{{ route('portal.biodata.update') }}" method="POST" enctype="multipart/form-data"
            class="space-y-5">
            @csrf
            @method('PATCH')

            <nav class="rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm" aria-label="Bagian formulir biodata">
                <ul class="flex flex-nowrap gap-1 overflow-x-auto pb-1" role="tablist">
                    @foreach ($tabs as $id => $judul)
                        <li class="shrink-0" role="presentation">
                            <button type="button" role="tab" id="{{ $id }}-tab"
                                aria-controls="{{ $id }}"
                                :aria-selected="activeTab === '{{ $id }}' ? 'true' : 'false'"
                                @click="activeTab = '{{ $id }}'"
                                :class="activeTab === '{{ $id }}' ? 'bg-emerald-700 text-white shadow-sm' :
                                    'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                                class="rounded-lg px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                                {{ $judul }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- TAB 1: INFORMASI UMUM --}}
            <section x-show="activeTab === 'umum'" x-cloak id="umum" role="tabpanel" aria-labelledby="umum-tab"
                class="space-y-5">
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-slate-900">Data pribadi</h2>
                        <p class="mt-1 text-sm text-slate-500">NIM dan identitas akademik utama tidak diubah melalui
                            formulir ini.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                        @foreach ($biodataFields as $field => [$label, $type, $maxlength])
                            <div
                                class="{{ in_array($field, ['email_pribadi', 'instansi_pekerjaan', 'nama_rekening'], true) ? 'sm:col-span-2' : '' }}">
                                <label class="{{ $labelClass }}" for="{{ $field }}">{{ $label }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                    value="{{ old($field, data_get($biodata, $field, '')) }}"
                                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                    @if ($type === 'number') min="0" @endif
                                    @if ($field === 'berat_badan') max="255" step="1" @endif
                                    @if ($field === 'tinggi_badan') max="65535" step="1" @endif
                                    @if ($field === 'penghasilan') step="1" @endif class="{{ $fieldClass }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- TAB 2: ALAMAT --}}
            <section x-show="activeTab === 'domisili'" x-cloak id="domisili" role="tabpanel" aria-labelledby="domisili-tab"
                class="space-y-5">
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-slate-900">Alamat sesuai KTP</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                        @foreach ($alamatKtpFields as $field => [$label, $type, $maxlength])
                            <div class="{{ $type === 'textarea' ? 'sm:col-span-2 lg:col-span-3' : '' }}">
                                <label class="{{ $labelClass }}" for="{{ $field }}">{{ $label }}</label>
                                @if ($type === 'textarea')
                                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" class="{{ $fieldClass }}">{{ old($field, data_get($alamatBiodata, $field, '')) }}</textarea>
                                @else
                                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                        value="{{ old($field, data_get($alamatBiodata, $field, '')) }}"
                                        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                        class="{{ $fieldClass }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
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
                                <span class="mt-1 block text-emerald-800/80">Centang bila kamu tinggal di alamat yang
                                    sama.</span>
                            </span>
                        </label>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" x-data="{ sameAddress: {{ old('domisili_sama_dengan_ktp', data_get($alamatBiodata, 'domisili_sama_dengan_ktp', false)) ? 'true' : 'false' }} }"
                            x-init="$watch('$root.querySelector(\'input[name=domisili_sama_dengan_ktp][type=checkbox]\').checked', value => sameAddress = value)">
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
                                <input id="status_tinggal" name="status_tinggal" type="text" maxlength="50"
                                    value="{{ old('status_tinggal', data_get($alamatBiodata, 'status_tinggal', '')) }}"
                                    class="{{ $fieldClass }}">
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
                    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                            <h2 class="font-semibold text-slate-900">{{ $judul }}</h2>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
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
                                            name="keluarga[{{ $jenis }}][{{ $field }}]" rows="2" class="{{ $fieldClass }}">{{ $familyValue }}</textarea>
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
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-slate-900">Data wali</h2>
                        <p class="mt-1 text-sm text-slate-500">Isi jika mahasiswa memiliki data wali.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
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
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                        <h2 class="font-semibold text-slate-900">Pendidikan sebelum masuk kampus ini</h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                        @foreach ($pendidikanFields as $field => [$label, $type, $maxlength])
                            <div
                                class="{{ in_array($field, ['perguruan_tinggi', 'program_studi_asal'], true) ? 'sm:col-span-2' : '' }}">
                                <label class="{{ $labelClass }}"
                                    for="{{ $field }}">{{ $label }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                                    value="{{ old($field, data_get($pendidikanAsal, $field, '')) }}"
                                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                                    @if ($field === 'ipk') min="0" max="4" step="0.01" @endif
                                    @if ($field === 'sks_diakui') min="0" max="65535" step="1" @endif
                                    class="{{ $fieldClass }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- UPLOAD BERKAS BIODATA --}}
            @php
                $berkasTersimpan = ($berkasBiodata ?? collect())->keyBy('jenis_berkas');
                $jenisBerkasUpload = [
                    'ijazah_terakhir' => 'Scan Ijazah Asli',
                    'transkrip' => 'Scan Transkrip Nilai',
                    'ktp' => 'Scan KTP',
                    'kartu_keluarga' => 'Scan Kartu Keluarga (KK)',
                    'akta_kelahiran' => 'Scan Akta Kelahiran',
                    'pas_foto_3x4' => 'Pas Foto 3x4',
                ];
            @endphp

            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                    <h2 class="font-semibold text-slate-900">Berkas Biodata</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Unggah dokumen pendukung. Semua berkas akan disimpan bersamaan saat menekan tombol
                        <span class="font-semibold text-slate-700">Simpan perubahan</span>.
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
                                </div>
                                @if ($berkasAda)
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                        <i class="bi bi-check-circle"></i> Tersimpan
                                    </span>
                                @endif
                            </div>

                            <input id="berkas_{{ $jenis }}" name="berkas_{{ $jenis }}" type="file"
                                accept="{{ $jenis === 'pas_foto_3x4' ? '.jpg,.jpeg,.png' : '.pdf,.jpg,.jpeg,.png' }}"
                                class="block w-full rounded-lg border border-slate-300 p-2 text-sm">
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('portal.biodata.show') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    Simpan perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
