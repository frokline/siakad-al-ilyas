@php
    $ubah = $sesi->exists;
    $zona = config('siakad.timezone', 'Asia/Makassar');
    $nilai = static function (string $key, mixed $default = ''): string {
        $value = old($key, $default);
        return is_string($value) || is_numeric($value) ? (string) $value : '';
    };
    $tokenLama = old('versi_pertemuan');
    $usang = $tokenLama !== null && (!is_string($tokenLama) || !hash_equals($versi, $tokenLama));
    $muatUlang = $ubah
        ? route('admin.pertemuan.edit', $sesi)
        : route('admin.pertemuan.create', ['kelas_id' => $kelas->id]);
    $tanggalDefault = $sesi->mulai_rencana?->setTimezone($zona)->toDateString();
    $mulaiDefault = $sesi->mulai_rencana?->setTimezone($zona)->format('H:i');
    $selesaiDefault = $sesi->selesai_rencana?->setTimezone($zona)->format('H:i');
    $sumberDefault = $sesi->jadwal_kuliah_id;
    $pengajarDefault = $sesi->pengajar_kelas_id;
    $aksiTautanDefault = $sesi->memilikiTautan() ? 'pertahankan' : 'hapus';
@endphp
@csrf
@if ($ubah)
    @method('PATCH')
@else
    <input type="hidden" name="kelas_kuliah_id" value="{{ $kelas->id }}">
@endif
<input type="hidden" name="versi_pertemuan" value="{{ $versi }}">
@if ($usang)
    <div class="alert alert-error" role="alert">
        Data kelas, jadwal, tim, atau pertemuan telah berubah. <a href="{{ $muatUlang }}">Muat ulang formulir</a>.
    </div>
@endif
<fieldset class="pertemuan-fieldset" @disabled(!$boleh || $usang)>
    <legend class="pertemuan-sr-only">Formulir pertemuan</legend>
    <div class="form-grid">
        @if (!$ubah)
            <div class="field">
                <label for="nomor">Nomor pertemuan <span aria-hidden="true">*</span></label>
                <input id="nomor" name="nomor" type="number" min="1" max="65535" required
                    value="{{ $nilai('nomor', $sesi->nomor) }}">
                @error('nomor')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div class="field"><label>Nomor pertemuan</label>
                <p class="pertemuan-readonly">{{ $sesi->nomor }}</p>
            </div>
        @endif
        <div class="field">
            <label for="jenis">Jenis <span aria-hidden="true">*</span></label>
            <select id="jenis" name="jenis" required>
                @foreach (\App\Models\Pertemuan::JENIS as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('jenis', $sesi->jenis) === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('jenis')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field pertemuan-wide">
            <label for="topik">Topik / judul <span aria-hidden="true">*</span></label>
            <input id="topik" name="topik" type="text" maxlength="200" required
                value="{{ $nilai('topik', $sesi->topik) }}">
            @error('topik')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field pertemuan-wide">
            <label for="rencana">Rencana pembelajaran</label>
            <textarea id="rencana" name="rencana" rows="3" maxlength="10000">{{ $nilai('rencana', $sesi->rencana) }}</textarea>
            @error('rencana')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="tanggal">Tanggal rencana <span aria-hidden="true">*</span></label>
            <input id="tanggal" name="tanggal" type="date" required
                min="{{ $kelas->rombel->periodeAkademik->mulai->toDateString() }}"
                max="{{ $kelas->rombel->periodeAkademik->selesai->toDateString() }}"
                value="{{ $nilai('tanggal', $tanggalDefault) }}">
            @error('tanggal')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="jam_mulai">Jam mulai <span aria-hidden="true">*</span></label>
            <input id="jam_mulai" name="jam_mulai" type="time" step="60" required
                value="{{ $nilai('jam_mulai', $mulaiDefault) }}">
            @error('jam_mulai')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="jam_selesai">Jam selesai <span aria-hidden="true">*</span></label>
            <input id="jam_selesai" name="jam_selesai" type="time" step="60" required
                value="{{ $nilai('jam_selesai', $selesaiDefault) }}">
            @error('jam_selesai')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="pengajar_kelas_id">Penanggung jawab <span aria-hidden="true">*</span></label>
            <select id="pengajar_kelas_id" name="pengajar_kelas_id" required>
                <option value="">Pilih dosen</option>
                @foreach ($kelas->pengajarKelas->where('aktif', true)->sortBy('dosen_id') as $anggota)
                    <option value="{{ $anggota->id }}" @selected($nilai('pengajar_kelas_id', $pengajarDefault) === (string) $anggota->id)>{{ $anggota->dosen->user->nama }}
                        — {{ $anggota->dosen->kode_dosen }}</option>
                @endforeach
            </select>
            @error('pengajar_kelas_id')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field pertemuan-wide">
            <label for="jadwal_kuliah_id">Pola jadwal sumber</label>
            <select id="jadwal_kuliah_id" name="jadwal_kuliah_id">
                <option value="">Pertemuan tambahan tanpa pola sumber</option>
                @foreach ($kelas->jadwalKuliah->sortBy(['hari', 'jam_mulai', 'id']) as $pola)
                    <option value="{{ $pola->id }}" @selected($nilai('jadwal_kuliah_id', $sumberDefault) === (string) $pola->id)>
                        {{ \App\Models\JadwalKuliah::HARI[$pola->hari] }}
                        {{ substr($pola->jam_mulai, 0, 5) }}–{{ substr($pola->jam_selesai, 0, 5) }} ·
                        {{ $pola->berlaku_mulai->format('d-m-Y') }}–{{ $pola->berlaku_selesai->format('d-m-Y') }}{{ $pola->aktif ? '' : ' (nonaktif)' }}
                    </option>
                @endforeach
            </select>
            <p class="help">Jika pola dipilih, tanggal/hari dan jam sesi harus berada dalam pola tersebut. Kosongkan
                untuk sesi tambahan.</p>
            @error('jadwal_kuliah_id')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="metode">Metode <span aria-hidden="true">*</span></label>
            <select id="metode" name="metode" required>
                @foreach (\App\Models\JadwalKuliah::METODE as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('metode', $sesi->metode) === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('metode')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="lokasi">Lokasi</label>
            <input id="lokasi" name="lokasi" type="text" maxlength="150"
                value="{{ $nilai('lokasi', $sesi->lokasi) }}">
            <p class="help">Wajib luring/campuran; kosongkan untuk daring.</p>
            @error('lokasi')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="aksi_tautan">Tautan pertemuan <span aria-hidden="true">*</span></label>
            <select id="aksi_tautan" name="aksi_tautan" required>
                <option value="pertahankan" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'pertahankan')>Pertahankan tersimpan</option>
                <option value="ganti" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'ganti')>Isi / ganti tautan</option>
                <option value="hapus" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'hapus')>Hapus / tanpa tautan</option>
                @if ($sesi->jadwal_kuliah_id)
                    <option value="salin_jadwal" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'salin_jadwal')>Salin dari pola sumber</option>
                @endif
            </select>
            @error('aksi_tautan')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="tautan_pertemuan">Tautan baru</label>
            <input id="tautan_pertemuan" name="tautan_pertemuan" type="url" maxlength="2048" autocomplete="off"
                spellcheck="false" value="" placeholder="https://...">
            <p class="help">Hanya HTTPS. Nilai tidak dipulihkan ke formulir setelah gagal.</p>
            @error('tautan_pertemuan')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field pertemuan-wide">
            <label for="alasan">Alasan {{ $ubah ? 'perubahan' : 'pembuatan' }} <span
                    aria-hidden="true">*</span></label>
            <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="2000" required>{{ $nilai('alasan') }}</textarea>
            <p class="help">10–2.000 karakter. Jangan masukkan rahasia rapat atau password.</p>
            @error('alasan')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <p class="pertemuan-note">Waktu yang dikirim dianggap zona {{ $zona }} lalu disimpan sebagai UTC. Sesi
        yang lewat tidak otomatis selesai.</p>
    <label class="pertemuan-confirm" for="konfirmasi">
        <input id="konfirmasi" name="konfirmasi" type="checkbox" value="1" required>
        <span>Saya telah memeriksa kelas, dosen, waktu, metode, dan sumber jadwal.</span>
    </label>
    @error('konfirmasi')
        <p class="field-error">{{ $message }}</p>
    @enderror
    <div class="actions pertemuan-actions">
        <button class="button" type="submit">{{ $ubah ? 'Simpan perubahan' : 'Buat pertemuan' }}</button>
        <a class="button secondary"
            href="{{ $ubah ? route('admin.pertemuan.show', $sesi) : route('admin.pertemuan.index') }}">Batal</a>
    </div>
</fieldset>
