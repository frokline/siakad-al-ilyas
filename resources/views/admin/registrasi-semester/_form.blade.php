@csrf

@if ($registrasi->exists)
    @method('PATCH')
    <input type="hidden" name="versi" value="{{ old('versi', $versi) }}">
@else
    <input type="hidden" name="riwayat_studi_id" value="{{ $riwayat->id }}">
@endif

@if (!$registrasi->exists || $registrasi->penempatanDapatDiubah())
    <div class="field">
        <label for="rombel_id">Rombel <span aria-hidden="true">*</span></label>
        <select id="rombel_id" name="rombel_id" required aria-describedby="rombel-help"
            aria-invalid="{{ $errors->has('rombel_id') ? 'true' : 'false' }}">
            <option value="">Pilih rombel</option>
            @foreach ($rombelPilihan as $pilihan)
                <option value="{{ $pilihan->id }}" @selected((string) old('rombel_id', $registrasi->rombel_id) === (string) $pilihan->id)>
                    {{ $pilihan->kode }}
                    · Semester {{ $pilihan->paketSemester->semester_studi }}
                    · {{ $pilihan->paketSemester->nama }}
                    · Versi {{ $pilihan->paketSemester->versi }}
                    · Kursi {{ $pilihan->kursi_terpakai }}/{{ $pilihan->kapasitas ?? 'tanpa batas' }}
                    @if ($pilihan->paketSemester->status !== \App\Models\PaketSemester::DITERBITKAN)
                        · Paket arsip, penempatan lama
                    @endif
                </option>
            @endforeach
        </select>
        <p class="help" id="rombel-help">
            Semester studi mengikuti paket rombel. Kapasitas diperiksa kembali saat disimpan.
            Setelah aktivasi pertama, rombel dan semester studi terkunci.
        </p>
        @error('rombel_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@else
    <div class="field">
        <p><strong>Rombel:</strong> {{ $registrasi->rombel->kode }}</p>
        <p class="help">
            Semester {{ $registrasi->semester_studi }}.
            Penempatan telah dikunci sejak aktivasi pertama.
        </p>
    </div>
@endif

@if ($registrasi->exists)
    <div class="field">
        <label for="status">Status registrasi <span aria-hidden="true">*</span></label>
        <select id="status" name="status" required aria-describedby="status-help">
            @foreach ($registrasi->pilihanStatus() as $kode => $label)
                <option value="{{ $kode }}" @selected(old('status', $registrasi->status) === $kode)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <p class="help" id="status-help">
            Terdaftar dan aktif mengisi kursi. Cuti dan batal melepaskan kursi.
            Pengaktifan kembali membutuhkan kursi tersedia.
        </p>
        @error('status')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="alasan_status">Alasan status</label>
        <textarea id="alasan_status" name="alasan_status" rows="4" minlength="10" maxlength="1000"
            aria-describedby="alasan-help">{{ old('alasan_status', $registrasi->alasan_status) }}</textarea>
        <p class="help" id="alasan-help">
            Wajib 10–1.000 karakter untuk cuti atau batal.
            Tidak disimpan untuk status terdaftar atau aktif.
        </p>
        @error('alasan_status')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
@else
    <p class="help">
        Registrasi baru berstatus <strong>terdaftar</strong>.
        Aktivasi dilakukan melalui halaman edit setelah data diperiksa.
    </p>
@endif

<label class="registrasi-confirm" for="konfirmasi">
    <input type="checkbox" id="konfirmasi" name="konfirmasi" value="1" required>
    <span>Saya telah memeriksa mahasiswa, periode, rombel, dan status registrasi.</span>
</label>
@error('konfirmasi')
    <p class="field-error">{{ $message }}</p>
@enderror

@if ($errors->has('versi') && $registrasi->exists)
    <div class="alert alert-error" role="alert">
        {{ $errors->first('versi') }}
        <a href="{{ route('admin.registrasi-semester.edit', $registrasi) }}">Muat formulir terbaru</a>
    </div>
@endif

<div class="actions registrasi-form-actions">
    <button type="submit" class="button" @disabled($errors->has('versi'))>
        {{ $registrasi->exists ? 'Simpan perubahan' : 'Simpan registrasi' }}
    </button>
    <a class="button secondary"
        href="{{ $registrasi->exists
            ? route('admin.registrasi-semester.show', $registrasi)
            : route('admin.registrasi-semester.index') }}">
        Kembali
    </a>
</div>
