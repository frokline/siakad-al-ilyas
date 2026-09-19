@php
    $mengubah = $penugasan !== null && $penugasan->exists;
    $formLama = $errors->has('versi_tim');
    $peranLama = old('peran', $peranAwal);
    $peranLama = is_string($peranLama) && isset(\App\Models\PengajarKelas::PERAN[$peranLama]) ? $peranLama : $peranAwal;
    $alasanLama = is_string(old('alasan')) ? old('alasan') : '';
    $dosenLama = is_scalar(old('dosen_id')) ? (string) old('dosen_id') : '';
    $aktifLama = old('aktif', $mengubah ? $penugasan->aktif : true);
    if (!in_array($aktifLama, [true, false, 0, 1, '0', '1'], true)) {
        $aktifLama = $mengubah ? $penugasan->aktif : true;
    }
    $aktifLama = (bool) $aktifLama;
    $koordinatorForm = $kelas->pengajarKelas->first(fn($row) => $row->isKoordinatorAktif());
@endphp

@csrf
<input type="hidden" name="versi_tim" value="{{ $versiTim }}">
@if ($mengubah)
    @method('PATCH')
@else
    <input type="hidden" name="kelas_kuliah_id" value="{{ $kelas->id }}">
@endif

@if ($formLama)
    <div class="alert alert-error" role="alert">
        Kelas atau tim berubah sejak formulir dibuka.
        <a
            href="{{ $mengubah ? route('admin.pengajar-kelas.edit', $penugasan) : route('admin.pengajar-kelas.create', ['kelas_id' => $kelas->id]) }}">Muat
            formulir terbaru</a>
        dan periksa kembali tim sebelum menyimpan.
    </div>
@endif

<fieldset class="pengajar-fieldset" @disabled(!$bolehSimpan || $formLama)>
    <legend class="pengajar-sr-only">Data penugasan dosen</legend>
    @if ($mengubah)
        <p><strong>Dosen:</strong> {{ $penugasan->dosen->kode_dosen }} — {{ $penugasan->dosen->user->nama }}</p>
        <p class="help">Identitas dosen dan kelas pada penugasan ini tetap.</p>
    @else
        <div class="field">
            <label for="dosen_id">Dosen</label>
            <select name="dosen_id" id="dosen_id" required aria-describedby="dosen-help"
                aria-invalid="{{ $errors->has('dosen_id') ? 'true' : 'false' }}">
                <option value="">Pilih dosen</option>
                @foreach ($dosenPilihan as $dosen)
                    <option value="{{ $dosen->id }}" @selected($dosenLama === (string) $dosen->id)>
                        {{ $dosen->kode_dosen }} — {{ $dosen->user->nama }}{{ $dosen->gelar ? ', ' . $dosen->gelar : '' }}
                    </option>
                @endforeach
            </select>
            <p class="help" id="dosen-help">Dosen aktif dengan akun dan role yang sesuai, serta belum memiliki
                penugasan pada kelas ini.</p>
            @error('dosen_id')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="peran">Peran dalam kelas</label>
            <select name="peran" id="peran" required aria-describedby="peran-help">
                @foreach (\App\Models\PengajarKelas::PERAN as $kode => $label)
                    <option value="{{ $kode }}" @selected($peranLama === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('peran')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        @if ($mengubah)
            <div class="field">
                <label for="aktif">Status penugasan</label>
                <select name="aktif" id="aktif" required>
                    <option value="1" @selected($aktifLama)>Aktif</option>
                    <option value="0" @selected(!$aktifLama)>Nonaktif</option>
                </select>
                @error('aktif')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>

    <div class="pengajar-note" id="peran-help">
        @if ($koordinatorForm)
            <p>Koordinator saat ini: <strong>{{ $koordinatorForm->dosen->user->nama }}</strong>.</p>
        @else
            <p>Kelas ini belum memiliki koordinator aktif.</p>
        @endif
        <p>Menetapkan dosen lain sebagai koordinator aktif mengalihkan koordinator lama menjadi pengajar. Penugasannya
            tetap aktif sampai dinonaktifkan.</p>
        @if ($mengubah)
            <p>Saat menonaktifkan penugasan, pertahankan pilihan peran sebelumnya. Untuk koordinator pada kelas aktif,
                tetapkan pengganti melalui penugasan dosen lain terlebih dahulu.</p>
        @else
            <p>Penugasan baru disimpan dengan status aktif.</p>
        @endif
    </div>

    <div class="field">
        <label for="alasan">Alasan penugasan/perubahan</label>
        <textarea id="alasan" name="alasan" rows="4" minlength="10" maxlength="2000" required
            aria-describedby="alasan-help" aria-invalid="{{ $errors->has('alasan') ? 'true' : 'false' }}">{{ $alasanLama }}</textarea>
        <p class="help" id="alasan-help">Wajib 10–2.000 karakter. Alasan disimpan pada riwayat perubahan.</p>
        @error('alasan')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <label class="pengajar-konfirmasi" for="konfirmasi-penugasan">
        <input type="checkbox" id="konfirmasi-penugasan" name="konfirmasi" value="1" required>
        <span>Saya telah memeriksa dosen, kelas, peran, dan dampak perubahan pada koordinator saat ini.</span>
    </label>
    @error('konfirmasi')
        <p class="field-error">{{ $message }}</p>
    @enderror

    <button class="button" type="submit">{{ $mengubah ? 'Simpan perubahan penugasan' : 'Tambahkan dosen' }}</button>
</fieldset>
