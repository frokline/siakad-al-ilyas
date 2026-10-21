@php
    $mengedit = $mahasiswa->exists;

    $nilai = static function (string $kolom, mixed $default = ''): string {
        $hasil = old($kolom, $default);

        return is_scalar($hasil) ? (string) $hasil : '';
    };

    $tanggalMaksimal = now(config('siakad.timezone', 'Asia/Makassar'))->toDateString();
@endphp

@if ($mengedit)
    <input type="hidden" name="versi" value="{{ $nilai('versi', $mahasiswa->versiForm()) }}">

    @error('versi')
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
            <p>{{ $message }}</p>
            <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}"
                class="font-semibold underline mt-1 inline-block hover:text-rose-900">
                Muat ulang formulir
            </a>
        </div>
    @enderror
@else
    <input type="hidden" name="user_id" value="{{ $akun->id }}">
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="nim" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">NIM <span
                class="text-rose-500">*</span></label>
        <input type="text" id="nim" name="nim" maxlength="40"
            value="{{ $nilai('nim', $mahasiswa->nim) }}" autocomplete="off" aria-describedby="nim-help" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nim') border-rose-500 bg-rose-50/50 @enderror">
        <p class="mt-1 text-xs text-slate-500" id="nim-help">
            NIM harus unik. Huruf akan disimpan sebagai huruf kapital.
        </p>
        @error('nim')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jenis_kelamin"
            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Jenis Kelamin</label>
        <select id="jenis_kelamin" name="jenis_kelamin"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jenis_kelamin') border-rose-500 bg-rose-50/50 @enderror">
            <option value="">Belum diisi</option>
            @foreach (\App\Models\Mahasiswa::JENIS_KELAMIN as $kode => $label)
                <option value="{{ $kode }}" @selected($nilai('jenis_kelamin', $mahasiswa->jenis_kelamin) === $kode)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('jenis_kelamin')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tempat_lahir"
            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tempat Lahir</label>
        <input type="text" id="tempat_lahir" name="tempat_lahir" maxlength="100"
            value="{{ $nilai('tempat_lahir', $mahasiswa->tempat_lahir) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('tempat_lahir') border-rose-500 bg-rose-50/50 @enderror">
        @error('tempat_lahir')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_lahir"
            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tanggal Lahir</label>
        <input type="date" id="tanggal_lahir" name="tanggal_lahir" min="1900-01-01" max="{{ $tanggalMaksimal }}"
            value="{{ $nilai('tanggal_lahir', $mahasiswa->tanggal_lahir?->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('tanggal_lahir') border-rose-500 bg-rose-50/50 @enderror">
        @error('tanggal_lahir')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="alamat"
            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Alamat</label>
        <textarea id="alamat" name="alamat" rows="4" maxlength="2000"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('alamat') border-rose-500 bg-rose-50/50 @enderror">{{ $nilai('alamat', $mahasiswa->alamat) }}</textarea>
        @error('alamat')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="kebutuhan_khusus"
            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kebutuhan Khusus</label>
        <textarea id="kebutuhan_khusus" name="kebutuhan_khusus" rows="2" maxlength="2000"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kebutuhan_khusus') border-rose-500 bg-rose-50/50 @enderror">{{ $nilai('kebutuhan_khusus', $mahasiswa->kebutuhan_khusus) }}</textarea>
        @error('kebutuhan_khusus')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <h3 class="text-sm font-bold text-slate-800 border-t border-slate-200 pt-5 mb-4">
            Informasi Pendaftaran
        </h3>
    </div>

    <div>
        <label for="konsentrasi" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Konsentrasi
        </label>
        <input type="text" id="konsentrasi" name="konsentrasi" maxlength="150"
            value="{{ $nilai('konsentrasi', $mahasiswa->konsentrasi) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
        @error('konsentrasi')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sistem_kuliah" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Sistem Kuliah
        </label>
        <input type="text" id="sistem_kuliah" name="sistem_kuliah" maxlength="100"
            value="{{ $nilai('sistem_kuliah', $mahasiswa->sistem_kuliah) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
        @error('sistem_kuliah')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jenis_pendaftaran" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Jenis Pendaftaran
        </label>
        <input type="text" id="jenis_pendaftaran" name="jenis_pendaftaran" maxlength="100"
            value="{{ $nilai('jenis_pendaftaran', $mahasiswa->jenis_pendaftaran) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
        @error('jenis_pendaftaran')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jalur_pendaftaran" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Jalur Pendaftaran
        </label>
        <input type="text" id="jalur_pendaftaran" name="jalur_pendaftaran" maxlength="100"
            value="{{ $nilai('jalur_pendaftaran', $mahasiswa->jalur_pendaftaran) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
        @error('jalur_pendaftaran')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="gelombang" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Gelombang
        </label>
        <input type="text" id="gelombang" name="gelombang" maxlength="50"
            value="{{ $nilai('gelombang', $mahasiswa->gelombang) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
        @error('gelombang')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_awal_masuk"
            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Tanggal Awal Masuk
        </label>
        <input type="date" id="tanggal_awal_masuk" name="tanggal_awal_masuk"
            value="{{ $nilai('tanggal_awal_masuk', $mahasiswa->tanggal_awal_masuk?->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
        @error('tanggal_awal_masuk')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tanggal_daftar_ulang"
            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Tanggal Daftar Ulang
        </label>
        <input type="date" id="tanggal_daftar_ulang" name="tanggal_daftar_ulang"
            value="{{ $nilai('tanggal_daftar_ulang', $mahasiswa->tanggal_daftar_ulang?->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800">
        @error('tanggal_daftar_ulang')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

</div>
