@php
    $editing = $rombel->exists;

    $value = static function (string $key, mixed $default = ''): string {
        $input = old($key, $default);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version ?? '') }}">

    @error('version')
        <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
            {{ $message }}
            <a href="{{ route('admin.rombel.edit', $rombel) }}" class="font-semibold underline ml-1 hover:text-rose-900">
                Muat ulang formulir
            </a>
        </div>
    @enderror

    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 text-sm">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Periode Akademik</span>
            <span class="mt-1 font-semibold text-slate-800 block">{{ $rombel->periodeAkademik->kode }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Program Studi</span>
            <span
                class="mt-1 font-semibold text-slate-800 block">{{ $rombel->paketSemester->kurikulum->programStudi->nama }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kurikulum</span>
            <span
                class="mt-1 font-mono font-semibold text-slate-800 block">{{ $rombel->paketSemester->kurikulum->kode }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Paket Semester</span>
            <span class="mt-1 font-semibold text-slate-800 block">{{ $rombel->paketSemester->nama }} —
                V{{ $rombel->paketSemester->versi }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Semester Studi</span>
            <span class="mt-1 font-semibold text-slate-800 block">{{ $rombel->paketSemester->semester_studi }}</span>
        </div>
    </div>

    <p class="text-xs text-slate-500 mb-6">
        Periode dan paket dikunci. Untuk penempatan pada periode atau paket berbeda, buat rombel baru.
    </p>
@else
    <div class="space-y-6 mb-6">
        <div>
            <label for="periode_akademik_id"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode Akademik <span
                    class="text-rose-500">*</span></label>
            <select id="periode_akademik_id" name="periode_akademik_id" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('periode_akademik_id') border-rose-500 bg-rose-50/50 @enderror"
                aria-invalid="{{ $errors->has('periode_akademik_id') ? 'true' : 'false' }}"
                aria-describedby="periode-error">
                <option value="">Pilih periode akademik</option>

                @foreach ($daftarPeriode as $periode)
                    <option value="{{ $periode->id }}" @selected($value('periode_akademik_id') === (string) $periode->id)>
                        {{ $periode->kode }}
                        — {{ $statusPeriodeOptions[$periode->status] }}
                    </option>
                @endforeach
            </select>

            @error('periode_akademik_id')
                <p id="periode-error" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="paket_semester_id"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Paket Semester <span
                    class="text-rose-500">*</span></label>
            <select id="paket_semester_id" name="paket_semester_id" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('paket_semester_id') border-rose-500 bg-rose-50/50 @enderror"
                aria-invalid="{{ $errors->has('paket_semester_id') ? 'true' : 'false' }}"
                aria-describedby="paket-help paket-error">
                <option value="">Pilih paket semester</option>

                @foreach ($daftarPaket as $paket)
                    <option value="{{ $paket->id }}" @selected($value('paket_semester_id') === (string) $paket->id)>
                        {{ $paket->kurikulum->programStudi->nama }}
                        — {{ $paket->kurikulum->kode }}
                        — {{ $paket->nama }}
                        — Semester {{ $paket->semester_studi }}
                        — V{{ $paket->versi }}
                    </option>
                @endforeach
            </select>

            <p id="paket-help" class="mt-1 text-xs text-slate-500">
                Menampilkan paket terbit dengan kurikulum, program studi, dan mata kuliah aktif.
            </p>

            @error('paket_semester_id')
                <p id="paket-error" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="kode" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kode Rombel
            <span class="text-rose-500">*</span></label>
        <input id="kode" name="kode" type="text" maxlength="40" required placeholder="Contoh: SI-1A"
            value="{{ $value('kode', $rombel->kode) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kode') border-rose-500 bg-rose-50/50 @enderror"
            aria-invalid="{{ $errors->has('kode') ? 'true' : 'false' }}" aria-describedby="kode-help kode-error">

        <p id="kode-help" class="mt-1 text-xs text-slate-500">
            Harus unik dalam periode akademik yang sama.
        </p>

        @error('kode')
            <p id="kode-error" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kapasitas" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kapasitas
            Mahasiswa <span class="text-slate-400 font-normal">— opsional</span></label>
        <input id="kapasitas" name="kapasitas" type="number" min="1" max="32767" step="1"
            placeholder="Contoh: 30" value="{{ $value('kapasitas', $rombel->kapasitas) }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kapasitas') border-rose-500 bg-rose-50/50 @enderror"
            aria-invalid="{{ $errors->has('kapasitas') ? 'true' : 'false' }}"
            aria-describedby="kapasitas-help kapasitas-error">

        <p id="kapasitas-help" class="mt-1 text-xs text-slate-500">
            Kosongkan jika tidak menetapkan batas kapasitas.
        </p>

        @error('kapasitas')
            <p id="kapasitas-error" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
