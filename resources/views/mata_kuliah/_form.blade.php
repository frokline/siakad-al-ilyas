@php
    $editing = $mataKuliah->exists;

    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version ?? '') }}">

    @error('version')
        <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
            {{ $message }}
            <a href="{{ route('admin.mata-kuliah.edit', $mataKuliah) }}"
                class="font-semibold underline ml-1 hover:text-rose-800">
                Muat ulang formulir dari data terbaru
            </a>
        </div>
    @enderror

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
            <a href="{{ route('admin.mata-kuliah.edit', $mataKuliah) }}"
                class="font-semibold underline hover:text-amber-900">
                Muat ulang formulir dari data terbaru
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Program Studi</span>
            <span class="mt-1 font-semibold text-slate-800 block">{{ $mataKuliah->programStudi->kode }} —
                {{ $mataKuliah->programStudi->nama }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kode Mata Kuliah</span>
            <span class="mt-1 font-mono font-bold text-slate-800 block">{{ $mataKuliah->kode }}</span>
        </div>
    </div>

    <p class="text-xs text-slate-500 mb-6">Kode dan program studi ditetapkan saat pembuatan.</p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="program_studi_id"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Program Studi <span
                    class="text-rose-500">*</span></label>
            <select id="program_studi_id" name="program_studi_id" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('program_studi_id') border-rose-500 bg-rose-50/50 @enderror">
                <option value="">Pilih Program Studi</option>

                @foreach ($daftarProdi as $prodi)
                    <option value="{{ $prodi->id }}" @selected($value('program_studi_id') === (string) $prodi->id)>
                        {{ $prodi->kode }} — {{ $prodi->nama }}
                    </option>
                @endforeach
            </select>

            @error('program_studi_id')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="kode" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kode Mata
                Kuliah <span class="text-rose-500">*</span></label>
            <input id="kode" name="kode" type="text" value="{{ $value('kode') }}" maxlength="40"
                placeholder="Contoh: SYR-101" autocomplete="off" spellcheck="false" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kode') border-rose-500 bg-rose-50/50 @enderror">
            <p class="mt-1 text-xs text-slate-500">Kode harus unik dalam program studi yang sama. Pastikan kode benar
                sebelum menyimpan.</p>

            @error('kode')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
@endif

<div class="space-y-6">
    <div>
        <label for="nama" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Nama Mata
            Kuliah <span class="text-rose-500">*</span></label>
        <input id="nama" name="nama" type="text" value="{{ $value('nama', $mataKuliah->nama) }}"
            maxlength="150" placeholder="Contoh: Pengantar Ilmu Syariah" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nama') border-rose-500 bg-rose-50/50 @enderror">

        @error('nama')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="aktif" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status Mata
            Kuliah <span class="text-rose-500">*</span></label>
        <select id="aktif" name="aktif" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('aktif') border-rose-500 bg-rose-50/50 @enderror">
            @foreach ($statusOptions as $kodeStatus => $labelStatus)
                <option value="{{ $kodeStatus }}" @selected($value('aktif', (int) $mataKuliah->aktif) === (string) $kodeStatus)>
                    {{ $labelStatus }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Gunakan Nonaktif jika mata kuliah tidak lagi digunakan.</p>

        @error('aktif')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
