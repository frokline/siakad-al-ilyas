@php
    $mengubah = isset($krs) && $krs->exists;
    $catatanLama = old('catatan', $mengubah ? $krs->catatan : '');
    $catatanLama = is_string($catatanLama) ? $catatanLama : '';
    $formLama = $mengubah && $errors->has('versi_form');
@endphp

@csrf
<input type="hidden" name="_form" value="{{ $mengubah ? 'update' : 'store' }}">

@if ($mengubah)
    @method('PATCH')
    <input type="hidden" name="versi_form" value="{{ $versiForm }}">
@else
    <input type="hidden" name="registrasi_semester_id" value="{{ $registrasi->id }}">
@endif

@if ($formLama)
    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 flex items-center justify-between"
        role="alert">
        <span>Data berubah sejak formulir dibuka.</span>
        <a href="{{ route('admin.krs.edit', $krs) }}" class="font-semibold underline hover:text-rose-900">Muat formulir
            terbaru</a>
        <span>sebelum menyimpan kembali.</span>
    </div>
@endif

<fieldset @disabled(!$bolehSimpan || $formLama)>
    <div class="mb-6">
        <label for="catatan" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Catatan
            KRS</label>
        <textarea id="catatan" name="catatan" rows="4" maxlength="2000" aria-describedby="catatan-help"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('catatan') border-rose-500 bg-rose-50/50 @enderror"
            aria-invalid="{{ $errors->has('catatan') ? 'true' : 'false' }}">{{ $catatanLama }}</textarea>
        <p id="catatan-help" class="mt-1 text-xs text-slate-500">Opsional, maksimal 2.000 karakter. Mata kuliah secara
            otomatis mengikuti paket semester yang sudah ditetapkan.</p>
        @error('catatan')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-6">
        <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi-draf">
            <input type="checkbox" id="konfirmasi-draf" name="konfirmasi" value="1" required
                class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark">
            <span class="text-sm font-medium text-slate-700">Saya sudah memeriksa identitas mahasiswa, periode, rombel,
                dan seluruh mata kuliah paket.</span>
        </label>
        @error('konfirmasi')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
        <a class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
            href="{{ $mengubah ? route('admin.krs.show', $krs) : route('admin.krs.index') }}">Kembali</a>
        <button
            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2"
            type="submit">
            {{ $mengubah ? 'Simpan catatan' : 'Buat draf KRS seluruh paket' }}
        </button>
    </div>
</fieldset>
