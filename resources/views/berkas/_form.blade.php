@php
    $edit = isset($file) && $file->exists;
    $teks = static function (string $key, string $fallback = ''): string {
        if (!session()->hasOldInput($key)) {
            return $fallback;
        }
        return is_string(old($key)) ? old($key) : '';
    };

    $nilaiLabel = $teks('label', $edit ? $file->label : '');
    $nilaiKet = $teks('keterangan', $edit ? $file->keterangan ?? '' : '');

    $input =
        'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
    $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
@endphp

<form action="{{ $edit ? route('berkas.update', $file) : route('berkas.store') }}" method="post"
    enctype="multipart/form-data" class="space-y-6" x-data="{
        judul: @js($nilaiLabel),
        ket: @js($nilaiKet),
        nama: '',
        ukuran: '',
        besar: false,
        pilih(e) {
            const f = e.target.files[0];
            if (!f) { this.nama = '';
                this.ukuran = '';
                this.besar = false; return; }
            this.nama = f.name;
            this.besar = f.size > 20 * 1024 * 1024;
            this.ukuran = f.size >= 1048576 ?
                (f.size / 1048576).toFixed(1) + ' MB' :
                Math.max(1, Math.round(f.size / 1024)) + ' KB';
        }
    }">
    @csrf

    @if ($edit)
        @method('PATCH')
        <input type="hidden" name="versi" value="{{ $teks('versi', $file->versiForm()) }}">
        @error('versi')
            <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
        @enderror
    @else
        <input type="hidden" name="upload_token" value="{{ $teks('upload_token', $token) }}">
        @error('upload_token')
            <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
        @enderror
    @endif

    @unless ($edit)
        {{-- Area unggah: input asli menutupi seluruh kotak, sehingga klik dan seret-lepas berfungsi bawaan browser. --}}
        <div>
            <label for="file" class="{{ $label }}">Pilih berkas <span class="text-rose-500">*</span></label>
            <div class="group relative rounded-2xl border-2 border-dashed px-6 py-10 text-center transition"
                :class="besar ? 'border-rose-300 bg-rose-50' :
                    (nama ? 'border-siakad-active bg-emerald-50/60' :
                        'border-slate-300 bg-slate-50 hover:border-siakad-active hover:bg-emerald-50/40')">
                <input id="file" name="file" type="file" required accept=".pdf,.jpg,.jpeg,.png"
                    aria-describedby="batas-file" @change="pilih($event)"
                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0">

                <div x-show="!nama" class="pointer-events-none">
                    <span
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-siakad-active">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </span>
                    <p class="mt-4 text-sm font-semibold text-slate-700">Klik untuk memilih berkas, atau seret ke sini
                    </p>
                    <p class="mt-1 text-xs text-slate-500">PDF, JPG, atau PNG &middot; maksimal 20 MB</p>
                </div>

                <div x-show="nama" x-cloak class="pointer-events-none">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full"
                        :class="besar ? 'bg-rose-100 text-rose-600' : 'bg-siakad-active text-white'">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <p class="mt-4 break-all text-sm font-semibold text-slate-800" x-text="nama"></p>
                    <p class="mt-1 text-xs" :class="besar ? 'font-semibold text-rose-600' : 'text-slate-500'"
                        x-text="besar ? ukuran + ' — melebihi batas 20 MB' : ukuran + ' · klik untuk mengganti berkas'">
                    </p>
                </div>
            </div>
            @error('file')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <p id="batas-file" class="mt-1.5 text-xs text-slate-500">Jika validasi gagal, pilih ulang berkas sebelum
                mengirim.</p>
        </div>
    @endunless

    <div>
        <div class="flex items-baseline justify-between">
            <label for="label" class="{{ $label }}">Label berkas <span class="text-rose-500">*</span></label>
            <span class="text-xs text-slate-400" x-text="judul.length + ' / 200'"></span>
        </div>
        <input id="label" name="label" required maxlength="200" x-model="judul" class="{{ $input }}"
            value="{{ $nilaiLabel }}" placeholder="Contoh: Bukti transfer SPP Oktober">
        @error('label')
            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-baseline justify-between">
            <label for="keterangan" class="{{ $label }}">Keterangan <span
                    class="font-normal text-slate-400">(opsional)</span></label>
            <span class="text-xs text-slate-400" x-text="ket.length + ' / 2000'"></span>
        </div>
        <textarea id="keterangan" name="keterangan" rows="4" maxlength="2000" x-model="ket" class="{{ $input }}"
            placeholder="Catatan singkat tentang isi berkas ini">{{ $nilaiKet }}</textarea>
        @error('keterangan')
            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($edit)
        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
            <label for="alasan" class="{{ $label }}">Alasan perubahan <span
                    class="text-rose-500">*</span></label>
            <textarea id="alasan" name="alasan" rows="3" required minlength="10" maxlength="2000"
                class="{{ $input }}" placeholder="Jelaskan mengapa data berkas ini diubah">{{ $teks('alasan') }}</textarea>
            @error('alasan')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-600">Minimal 10 karakter. Isi file dan nama asli tidak berubah. Untuk
                dokumen versi baru, unggah sebagai berkas baru.</p>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
        <button type="submit" @unless ($edit) :disabled="besar" @endunless
            class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/40 disabled:cursor-not-allowed disabled:opacity-50">
            @unless ($edit)
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
            @endunless
            {{ $edit ? 'Simpan perubahan' : 'Unggah berkas' }}
        </button>

        @if ($edit)
            <a href="{{ route('berkas.edit', $file) }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Muat
                ulang versi terbaru</a>
        @else
            <a href="{{ route('berkas.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Batal</a>
        @endif
    </div>
</form>
