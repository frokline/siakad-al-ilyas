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
    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 flex items-center justify-between"
        role="alert">
        <span>Data kelas, jadwal, tim, atau pertemuan telah berubah.</span>
        <a href="{{ $muatUlang }}" class="font-semibold underline hover:text-rose-900">Muat ulang formulir</a>
    </div>
@endif

<fieldset @disabled(!$boleh || $usang)>
    <legend class="sr-only">Formulir Pertemuan</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <div>
            @if (!$ubah)
                <label for="nomor" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Nomor
                    Pertemuan <span class="text-rose-500">*</span></label>
                <input id="nomor" name="nomor" type="number" min="1" max="65535" required
                    value="{{ $nilai('nomor', $sesi->nomor) }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nomor') border-rose-500 bg-rose-50/50 @enderror">
                @error('nomor')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            @else
                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wider">Nomor
                    Pertemuan</label>
                <p class="font-mono font-bold text-slate-800 bg-slate-100 rounded-lg px-4 py-2.5 inline-block">
                    {{ $sesi->nomor }}</p>
            @endif
        </div>

        <div>
            <label for="jenis" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Jenis
                <span class="text-rose-500">*</span></label>
            <select id="jenis" name="jenis" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jenis') border-rose-500 bg-rose-50/50 @enderror">
                @foreach (\App\Models\Pertemuan::JENIS as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('jenis', $sesi->jenis) === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('jenis')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="topik" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Topik /
                Judul <span class="text-rose-500">*</span></label>
            <input id="topik" name="topik" type="text" maxlength="200" required
                value="{{ $nilai('topik', $sesi->topik) }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('topik') border-rose-500 bg-rose-50/50 @enderror">
            @error('topik')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="rencana" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Rencana
                Pembelajaran</label>
            <textarea id="rencana" name="rencana" rows="3" maxlength="10000"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('rencana') border-rose-500 bg-rose-50/50 @enderror">{{ $nilai('rencana', $sesi->rencana) }}</textarea>
            @error('rencana')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tanggal" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tanggal
                Rencana <span class="text-rose-500">*</span></label>
            <input id="tanggal" name="tanggal" type="date" required
                min="{{ $kelas->rombel->periodeAkademik->mulai->toDateString() }}"
                max="{{ $kelas->rombel->periodeAkademik->selesai->toDateString() }}"
                value="{{ $nilai('tanggal', $tanggalDefault) }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('tanggal') border-rose-500 bg-rose-50/50 @enderror">
            @error('tanggal')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="jam_mulai" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Jam
                    Mulai <span class="text-rose-500">*</span></label>
                <input id="jam_mulai" name="jam_mulai" type="time" step="60" required
                    value="{{ $nilai('jam_mulai', $mulaiDefault) }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jam_mulai') border-rose-500 bg-rose-50/50 @enderror">
                @error('jam_mulai')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="jam_selesai"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Jam Selesai <span
                        class="text-rose-500">*</span></label>
                <input id="jam_selesai" name="jam_selesai" type="time" step="60" required
                    value="{{ $nilai('jam_selesai', $selesaiDefault) }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jam_selesai') border-rose-500 bg-rose-50/50 @enderror">
                @error('jam_selesai')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="pengajar_kelas_id"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Penanggung Jawab <span
                    class="text-rose-500">*</span></label>
            <select id="pengajar_kelas_id" name="pengajar_kelas_id" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('pengajar_kelas_id') border-rose-500 bg-rose-50/50 @enderror">
                <option value="">Pilih dosen</option>
                @foreach ($kelas->pengajarKelas->where('aktif', true)->sortBy('dosen_id') as $anggota)
                    <option value="{{ $anggota->id }}" @selected($nilai('pengajar_kelas_id', $pengajarDefault) === (string) $anggota->id)>
                        {{ $anggota->dosen->user->nama }} — {{ $anggota->dosen->kode_dosen }}
                    </option>
                @endforeach
            </select>
            @error('pengajar_kelas_id')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="jadwal_kuliah_id"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Pola Jadwal Sumber</label>
            <select id="jadwal_kuliah_id" name="jadwal_kuliah_id"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jadwal_kuliah_id') border-rose-500 bg-rose-50/50 @enderror">
                <option value="">Pertemuan tambahan tanpa pola sumber</option>
                @foreach ($kelas->jadwalKuliah->sortBy(['hari', 'jam_mulai', 'id']) as $pola)
                    <option value="{{ $pola->id }}" @selected($nilai('jadwal_kuliah_id', $sumberDefault) === (string) $pola->id)>
                        {{ \App\Models\JadwalKuliah::HARI[$pola->hari] }}
                        {{ substr($pola->jam_mulai, 0, 5) }}–{{ substr($pola->jam_selesai, 0, 5) }} &middot;
                        {{ $pola->berlaku_mulai->format('d-m-Y') }}–{{ $pola->berlaku_selesai->format('d-m-Y') }}{{ $pola->aktif ? '' : ' (nonaktif)' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Jika pola dipilih, tanggal dan jam sesi wajib selaras. Kosongkan
                untuk sesi pengganti/tambahan.</p>
            @error('jadwal_kuliah_id')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="metode" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Metode
                <span class="text-rose-500">*</span></label>
            <select id="metode" name="metode" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('metode') border-rose-500 bg-rose-50/50 @enderror">
                @foreach (\App\Models\JadwalKuliah::METODE as $kode => $label)
                    <option value="{{ $kode }}" @selected($nilai('metode', $sesi->metode) === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('metode')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="lokasi" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Lokasi
                / Ruangan</label>
            <input id="lokasi" name="lokasi" type="text" maxlength="150"
                value="{{ $nilai('lokasi', $sesi->lokasi) }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('lokasi') border-rose-500 bg-rose-50/50 @enderror">
            <p class="mt-1 text-xs text-slate-500">Wajib diisi untuk metode luring/campuran.</p>
            @error('lokasi')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="aksi_tautan"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tautan Pertemuan <span
                    class="text-rose-500">*</span></label>
            <select id="aksi_tautan" name="aksi_tautan" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('aksi_tautan') border-rose-500 bg-rose-50/50 @enderror">
                <option value="pertahankan" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'pertahankan')>Pertahankan tautan tersimpan</option>
                <option value="ganti" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'ganti')>Isi / ganti tautan</option>
                <option value="hapus" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'hapus')>Hapus / tanpa tautan</option>
                @if ($sesi->jadwal_kuliah_id || $kelas->jadwalKuliah->where('aktif', true)->isNotEmpty())
                    <option value="salin_jadwal" @selected($nilai('aksi_tautan', $aksiTautanDefault) === 'salin_jadwal')>Salin dari pola sumber</option>
                @endif
            </select>
            <p class="mt-1 text-xs text-slate-500">Metode luring tidak boleh memiliki tautan. Jika metode diubah ke
                luring,
                pilih <strong>Hapus</strong>.</p>
            @error('aksi_tautan')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tautan_pertemuan"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tautan Baru</label>
            <input id="tautan_pertemuan" name="tautan_pertemuan" type="url" maxlength="2048" autocomplete="off"
                spellcheck="false" placeholder="https://..."
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('tautan_pertemuan') border-rose-500 bg-rose-50/50 @enderror">
            <p class="mt-1 text-xs text-slate-500">Hanya format HTTPS valid yang diizinkan.</p>
            @error('tautan_pertemuan')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="alasan" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Alasan
                {{ $ubah ? 'Perubahan' : 'Pembuatan' }} <span aria-hidden="true">*</span></label>
            <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="2000" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('alasan') border-rose-500 bg-rose-50/50 @enderror">{{ $nilai('alasan') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Wajib diisi 10–2.000 karakter. Jangan cantumkan rahasia rapat /
                password.</p>
            @error('alasan')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-lg bg-blue-50 border border-blue-200 p-4 text-xs text-blue-800 mb-6">
        Waktu yang Anda isikan dianggap sebagai zona waktu <strong>{{ $zona }}</strong> lalu disimpan sebagai
        UTC ke dalam basis data. Sesi yang melewati batas waktu tidak akan otomatis selesai hingga Anda mengubahnya.
    </div>

    <div class="mb-6">
        <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi">
            <input id="konfirmasi" name="konfirmasi" type="checkbox" value="1" required
                class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark">
            <span class="text-sm font-medium text-slate-700">Saya telah memastikan kelas, dosen, waktu, metode, dan
                sumber jadwal sudah valid.</span>
        </label>
        @error('konfirmasi')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
        <a class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
            href="{{ $ubah ? route('admin.pertemuan.show', $sesi) : route('admin.pertemuan.index') }}">Batal</a>
        <button
            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2"
            type="submit">
            {{ $ubah ? 'Simpan Perubahan' : 'Buat Pertemuan' }}
        </button>
    </div>
</fieldset>
