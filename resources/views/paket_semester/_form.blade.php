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
        <div class="alert alert-error" role="alert">
            {{ $message }}

            <a href="{{ route('admin.paket-semester.edit', $paketSemester) }}">
                Muat ulang formulir
            </a>
        </div>
    @enderror

    <dl class="detail-grid">
        <div>
            <dt>Program studi</dt>
            <dd>{{ $paketSemester->kurikulum->programStudi->nama }}</dd>
        </div>

        <div>
            <dt>Kurikulum</dt>
            <dd>
                {{ $paketSemester->kurikulum->kode }}
                — {{ $paketSemester->kurikulum->nama }}
            </dd>
        </div>

        <div>
            <dt>Semester studi</dt>
            <dd>{{ $paketSemester->semester_studi }}</dd>
        </div>

        <div>
            <dt>Versi</dt>
            <dd>{{ $paketSemester->versi }}</dd>
        </div>
    </dl>
@else
    <div class="field">
        <label for="kurikulum_id">Kurikulum</label>

        <select id="kurikulum_id" name="kurikulum_id" required
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
            <p id="kurikulum-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="semester_studi">Semester studi</label>

        <input id="semester_studi" name="semester_studi" type="number" min="1" max="32767" step="1"
            required value="{{ $value('semester_studi') }}"
            aria-invalid="{{ $errors->has('semester_studi') ? 'true' : 'false' }}"
            aria-describedby="semester-help semester-error">

        <p id="semester-help" class="help">
            Contoh: 1 untuk paket semester pertama mahasiswa.
        </p>

        @error('semester_studi')
            <p id="semester-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>
@endif

<div class="field">
    <label for="nama">Nama paket</label>

    <input id="nama" name="nama" type="text" maxlength="100" required
        value="{{ $value('nama', $paketSemester->nama) }}" placeholder="Contoh: Paket Semester 1"
        aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}" aria-describedby="nama-error">

    @error('nama')
        <p id="nama-error" class="field-error">{{ $message }}</p>
    @enderror
</div>

@if (!$editing)
    <p class="help">
        Nomor versi dibuat otomatis untuk kurikulum dan semester yang dipilih.
        Kurikulum, semester studi, dan versi dikunci setelah paket dibuat.
        Mata kuliah dipilih pada langkah berikutnya.
    </p>
@else
    @php
        $existingIds = $paketSemester->details->pluck('kurikulum_mata_kuliah_id')->all();

        $oldIds = old('mata_kuliah_ids', $existingIds);

        $selectedIds = is_array($oldIds)
            ? array_map(static fn($id): string => (string) $id, array_filter($oldIds, 'is_scalar'))
            : [];
    @endphp

    <div class="paket-section-heading">
        <h2>Susunan Mata Kuliah</h2>

        <p class="help">
            Centang mata kuliah yang masuk paket.
            Draf boleh disimpan tanpa mata kuliah.
        </p>
    </div>

    @error('mata_kuliah_ids')
        <p class="field-error">{{ $message }}</p>
    @enderror

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">Pilih</th>
                    <th scope="col">Kode</th>
                    <th scope="col">Mata Kuliah</th>
                    <th scope="col">SKS</th>
                    <th scope="col">Semester Rekomendasi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($daftarMataKuliah as $item)
                    @php
                        $sudahDiPaket = in_array($item->id, $existingIds, true);

                        $tidakBisaDitambahkan = !$item->mataKuliah->aktif && !$sudahDiPaket;
                    @endphp

                    <tr>
                        <td>
                            <input class="paket-checkbox" id="mata-kuliah-{{ $item->id }}" name="mata_kuliah_ids[]"
                                type="checkbox" value="{{ $item->id }}" @checked(in_array((string) $item->id, $selectedIds, true))
                                @disabled($tidakBisaDitambahkan)
                                aria-labelledby="nama-mk-{{ $item->id }} kode-mk-{{ $item->id }}">
                        </td>

                        <td id="kode-mk-{{ $item->id }}">
                            {{ $item->mataKuliah->kode }}
                        </td>

                        <td>
                            <label id="nama-mk-{{ $item->id }}" for="mata-kuliah-{{ $item->id }}">
                                {{ $item->mataKuliah->nama }}
                            </label>

                            <div class="help">
                                {{ $item->sifat === 'wajib' ? 'Wajib' : 'Pilihan' }}

                                @if (!$item->mataKuliah->aktif)
                                    — Mata kuliah nonaktif
                                @endif
                            </div>
                        </td>

                        <td>
                            {{ number_format((float) $item->sks, 1, ',', '.') }}
                        </td>

                        <td>{{ $item->semester_rekomendasi }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Kurikulum belum memiliki mata kuliah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="help">
        Mata kuliah nonaktif yang sudah tercatat dapat dikeluarkan.
        Semua mata kuliah dalam paket harus aktif saat paket diterbitkan.
    </p>
@endif
