@php
    $editing = $paketSemester->exists;

    $value = static function (string $key, mixed $default = ''): string {
        $input = old($key, $default);

        return is_scalar($input) ? (string) $input : '';
    };
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version) }}">

    @error('version')
        <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
            {{ $message }}
            <a href="{{ route('admin.paket-semester.edit', $paketSemester) }}"
                class="font-semibold underline ml-1 hover:text-rose-900">
                Muat ulang formulir
            </a>
        </div>
    @enderror

    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Program Studi</span>
            <span
                class="mt-1 font-semibold text-slate-800 block">{{ $paketSemester->kurikulum?->programStudi?->nama ?? 'Program Studi tidak ditemukan' }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kurikulum</span>
            <span class="mt-1 font-semibold text-slate-800 block">{{ $paketSemester->kurikulum?->kode ?? '-' }} —
                {{ $paketSemester->kurikulum?->nama ?? 'Kurikulum tidak ditemukan' }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Semester Studi</span>
            <span class="mt-1 font-semibold text-slate-800 block">{{ $paketSemester->semester_studi }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Versi Paket</span>
            <span class="mt-1 font-mono font-bold text-slate-800 block">{{ $paketSemester->versi }}</span>
        </div>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="kurikulum_id"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kurikulum <span
                    class="text-rose-500">*</span></label>
            <select id="kurikulum_id" name="kurikulum_id" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kurikulum_id') border-rose-500 bg-rose-50/50 @enderror"
                aria-invalid="{{ $errors->has('kurikulum_id') ? 'true' : 'false' }}" aria-describedby="kurikulum-error">
                <option value="">Pilih kurikulum</option>

                @foreach ($daftarKurikulum as $kurikulum)
                    <option value="{{ $kurikulum->id }}" @selected($value('kurikulum_id') === (string) $kurikulum->id)>
                        {{ $kurikulum->programStudi->nama }}
                        — {{ $kurikulum->kode }}
                        — {{ $kurikulum->nama }}
                    </option>
                @endforeach
            </select>

            @error('kurikulum_id')
                <p id="kurikulum-error" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="semester_studi"
                class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Semester Studi <span
                    class="text-rose-500">*</span></label>
            <input id="semester_studi" name="semester_studi" type="number" min="1" max="32767" step="1"
                required value="{{ $value('semester_studi') }}"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('semester_studi') border-rose-500 bg-rose-50/50 @enderror"
                aria-invalid="{{ $errors->has('semester_studi') ? 'true' : 'false' }}"
                aria-describedby="semester-help semester-error">

            <p id="semester-help" class="mt-1 text-xs text-slate-500">
                Contoh: 1 untuk paket semester pertama mahasiswa.
            </p>

            @error('semester_studi')
                <p id="semester-error" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
@endif

<div class="mb-6">
    <label for="nama" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Nama Paket <span
            class="text-rose-500">*</span></label>
    <input id="nama" name="nama" type="text" maxlength="100" required
        value="{{ $value('nama', $paketSemester->nama) }}" placeholder="Contoh: Paket Semester 1"
        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('nama') border-rose-500 bg-rose-50/50 @enderror"
        aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}" aria-describedby="nama-error">

    @error('nama')
        <p id="nama-error" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
    @enderror
</div>

@if (!$editing)
    <p class="text-xs text-slate-500">
        Nomor versi dibuat otomatis untuk kurikulum dan semester yang dipilih. Kurikulum, semester studi, dan versi
        dikunci setelah paket dibuat. Mata kuliah dipilih pada langkah berikutnya.
    </p>
@else
    @php
        $existingIds = $paketSemester->details->pluck('kurikulum_mata_kuliah_id')->all();

        $oldIds = old('mata_kuliah_ids', $existingIds);

        $selectedIds = is_array($oldIds)
            ? array_map(static fn($id): string => (string) $id, array_filter($oldIds, 'is_scalar'))
            : [];
    @endphp

    <div class="mt-8 pt-6 border-t border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Susunan Mata Kuliah</h3>
                <p class="text-xs text-slate-500 mt-0.5">Centang mata kuliah yang masuk paket. Draf boleh disimpan tanpa
                    mata kuliah.</p>
            </div>
        </div>

        @error('mata_kuliah_ids')
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700" role="alert">
                {{ $message }}
            </div>
        @enderror

        <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold w-16 text-center">Pilih</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Kode</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Mata Kuliah</th>
                            <th scope="col" class="px-4 py-3 font-semibold">SKS</th>
                            <th scope="col" class="px-4 py-3 font-semibold text-center">Semester Rekomendasi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($daftarMataKuliah as $item)
                            @php
                                $sudahDiPaket = in_array($item->id, $existingIds, true);
                                $tidakBisaDitambahkan = !$item->mataKuliah->aktif && !$sudahDiPaket;
                            @endphp

                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    <input
                                        class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark paket-checkbox"
                                        id="mata-kuliah-{{ $item->id }}" name="mata_kuliah_ids[]" type="checkbox"
                                        value="{{ $item->id }}" @checked(in_array((string) $item->id, $selectedIds, true))
                                        @disabled($tidakBisaDitambahkan)
                                        aria-labelledby="nama-mk-{{ $item->id }} kode-mk-{{ $item->id }}">
                                </td>

                                <td class="px-4 py-3 font-mono font-bold text-slate-800 text-xs"
                                    id="kode-mk-{{ $item->id }}">
                                    {{ $item->mataKuliah->kode }}
                                </td>

                                <td class="px-4 py-3">
                                    <label class="font-bold text-slate-800 block cursor-pointer"
                                        id="nama-mk-{{ $item->id }}" for="mata-kuliah-{{ $item->id }}">
                                        {{ $item->mataKuliah->nama }}
                                    </label>

                                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
                                        <span
                                            class="font-medium {{ $item->sifat === 'wajib' ? 'text-siakad-dark' : 'text-amber-600' }}">
                                            {{ $item->sifat === 'wajib' ? 'Wajib' : 'Pilihan' }}
                                        </span>

                                        @if (!$item->mataKuliah->aktif)
                                            <span class="text-rose-600">— Mata kuliah nonaktif</span>
                                        @endif
                                    </span>
                                </td>

                                <td class="px-4 py-3 font-semibold text-slate-700">
                                    {{ number_format((float) $item->sks, 1, ',', '.') }}
                                </td>

                                <td class="px-4 py-3 text-center font-medium text-slate-700">
                                    {{ $item->semester_rekomendasi }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    Kurikulum belum memiliki mata kuliah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            Mata kuliah nonaktif yang sudah tercatat dapat dikeluarkan. Semua mata kuliah dalam paket harus aktif saat
            paket diterbitkan.
        </p>
    </div>
@endif
