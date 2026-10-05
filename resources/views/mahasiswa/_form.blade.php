@php
    $mengedit = $mahasiswa->exists;

    $nilai = static function (string $kolom, mixed $default = ''): string {
        $hasil = old($kolom, $default);

        return is_scalar($hasil) ? (string) $hasil : '';
    };

    $tanggalMaksimal = now(config('siakad.timezone', 'Asia/Makassar'))->toDateString();
@endphp

<div class="space-y-6">
    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
        <strong class="font-bold text-slate-800 text-base block">{{ $akun->nama }}</strong>
        <span class="text-xs text-slate-500 block mt-0.5">{{ $akun->email }}</span>
        <p class="text-xs text-slate-400 mt-2">
            Nama, email, dan status akun dikelola melalui menu Pengguna.
        </p>
    </div>

    @error('user_id')
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
            {{ $message }}
        </div>
    @enderror

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
    </div>

    <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
        <a class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
            href="{{ $mengedit ? route('admin.mahasiswa.show', $mahasiswa) : route('admin.mahasiswa.index') }}">
            Batal
        </a>
        <button type="submit" @disabled($errors->has('versi'))
            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 disabled:opacity-50">
            {{ $mengedit ? 'Simpan Perubahan' : 'Simpan Mahasiswa' }}
        </button>
    </div>
</div>
