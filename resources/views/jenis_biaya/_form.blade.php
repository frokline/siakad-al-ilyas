@csrf
@php
    $input =
        'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
    $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
    $nilaiNama = old('nama', $jenisBiaya->nama);
    $nilaiKeterangan = old('keterangan', $jenisBiaya->keterangan);
@endphp

<div class="space-y-6" x-data="{ nama: @js((string) $nilaiNama), keterangan: @js((string) ($nilaiKeterangan ?? '')) }">
    <fieldset class="space-y-5">
        <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">1. Identitas jenis biaya
        </legend>

        @if ($baru)
            <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
            @error('form_token')
                <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
            @enderror
            <div>
                <label for="kode" class="{{ $label }}">Kode <span class="text-rose-500">*</span></label>
                <input id="kode" name="kode" maxlength="30" minlength="2" required autocomplete="off"
                    placeholder="SPP" value="{{ old('kode') }}" aria-describedby="kode-hint"
                    class="{{ $input }} font-mono uppercase">
                @error('kode')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <p id="kode-hint" class="mt-1.5 text-xs text-slate-500">2&ndash;30 karakter; diawali huruf, memakai
                    huruf, angka, - atau _. Huruf kecil otomatis menjadi besar. <strong>Kode tetap setelah
                        dibuat.</strong></p>
            </div>
        @else
            @method('PATCH')
            <input type="hidden" name="versi" value="{{ old('versi', $jenisBiaya->versiForm()) }}">
            @error('versi')
                <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
            @enderror
            <div>
                <label for="kode" class="{{ $label }}">Kode tetap</label>
                <div class="relative">
                    <input id="kode" value="{{ $jenisBiaya->kode }}" readonly
                        class="{{ $input }} cursor-not-allowed bg-slate-100 pr-11 font-mono text-slate-500">
                    <svg class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
        @endif

        <div>
            <div class="flex items-baseline justify-between">
                <label for="nama" class="{{ $label }}">Nama biaya <span
                        class="text-rose-500">*</span></label>
                <span class="text-xs text-slate-400" x-text="nama.length + ' / 100'"></span>
            </div>
            <input id="nama" name="nama" minlength="3" maxlength="100" required x-model="nama"
                placeholder="SPP Semester" value="{{ $nilaiNama }}" class="{{ $input }}">
            @error('nama')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </fieldset>

    <fieldset class="border-t border-slate-100 pt-6">
        <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-siakad-active">2. Keterangan</legend>
        <div class="flex items-baseline justify-between">
            <label for="keterangan" class="{{ $label }}">Keterangan <span
                    class="font-normal text-slate-400">(opsional)</span></label>
            <span class="text-xs text-slate-400" x-text="keterangan.length + ' / 1000'"></span>
        </div>
        <textarea id="keterangan" name="keterangan" rows="5" maxlength="1000" x-model="keterangan" class="{{ $input }}"
            placeholder="Tuliskan keterangan tagihan memakai jenis biaya ini">{{ $nilaiKeterangan }}</textarea>
        @error('keterangan')
            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </fieldset>

    @unless ($baru)
        <fieldset class="border-t border-slate-100 pt-6">
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-siakad-active">3. Alasan perubahan
            </legend>
            <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                <label for="alasan" class="{{ $label }}">Alasan perubahan <span
                        class="text-rose-500">*</span></label>
                <textarea id="alasan" name="alasan" minlength="10" maxlength="1000" rows="3" required
                    class="{{ $input }}" placeholder="Minimal 10 karakter">{{ old('alasan') }}</textarea>
                @error('alasan')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </fieldset>
    @endunless

    <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
        <button type="submit"
            class="rounded-xl bg-siakad-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/40">{{ $baru ? 'Simpan jenis biaya' : 'Simpan perubahan' }}</button>
        <a href="{{ $baru ? route('keuangan.jenis-biaya.index') : route('keuangan.jenis-biaya.show', $jenisBiaya) }}"
            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Batal</a>
    </div>
</div>
