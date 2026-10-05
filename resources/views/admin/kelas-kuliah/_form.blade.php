@csrf

@if ($errors->any())
    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
        <strong class="font-bold">Gagal menyimpan kelas.</strong> Terdapat beberapa masalah:
        <ul class="list-disc list-inside mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($kelas->exists)
    @method('PATCH')
    <input type="hidden" name="versi" value="{{ old('versi', $versi) }}">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 text-sm">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Mata Kuliah</span>
            <span class="mt-1 font-bold text-slate-800 block">{{ $kelas->nama_mk_snapshot }}</span>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">SKS</span>
            <span class="mt-1 font-semibold text-slate-700 block">{{ str_replace('.', ',', $kelas->sks_snapshot) }}
                SKS</span>
        </div>
    </div>
@else
    <input type="hidden" name="rombel_id" value="{{ $rombel->id }}">

    <div class="mb-6">
        <label for="detail_paket_id" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Mata
            Kuliah Paket <span class="text-rose-500">*</span></label>
        <select name="detail_paket_id" id="detail_paket_id" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('detail_paket_id') border-rose-500 bg-rose-50/50 @enderror"
            aria-describedby="mata-kuliah-help" aria-invalid="{{ $errors->has('detail_paket_id') ? 'true' : 'false' }}">
            <option value="">Pilih mata kuliah</option>
            @foreach ($detailPilihan as $detail)
                <option value="{{ $detail->id }}" @selected((string) old('detail_paket_id') === (string) $detail->id)>
                    {{ $detail->kurikulumMataKuliah->mataKuliah->kode }}
                    — {{ $detail->kurikulumMataKuliah->mataKuliah->nama }}
                    — {{ str_replace('.', ',', $detail->kurikulumMataKuliah->sks) }} SKS
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500" id="mata-kuliah-help">
            Hanya mata kuliah aktif dari paket rombel yang belum memiliki kelas. Nama dan SKS disimpan saat kelas
            dibuat.
        </p>
        @error('detail_paket_id')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
@endif

@if (!$kelas->exists || $kelas->kodeDapatDiubah())
    <div class="mb-6">
        <label for="kode" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kode Kelas
            <span class="text-rose-500">*</span></label>
        <input type="text" name="kode" id="kode" required maxlength="40" autocomplete="off"
            autocapitalize="characters" spellcheck="false" value="{{ old('kode', $kelas->kode) }}"
            placeholder="Contoh: UMQ-A"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('kode') border-rose-500 bg-rose-50/50 @enderror"
            aria-describedby="kode-help" aria-invalid="{{ $errors->has('kode') ? 'true' : 'false' }}">
        <p class="mt-1 text-xs text-slate-500" id="kode-help">
            Maksimal 40 karakter. Kode harus unik dalam rombel dan terkunci setelah aktivasi pertama.
        </p>
        @error('kode')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
@else
    <div class="mb-6 rounded-lg bg-slate-50 p-4 border border-slate-200">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kode Kelas</span>
        <span class="mt-1 font-mono font-bold text-slate-800 text-base block">{{ $kelas->kode }}</span>
        <p class="mt-1 text-xs text-slate-500">Kode telah dikunci sejak aktivasi pertama.</p>
    </div>
@endif

@if ($kelas->exists)
    <div class="mb-6">
        <label for="status" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status Kelas
            <span class="text-rose-500">*</span></label>
        <select name="status" id="status" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('status') border-rose-500 bg-rose-50/50 @enderror"
            aria-describedby="status-help">
            @foreach ($kelas->pilihanStatus() as $kode => $label)
                <option value="{{ $kode }}" @selected(old('status', $kelas->status) === $kode)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500" id="status-help">
            Aktivasi memerlukan periode aktif. Kelas aktif harus diselesaikan sebelum diarsipkan. Arsip yang belum
            pernah aktif dapat dikembalikan ke persiapan.
        </p>
        @error('status')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>
@else
    <div class="mb-6 rounded-lg bg-slate-50 p-4 border border-slate-200 text-sm text-slate-600">
        Status awal kelas adalah <strong class="text-slate-800">persiapan</strong>. Setelah data diperiksa, aktivasi
        dilakukan melalui halaman edit.
    </div>
@endif

<div class="mb-6">
    <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi">
        <input class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark" type="checkbox"
            name="konfirmasi" id="konfirmasi" value="1" required>
        <span class="text-sm font-medium text-slate-700">Saya telah memeriksa rombel, mata kuliah, kode kelas, dan
            status yang dipilih.</span>
    </label>
    @error('konfirmasi')
        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
    @enderror
</div>

@if ($errors->has('versi') && $kelas->exists)
    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 flex items-center justify-between"
        role="alert">
        <span>{{ $errors->first('versi') }}</span>
        <a href="{{ route('admin.kelas-kuliah.edit', $kelas) }}"
            class="font-semibold underline hover:text-rose-900">Muat formulir terbaru</a>
    </div>
@endif

<div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
    <a class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
        href="{{ $kelas->exists ? route('admin.kelas-kuliah.show', $kelas) : route('admin.kelas-kuliah.index') }}">
        Kembali
    </a>
    <button type="submit"
        class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 disabled:opacity-50"
        @disabled($errors->has('versi'))>
        {{ $kelas->exists ? 'Simpan perubahan' : 'Simpan kelas' }}
    </button>
</div>
