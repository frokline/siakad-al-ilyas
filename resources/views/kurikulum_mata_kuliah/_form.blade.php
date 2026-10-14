@php
    // Nilai default dipakai ketika controller halaman tambah belum mengirim $detail.
    $detail =
        $detail ??
        (object) [
            'exists' => false,
            'sks' => '',
            'semester_rekomendasi' => '',
            'sifat' => array_key_first($sifatOptions ?? []) ?? '',
            'mataKuliah' => null,
        ];

    $isEditing = (bool) ($detail->exists ?? false);

    $value = static function (string $field, mixed $fallback = ''): string {
        $input = old($field, $fallback);

        return is_scalar($input) ? (string) $input : '';
    };

    $inputClass =
        'block w-full min-w-0 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helpClass = 'text-xs leading-5 text-slate-500';
    $errorClass = 'mt-1 text-xs font-medium text-rose-600';
@endphp

<input type="hidden" name="version" value="{{ $value('version', $version ?? '') }}">

@if ($errors->has('version'))
    <div class="mb-5 flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 p-3.5 text-sm text-rose-800"
        role="alert">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v3m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 00-3.4 0z" />
        </svg>
        <div>
            <p class="font-semibold">Data kurikulum perlu diperiksa.</p>
            <p class="mt-1">{{ $errors->first('version') }}</p>
            <a class="mt-2 inline-block font-semibold underline underline-offset-2 hover:text-rose-950"
                href="{{ $isEditing
                    ? route('admin.kurikulum.mata-kuliah.edit', [$kurikulum, $detail])
                    : route('admin.kurikulum.mata-kuliah.create', $kurikulum) }}">
                Muat ulang formulir dari data terbaru
            </a>
        </div>
    </div>
@endif

@if ($errors->any() && !$errors->has('version'))
    <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 p-3.5 text-sm text-rose-800" role="alert">
        <p class="font-semibold">Periksa kembali formulir Anda.</p>
        <p class="mt-1">Beberapa kolom belum sesuai. Pesan kesalahan ditampilkan tepat di bawah kolom terkait.</p>
    </div>
@endif

<div class="space-y-6">
    @if ($isEditing)
        <div class="flex min-w-0 flex-col gap-2">
            <span class="{{ $labelClass }}">Mata kuliah</span>

            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3.5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                    {{ $detail->mataKuliah->kode }}
                </p>
                <p class="mt-1 text-sm font-semibold leading-6 text-slate-900">
                    {{ $detail->mataKuliah->nama }}
                </p>
            </div>

            @if (!$detail->mataKuliah->aktif)
                <p class="{{ $helpClass }}">Mata kuliah ini sedang nonaktif pada katalog. Data penempatannya tetap
                    ditampilkan.</p>
            @endif
        </div>
    @else
        <div class="flex min-w-0 flex-col gap-2">
            <label for="mata_kuliah_id" class="{{ $labelClass }}">
                Mata kuliah <span class="text-rose-600" aria-hidden="true">*</span>
            </label>

            <select id="mata_kuliah_id" name="mata_kuliah_id" required
                aria-invalid="{{ $errors->has('mata_kuliah_id') ? 'true' : 'false' }}"
                class="{{ $inputClass }} {{ $errors->has('mata_kuliah_id') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-100' : '' }}">
                <option value="">Pilih mata kuliah</option>

                @foreach ($daftarMataKuliah as $mataKuliah)
                    <option value="{{ $mataKuliah->id }}" @selected($value('mata_kuliah_id') === (string) $mataKuliah->id)>
                        {{ $mataKuliah->kode }} — {{ $mataKuliah->nama }}
                    </option>
                @endforeach
            </select>

            <p class="{{ $helpClass }}">Hanya mata kuliah aktif dari program studi yang sama dan belum tercantum
                dalam kurikulum yang ditampilkan.</p>

            @error('mata_kuliah_id')
                <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6">
        <div class="flex min-w-0 flex-col gap-2">
            <label for="sks" class="{{ $labelClass }}">
                Jumlah SKS <span class="text-rose-600" aria-hidden="true">*</span>
            </label>

            <input id="sks" name="sks" type="text" inputmode="decimal" maxlength="5"
                value="{{ $value('sks', $detail->sks ?? '') }}" placeholder="Contoh: 3 atau 2,5" required
                aria-invalid="{{ $errors->has('sks') ? 'true' : 'false' }}"
                class="{{ $inputClass }} {{ $errors->has('sks') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-100' : '' }}">

            <p class="{{ $helpClass }}">Masukkan jumlah kredit mata kuliah.</p>

            @error('sks')
                <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex min-w-0 flex-col gap-2">
            <label for="semester_rekomendasi" class="{{ $labelClass }}">
                Semester rekomendasi <span class="text-rose-600" aria-hidden="true">*</span>
            </label>

            <input id="semester_rekomendasi" name="semester_rekomendasi" type="number"
                value="{{ $value('semester_rekomendasi', $detail->semester_rekomendasi ?? '') }}" min="1"
                max="32767" step="1" placeholder="Contoh: 1" required
                aria-invalid="{{ $errors->has('semester_rekomendasi') ? 'true' : 'false' }}"
                class="{{ $inputClass }} {{ $errors->has('semester_rekomendasi') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-100' : '' }}">

            <p class="{{ $helpClass }}">Semester studi yang direkomendasikan untuk mengambil mata kuliah ini.</p>

            @error('semester_rekomendasi')
                <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex min-w-0 flex-col gap-2">
        <label for="sifat" class="{{ $labelClass }}">
            Sifat mata kuliah <span class="text-rose-600" aria-hidden="true">*</span>
        </label>

        <select id="sifat" name="sifat" required aria-invalid="{{ $errors->has('sifat') ? 'true' : 'false' }}"
            class="{{ $inputClass }} {{ $errors->has('sifat') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-100' : '' }}">
            @foreach ($sifatOptions as $kodeSifat => $labelSifat)
                <option value="{{ $kodeSifat }}" @selected($value('sifat', $detail->sifat ?? '') === (string) $kodeSifat)>
                    {{ $labelSifat }}
                </option>
            @endforeach
        </select>

        <p class="{{ $helpClass }}">Label Pilihan merupakan penanda kurikulum. KRS versi pertama tetap mengikuti
            paket semester.</p>

        @error('sifat')
            <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>
