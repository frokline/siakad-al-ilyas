@php
    $editing = $programStudi->exists;

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
            <a href="{{ route('admin.program-studi.edit', $programStudi) }}"
                class="font-semibold underline ml-1 hover:text-rose-800">
                Muat ulang data terbaru
            </a>
        </div>
    @enderror
@endif

<div class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="kode" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Kode
                Program Studi</label>
            <input type="text" id="kode" name="kode" value="{{ $value('kode', $programStudi->kode) }}"
                maxlength="30" autocomplete="off" autocapitalize="characters" spellcheck="false" required
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kode') border-rose-300 bg-rose-50/50 @enderror"
                @error('kode')
                    aria-invalid="true"
                    aria-describedby="kode-error"
                @enderror>

            @error('kode')
                <p id="kode-error" class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="jenjang" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Jenjang
                / Program</label>
            <input type="text" id="jenjang" name="jenjang" value="{{ $value('jenjang', $programStudi->jenjang) }}"
                maxlength="30" required
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jenjang') border-rose-300 bg-rose-50/50 @enderror"
                @error('jenjang')
                    aria-invalid="true"
                    aria-describedby="jenjang-error"
                @enderror>

            @error('jenjang')
                <p id="jenjang-error" class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="nama" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Nama
            Program Studi</label>
        <input type="text" id="nama" name="nama" value="{{ $value('nama', $programStudi->nama) }}"
            maxlength="150" required
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nama') border-rose-300 bg-rose-50/50 @enderror"
            @error('nama')
                aria-invalid="true"
                aria-describedby="nama-error"
            @enderror>

        @error('nama')
            <p id="nama-error" class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="aktif" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Status
            Penggunaan</label>
        <select id="aktif" name="aktif" required
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('aktif') border-rose-300 bg-rose-50/50 @enderror"
            @error('aktif')
                aria-invalid="true"
                aria-describedby="aktif-error"
            @enderror>
            <option value="1" @selected($value('aktif', (int) $programStudi->aktif) === '1')>
                Aktif
            </option>

            <option value="0" @selected($value('aktif', (int) $programStudi->aktif) === '0')>
                Nonaktif
            </option>
        </select>

        @error('aktif')
            <p id="aktif-error" class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
