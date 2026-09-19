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
    <div class="alert alert-error" role="alert">
        Data sudah berubah sejak formulir dibuka. <a href="{{ $muatUlang }}">Muat ulang formulir</a> sebelum
        melanjutkan.
    </div>
@endif
<fieldset class="jadwal-fieldset" @disabled(!$boleh || $usang)>
    <legend class="jadwal-sr-only">Pola jadwal mingguan</legend>
    <div class="form-grid">
        <div class="field">
            <label for="hari">Hari <span aria-hidden="true">*</span></label>
            <select id="hari" name="hari" required
                @if ($errors->has('hari')) aria-invalid="true" aria-describedby="hari-error" @endif>
                @foreach (\App\Models\JadwalKuliah::HARI as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('hari', $jadwal->hari) === (string) $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('hari')
                <p class="field-error" id="hari-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="metode">Metode <span aria-hidden="true">*</span></label>
            <select id="metode" name="metode" required>
                @foreach (\App\Models\JadwalKuliah::METODE as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('metode', $jadwal->metode) === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('metode')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="jam_mulai">Jam mulai <span aria-hidden="true">*</span></label>
            <input id="jam_mulai" name="jam_mulai" type="time" step="60" required
                value="{{ $nilai('jam_mulai', substr($jadwal->jam_mulai ?? '', 0, 5)) }}">
            @error('jam_mulai')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="jam_selesai">Jam selesai <span aria-hidden="true">*</span></label>
            <input id="jam_selesai" name="jam_selesai" type="time" step="60" required
                value="{{ $nilai('jam_selesai', substr($jadwal->jam_selesai ?? '', 0, 5)) }}">
            @error('jam_selesai')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="berlaku_mulai">Berlaku mulai <span aria-hidden="true">*</span></label>
            <input id="berlaku_mulai" name="berlaku_mulai" type="date" required
                min="{{ $kelas->rombel->periodeAkademik->mulai->toDateString() }}"
                max="{{ $kelas->rombel->periodeAkademik->selesai->toDateString() }}"
                value="{{ $nilai('berlaku_mulai', $jadwal->berlaku_mulai?->toDateString()) }}">
            @error('berlaku_mulai')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="berlaku_selesai">Berlaku sampai <span aria-hidden="true">*</span></label>
            <input id="berlaku_selesai" name="berlaku_selesai" type="date" required
                min="{{ $kelas->rombel->periodeAkademik->mulai->toDateString() }}"
                max="{{ $kelas->rombel->periodeAkademik->selesai->toDateString() }}"
                value="{{ $nilai('berlaku_selesai', $jadwal->berlaku_selesai?->toDateString()) }}">
            @error('berlaku_selesai')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field jadwal-wide">
            <label for="lokasi">Lokasi</label>
            <input id="lokasi" name="lokasi" type="text" maxlength="150"
                value="{{ $nilai('lokasi', $jadwal->lokasi) }}" aria-describedby="lokasi-help">
            <p class="help" id="lokasi-help">Wajib untuk luring/campuran. Kosongkan untuk daring.</p>
            @error('lokasi')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="aksi_tautan">Tautan pertemuan</label>
            <select id="aksi_tautan" name="aksi_tautan" required aria-describedby="tautan-help">
                <option value="pertahankan" @selected($nilai('aksi_tautan', $ubah ? 'pertahankan' : 'hapus') === 'pertahankan')>Pertahankan tautan tersimpan</option>
                <option value="ganti" @selected($nilai('aksi_tautan', $ubah ? 'pertahankan' : 'hapus') === 'ganti')>Isi / ganti tautan</option>
                <option value="hapus" @selected($nilai('aksi_tautan', $ubah ? 'pertahankan' : 'hapus') === 'hapus')>Tanpa tautan / hapus tautan</option>
            </select>
            <p class="help" id="tautan-help">
                {{ $jadwal->memilikiTautan() ? 'Ada tautan tersimpan.' : 'Belum ada tautan tersimpan.' }} Untuk luring,
                pilih tanpa tautan.</p>
            @error('aksi_tautan')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="tautan_pertemuan">Tautan baru</label>
            <input id="tautan_pertemuan" name="tautan_pertemuan" type="url" maxlength="2048" autocomplete="off"
                spellcheck="false" value="" placeholder="https://..." aria-describedby="tautan-baru-help">
            <p class="help" id="tautan-baru-help">Isi hanya jika memilih isi/ganti. Setelah validasi gagal, masukkan
                kembali tautan baru.</p>
            @error('tautan_pertemuan')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        @if ($ubah)
            <div class="field">
                <label for="aktif">Status pola <span aria-hidden="true">*</span></label>
                <select id="aktif" name="aktif" required>
                    <option value="1" @selected($nilai('aktif', $jadwal->aktif ? '1' : '0') === '1')>Aktif — memesan waktu</option>
                    <option value="0" @selected($nilai('aktif', $jadwal->aktif ? '1' : '0') === '0')>Nonaktif — tidak memesan waktu</option>
                </select>
                @error('aktif')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @endif
        <div class="field jadwal-wide">
            <label for="alasan">Alasan {{ $ubah ? 'perubahan' : 'pembuatan' }} <span
                    aria-hidden="true">*</span></label>
            <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="2000" required>{{ $nilai('alasan') }}</textarea>
            <p class="help">10–2.000 karakter. Jangan masukkan kata sandi atau tautan pertemuan dalam alasan.</p>
            @error('alasan')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <p class="jadwal-note">Jam memakai {{ config('siakad.timezone', 'Asia/Makassar') }}. Benturan rombel dan semua
        dosen aktif diperiksa saat menyimpan.</p>
    <label class="jadwal-confirm" for="konfirmasi">
        <input id="konfirmasi" name="konfirmasi" type="checkbox" value="1" required>
        <span>Saya telah memeriksa hari, jam, rentang tanggal, dan dampak perubahan jadwal.</span>
    </label>
    @error('konfirmasi')
        <p class="field-error">{{ $message }}</p>
    @enderror
    <div class="actions jadwal-actions">
        <button class="button" type="submit">{{ $ubah ? 'Simpan perubahan' : 'Simpan jadwal' }}</button>
        <a class="button secondary"
            href="{{ $ubah ? route('admin.jadwal-kuliah.show', $jadwal) : route('admin.jadwal-kuliah.index') }}">Batal</a>
    </div>
</fieldset>
