@php
    $editing = $kurikulum->exists;
    $editable = $kurikulum->identitasDapatDiubah();

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
            <a href="{{ route('admin.kurikulum.edit', $kurikulum) }}"
                class="font-semibold underline ml-1 hover:text-rose-800">
                Muat ulang formulir dari data terbaru
            </a>
        </div>
    @enderror
@endif

<div class="space-y-6">
    @if ($editable)
        <div>
            <label for="program_studi_id"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Program Studi <span
                    class="text-rose-500">*</span></label>
            <select id="program_studi_id" @disabled($prodiTerkunci ?? false) name="program_studi_id" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('program_studi_id') border-rose-500 bg-rose-50/50 @enderror">
                <option value="">Pilih Program Studi</option>

                @foreach ($daftarProdi as $prodi)
                    <option value="{{ $prodi->id }}" @selected($value('program_studi_id', $kurikulum->program_studi_id) === (string) $prodi->id)>
                        {{ $prodi->kode }} — {{ $prodi->nama }}
                        {{ $prodi->aktif ? '' : '(Nonaktif)' }}
                    </option>
                @endforeach
            </select>

            @if ($prodiTerkunci ?? false)
                <input type="hidden" name="program_studi_id" value="{{ $kurikulum->program_studi_id }}">
                <p class="mt-1 text-xs text-amber-600 font-medium">Program studi terkunci karena kurikulum sudah
                    memiliki mata kuliah.</p>
            @endif

            @error('program_studi_id')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="kode" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kode
                    Kurikulum <span class="text-rose-500">*</span></label>
                <input id="kode" name="kode" type="text" value="{{ $value('kode', $kurikulum->kode) }}"
                    maxlength="40" placeholder="Contoh: KUR-2026" autocomplete="off" spellcheck="false" required
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kode') border-rose-500 bg-rose-50/50 @enderror">
                <p class="mt-1 text-xs text-slate-500">Kode harus unik dalam program studi yang sama.</p>

                @error('kode')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tahun_berlaku"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Tahun Berlaku <span
                        class="text-rose-500">*</span></label>
                <input id="tahun_berlaku" name="tahun_berlaku" type="number"
                    value="{{ $value('tahun_berlaku', $kurikulum->tahun_berlaku) }}" min="1900" max="9999"
                    step="1" inputmode="numeric" placeholder="Contoh: 2026" required
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('tahun_berlaku') border-rose-500 bg-rose-50/50 @enderror">

                @error('tahun_berlaku')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="nama" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Nama
                Kurikulum <span class="text-rose-500">*</span></label>
            <input id="nama" name="nama" type="text" value="{{ $value('nama', $kurikulum->nama) }}"
                maxlength="150" placeholder="Contoh: Kurikulum Ilmu Syariah 2026" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nama') border-rose-500 bg-rose-50/50 @enderror">

            @error('nama')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    @else
        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 mb-6">
            Identitas kurikulum sudah terkunci. Buat kurikulum versi baru apabila terdapat perubahan identitas.
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Program Studi</span>
                <span class="mt-1 font-semibold text-slate-800 block">{{ $kurikulum->programStudi->kode }} —
                    {{ $kurikulum->programStudi->nama }}</span>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kode Kurikulum</span>
                <span class="mt-1 font-mono font-bold text-slate-800 block">{{ $kurikulum->kode }}</span>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Nama Kurikulum</span>
                <span class="mt-1 font-semibold text-slate-800 block">{{ $kurikulum->nama }}</span>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tahun Berlaku</span>
                <span class="mt-1 font-semibold text-slate-800 block">{{ $kurikulum->tahun_berlaku }}</span>
            </div>
        </div>
    @endif

    <div class="border-t border-slate-200 pt-6">
        <label for="status" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status
            Kurikulum <span class="text-rose-500">*</span></label>
        <select id="status" name="status" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('status') border-rose-500 bg-rose-50/50 @enderror">
            @foreach ($statusOptions as $kodeStatus => $labelStatus)
                <option value="{{ $kodeStatus }}" @selected($value('status', $kurikulum->status) === $kodeStatus)>
                    {{ $labelStatus }}
                </option>
            @endforeach
        </select>

        @if ($editable)
            <p class="mt-1.5 text-xs text-slate-500">Memilih Aktif atau Arsip akan mengunci identitas kurikulum. Status
                tidak dapat dikembalikan menjadi Draf.</p>
        @else
            <p class="mt-1.5 text-xs text-slate-500">Kurikulum arsip dapat diaktifkan kembali jika program studi aktif.
                Identitasnya tetap terkunci.</p>
        @endif

        @error('status')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
