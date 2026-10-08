@csrf
@php
    $input =
        'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
    $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
    $namaBulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
@endphp

<div class="space-y-6">
    {{-- Bagian 1: identitas tagihan (hanya saat membuat) --}}
    @if ($baru)
        <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
        <input type="hidden" name="registrasi_semester_id" value="{{ $pilihan->id }}">
        @error('form_token')
            <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
        @enderror
        @error('registrasi_semester_id')
            <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
        @enderror

        <fieldset class="space-y-5">
            <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">1. Jenis dan bulan
                kewajiban</legend>

            <div>
                <label for="jenis_biaya_id" class="{{ $label }}">Jenis biaya <span
                        class="text-rose-500">*</span></label>
                <select id="jenis_biaya_id" name="jenis_biaya_id" required class="{{ $input }}">
                    <option value="">Pilih jenis biaya</option>
                    @foreach ($jenis as $j)
                        <option value="{{ $j->id }}" @selected((string) old('jenis_biaya_id') === (string) $j->id)>{{ $j->kode }} &mdash;
                            {{ $j->nama }}</option>
                    @endforeach
                </select>
                @error('jenis_biaya_id')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="bulan_tagihan" class="{{ $label }}">Bulan kewajiban <span
                            class="text-rose-500">*</span></label>
                    <select id="bulan_tagihan" name="bulan_tagihan" required class="{{ $input }}">
                        <option value="">Pilih bulan</option>
                        @foreach ($namaBulan as $no => $nama)
                            <option value="{{ $no }}" @selected((string) old('bulan_tagihan') === (string) $no)>{{ $nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('bulan_tagihan')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="tahun_tagihan" class="{{ $label }}">Tahun kewajiban <span
                            class="text-rose-500">*</span></label>
                    <input id="tahun_tagihan" type="number" name="tahun_tagihan" min="2000" max="2199" required
                        class="{{ $input }}" value="{{ old('tahun_tagihan', now('Asia/Makassar')->year) }}">
                    @error('tahun_tagihan')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </fieldset>
    @else
        @method('PATCH')
        <input type="hidden" name="versi" value="{{ old('versi', $tagihan->versiForm()) }}">
        @error('versi')
            <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
        @enderror
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <strong>{{ $tagihan->nomor }}</strong> &middot; Bulan
            {{ sprintf('%02d/%04d', $tagihan->bulan_tagihan, $tagihan->tahun_tagihan) }}.
            Identitas tagihan tidak dapat diganti.
        </div>
    @endif

    {{-- Bagian 2: nominal dan jatuh tempo --}}
    <fieldset class="space-y-5 {{ $baru ? 'border-t border-slate-100 pt-6' : '' }}">
        <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">{{ $baru ? '2' : '1' }}.
            Nominal dan jatuh tempo</legend>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="nominal" class="{{ $label }}">Nominal <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-500">Rp</span>
                    <input id="nominal" name="nominal" inputmode="decimal" maxlength="15" required
                        class="{{ $input }} pl-11" value="{{ old('nominal', $tagihan->nominal) }}"
                        placeholder="250000.00">
                </div>
                @error('nominal')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-500">Tanpa titik pemisah ribuan atau koma. Contoh: 250000 atau
                    250000.50.</p>
            </div>
            <div>
                <label for="jatuh_tempo" class="{{ $label }}">Jatuh tempo <span
                        class="text-rose-500">*</span></label>
                <input id="jatuh_tempo" type="date" name="jatuh_tempo" min="2000-01-01" max="2199-12-31" required
                    class="{{ $input }}"
                    value="{{ old('jatuh_tempo', $tagihan->jatuh_tempo?->format('Y-m-d')) }}">
                @error('jatuh_tempo')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="catatan" class="{{ $label }}">Catatan pada tagihan <span
                    class="font-normal text-slate-400">(opsional, terlihat mahasiswa setelah terbit)</span></label>
            <textarea id="catatan" name="catatan" rows="3" maxlength="1000" class="{{ $input }}"
                placeholder="Contoh: SPP bulan Oktober, termasuk biaya praktikum">{{ old('catatan', $tagihan->catatan) }}</textarea>
            @error('catatan')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </fieldset>

    {{-- Bagian 3: alasan --}}
    <fieldset class="border-t border-slate-100 pt-6">
        <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-siakad-active">{{ $baru ? '3' : '2' }}.
            Alasan (internal)</legend>
        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
            <label for="alasan" class="{{ $label }}">Alasan pencatatan/perubahan <span
                    class="text-rose-500">*</span></label>
            <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="1000" required
                class="{{ $input }}" placeholder="Tulis alasan, minimal 10 karakter">{{ old('alasan') }}</textarea>
            @error('alasan')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-600">Hanya terlihat oleh petugas, tercatat pada audit.</p>
        </div>
    </fieldset>

    <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
        <button type="submit"
            class="rounded-xl bg-siakad-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/40">Simpan
            draf</button>
        <a href="{{ $baru ? route('tagihan.create') : route('tagihan.show', $tagihan) }}"
            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Batal</a>
    </div>
</div>
