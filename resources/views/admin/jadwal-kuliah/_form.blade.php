@php
    $ubah = $jadwal->exists;
    $nilai = static function (string $key, mixed $default = ''): string {
        $value = old($key, $default);
        return is_string($value) || is_numeric($value) ? (string) $value : '';
    };
    $tokenLama = old('versi_jadwal');
    $usang = $tokenLama !== null && (!is_string($tokenLama) || !hash_equals($versi, $tokenLama));
    $muatUlang = $ubah
        ? route('admin.jadwal-kuliah.edit', $jadwal)
        : route('admin.jadwal-kuliah.create', ['kelas_id' => $kelas->id]);
@endphp

@csrf
@if ($ubah)
    @method('PATCH')
@else
    <input type="hidden" name="kelas_kuliah_id" value="{{ $kelas->id }}">
@endif
<input type="hidden" name="versi_jadwal" value="{{ $versi }}">

@if ($usang)
    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 flex items-center justify-between"
        role="alert">
        <span>Data sudah berubah sejak formulir dibuka.</span>
        <a href="{{ $muatUlang }}" class="font-semibold underline hover:text-rose-900">Muat ulang formulir</a>
        <span>sebelum melanjutkan.</span>
    </div>
@endif

<fieldset @disabled(!$boleh || $usang)>
    <legend class="sr-only">Pola jadwal mingguan</legend>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="hari" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Hari
                <span class="text-rose-500">*</span></label>
            <select id="hari" name="hari" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('hari') border-rose-500 bg-rose-50/50 @enderror"
                @if ($errors->has('hari')) aria-invalid="true" aria-describedby="hari-error" @endif>
                @foreach (\App\Models\JadwalKuliah::HARI as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('hari', $jadwal->hari) === (string) $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('hari')
                <p class="mt-1 text-xs font-semibold text-rose-600" id="hari-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="metode" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Metode
                <span class="text-rose-500">*</span></label>
            <select id="metode" name="metode" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('metode') border-rose-500 bg-rose-50/50 @enderror">
                @foreach (\App\Models\JadwalKuliah::METODE as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('metode', $jadwal->metode) === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('metode')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="jam_mulai" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Jam
                Mulai <span class="text-rose-500">*</span></label>
            <input id="jam_mulai" name="jam_mulai" type="time" step="60" required
                value="{{ $nilai('jam_mulai', substr($jadwal->jam_mulai ?? '', 0, 5)) }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jam_mulai') border-rose-500 bg-rose-50/50 @enderror">
            @error('jam_mulai')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="jam_selesai" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Jam
                Selesai <span class="text-rose-500">*</span></label>
            <input id="jam_selesai" name="jam_selesai" type="time" step="60" required
                value="{{ $nilai('jam_selesai', substr($jadwal->jam_selesai ?? '', 0, 5)) }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jam_selesai') border-rose-500 bg-rose-50/50 @enderror">
            @error('jam_selesai')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="berlaku_mulai"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Berlaku Mulai <span
                    class="text-rose-500">*</span></label>
            <input id="berlaku_mulai" name="berlaku_mulai" type="date" required
                min="{{ $kelas->rombel->periodeAkademik->mulai->toDateString() }}"
                max="{{ $kelas->rombel->periodeAkademik->selesai->toDateString() }}"
                value="{{ $nilai('berlaku_mulai', $jadwal->berlaku_mulai?->toDateString()) }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('berlaku_mulai') border-rose-500 bg-rose-50/50 @enderror">
            @error('berlaku_mulai')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="berlaku_selesai"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Berlaku Sampai <span
                    class="text-rose-500">*</span></label>
            <input id="berlaku_selesai" name="berlaku_selesai" type="date" required
                min="{{ $kelas->rombel->periodeAkademik->mulai->toDateString() }}"
                max="{{ $kelas->rombel->periodeAkademik->selesai->toDateString() }}"
                value="{{ $nilai('berlaku_selesai', $jadwal->berlaku_selesai?->toDateString()) }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('berlaku_selesai') border-rose-500 bg-rose-50/50 @enderror">
            @error('berlaku_selesai')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="lokasi"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Lokasi</label>
            <input id="lokasi" name="lokasi" type="text" maxlength="150"
                value="{{ $nilai('lokasi', $jadwal->lokasi) }}" aria-describedby="lokasi-help"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('lokasi') border-rose-500 bg-rose-50/50 @enderror">
            <p class="mt-1 text-xs text-slate-500" id="lokasi-help">Wajib diisi untuk metode luring/campuran. Kosongkan
                untuk metode daring.</p>
            @error('lokasi')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="aksi_tautan" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tautan
                Pertemuan</label>
            <select id="aksi_tautan" name="aksi_tautan" required aria-describedby="tautan-help"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('aksi_tautan') border-rose-500 bg-rose-50/50 @enderror">
                <option value="pertahankan" @selected($nilai('aksi_tautan', $ubah ? 'pertahankan' : 'hapus') === 'pertahankan')>Pertahankan tautan tersimpan</option>
                <option value="ganti" @selected($nilai('aksi_tautan', $ubah ? 'pertahankan' : 'hapus') === 'ganti')>Isi / ganti tautan</option>
                <option value="hapus" @selected($nilai('aksi_tautan', $ubah ? 'pertahankan' : 'hapus') === 'hapus')>Tanpa tautan / hapus tautan</option>
            </select>
            <p class="mt-1 text-xs text-slate-500" id="tautan-help">
                <span
                    class="font-semibold text-slate-600">{{ $jadwal->memilikiTautan() ? 'Ada tautan tersimpan.' : 'Belum ada tautan tersimpan.' }}</span>
                Untuk luring, pilih tanpa tautan.
            </p>
            @error('aksi_tautan')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tautan_pertemuan"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tautan Baru</label>
            <input id="tautan_pertemuan" name="tautan_pertemuan" type="url" maxlength="2048" autocomplete="off"
                spellcheck="false" value="" placeholder="https://..." aria-describedby="tautan-baru-help"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('tautan_pertemuan') border-rose-500 bg-rose-50/50 @enderror">
            <p class="mt-1 text-xs text-slate-500" id="tautan-baru-help">Isi hanya jika memilih isi/ganti tautan di
                samping. Jika validasi gagal, masukkan kembali tautan baru.</p>
            @error('tautan_pertemuan')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($ubah)
            <div class="md:col-span-2">
                <label for="aktif"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status Pola <span
                        class="text-rose-500">*</span></label>
                <select id="aktif" name="aktif" required
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('aktif') border-rose-500 bg-rose-50/50 @enderror">
                    <option value="1" @selected($nilai('aktif', $jadwal->aktif ? '1' : '0') === '1')>Aktif — memesan waktu</option>
                    <option value="0" @selected($nilai('aktif', $jadwal->aktif ? '1' : '0') === '0')>Nonaktif — melepaskan waktu</option>
                </select>
                @error('aktif')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div class="md:col-span-2">
            <label for="alasan" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Alasan
                {{ $ubah ? 'Perubahan' : 'Pembuatan' }} <span aria-hidden="true">*</span></label>
            <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="2000" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('alasan') border-rose-500 bg-rose-50/50 @enderror">{{ $nilai('alasan') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Wajib 10–2.000 karakter. Jangan masukkan kata sandi atau tautan
                pertemuan di dalam kotak alasan ini.</p>
            @error('alasan')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-lg bg-blue-50 border border-blue-200 p-4 text-xs text-blue-800 mb-6 flex gap-3 items-start">
        <svg class="h-5 w-5 flex-shrink-0 mt-0.5 text-blue-600" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            Jam menggunakan zona waktu <strong>{{ config('siakad.timezone', 'Asia/Makassar') }}</strong>. Benturan
            jadwal dengan rombel lain dan semua dosen yang aktif akan diperiksa secara otomatis saat Anda menyimpan form
            ini.
        </div>
    </div>

    <div class="mb-6">
        <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi">
            <input class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark" id="konfirmasi"
                name="konfirmasi" type="checkbox" value="1" required>
            <span class="text-sm font-medium text-slate-700">Saya telah memeriksa hari, jam, rentang tanggal, dan
                menyadari dampak perubahan jadwal ini.</span>
        </label>
        @error('konfirmasi')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
        <a class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
            href="{{ $ubah ? route('admin.jadwal-kuliah.show', $jadwal) : route('admin.jadwal-kuliah.index') }}">
            Batal
        </a>
        <button
            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2"
            type="submit">
            {{ $ubah ? 'Simpan perubahan' : 'Simpan jadwal' }}
        </button>
    </div>
</fieldset>
