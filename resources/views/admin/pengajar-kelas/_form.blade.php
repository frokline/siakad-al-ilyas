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
    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 flex items-center justify-between"
        role="alert">
        <span>Kelas atau tim berubah sejak formulir dibuka.</span>
        <a href="{{ $mengubah ? route('admin.pengajar-kelas.edit', $penugasan) : route('admin.pengajar-kelas.create', ['kelas_id' => $kelas->id]) }}"
            class="font-semibold underline hover:text-rose-900">Muat formulir terbaru</a>
        <span>dan periksa kembali tim sebelum menyimpan.</span>
    </div>
@endif

<fieldset @disabled(!$bolehSimpan || $formLama) class="space-y-6">
    <legend class="sr-only">Data penugasan dosen</legend>
    @if ($mengubah)
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Dosen</span>
            <strong class="font-bold text-slate-800 block mt-1"><span
                    class="font-mono">{{ $penugasan->dosen->kode_dosen }}</span> —
                {{ $penugasan->dosen->user->nama }}</strong>
            <p class="text-xs text-slate-500 mt-1">Identitas dosen dan kelas pada penugasan ini tetap.</p>
        </div>
    @else
        <div>
            <label for="dosen_id" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Dosen
                <span class="text-rose-500">*</span></label>
            <select name="dosen_id" id="dosen_id" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('dosen_id') border-rose-500 bg-rose-50/50 @enderror"
                aria-describedby="dosen-help" aria-invalid="{{ $errors->has('dosen_id') ? 'true' : 'false' }}">
                <option value="">Pilih dosen</option>
                @foreach ($dosenPilihan as $dosen)
                    <option value="{{ $dosen->id }}" @selected($dosenLama === (string) $dosen->id)>
                        {{ $dosen->kode_dosen }} —
                        {{ $dosen->user->nama }}{{ $dosen->gelar ? ', ' . $dosen->gelar : '' }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500" id="dosen-help">Dosen aktif dengan akun dan role yang sesuai, serta
                belum memiliki penugasan pada kelas ini.</p>
            @error('dosen_id')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="peran" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Peran
                dalam Kelas <span class="text-rose-500">*</span></label>
            <select name="peran" id="peran" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white"
                aria-describedby="peran-help">
                @foreach (\App\Models\PengajarKelas::PERAN as $kode => $label)
                    <option value="{{ $kode }}" @selected($peranLama === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            @error('peran')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($mengubah)
            <div>
                <label for="aktif"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status Penugasan <span
                        class="text-rose-500">*</span></label>
                <select name="aktif" id="aktif" required
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="1" @selected($aktifLama)>Aktif</option>
                    <option value="0" @selected(!$aktifLama)>Nonaktif</option>
                </select>
                @error('aktif')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>

    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-xs text-slate-600 space-y-1.5" id="peran-help">
        @if ($koordinatorForm)
            <p>Koordinator saat ini: <strong class="text-slate-800">{{ $koordinatorForm->dosen->user->nama }}</strong>.
            </p>
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

    <div>
        <label for="alasan" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Alasan
            Penugasan/Perubahan <span class="text-rose-500">*</span></label>
        <textarea id="alasan" name="alasan" rows="4" minlength="10" maxlength="2000" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('alasan') border-rose-500 bg-rose-50/50 @enderror"
            aria-describedby="alasan-help" aria-invalid="{{ $errors->has('alasan') ? 'true' : 'false' }}">{{ $alasanLama }}</textarea>
        <p class="mt-1 text-xs text-slate-500" id="alasan-help">Wajib 10–2.000 karakter. Alasan disimpan pada riwayat
            perubahan.</p>
        @error('alasan')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi-penugasan">
            <input class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark" type="checkbox"
                id="konfirmasi-penugasan" name="konfirmasi" value="1" required>
            <span class="text-sm font-medium text-slate-700">Saya telah memeriksa dosen, kelas, peran, dan dampak
                perubahan pada koordinator saat ini.</span>
        </label>
        @error('konfirmasi')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="pt-5 border-t border-slate-200 flex justify-end">
        <button
            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2"
            type="submit">{{ $mengubah ? 'Simpan perubahan penugasan' : 'Tambahkan dosen' }}</button>
    </div>
</fieldset>
