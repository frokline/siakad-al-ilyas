@php
    $terbit = $agenda->diterbitkan_at !== null;
    $input =
        'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500';
    $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
@endphp
@csrf
@if ($baru)
    <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
@else
    @method('PATCH')
    <input type="hidden" name="versi" value="{{ old('versi', $agenda->versiForm()) }}">
@endif

<div class="space-y-6">
    {{-- Bagian 1: informasi utama --}}
    <fieldset class="space-y-5">
        <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">1. Informasi agenda</legend>

        <div>
            <label for="judul" class="{{ $label }}">Judul <span class="text-rose-500">*</span></label>
            <input id="judul" name="judul" maxlength="200" minlength="3" required class="{{ $input }}"
                value="{{ old('judul', $agenda->judul) }}" placeholder="Contoh: Ujian Tengah Semester Ganjil">
            @error('judul')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="jenis" class="{{ $label }}">Jenis agenda <span class="text-rose-500">*</span></label>
            <select id="jenis" name="jenis" required class="{{ $input }}">
                @foreach (\App\Models\KalenderAkademik::JENIS as $kode => $nama)
                    <option value="{{ $kode }}" @selected(old('jenis', $agenda->jenis ?? 'lainnya') === $kode)>{{ $nama }}</option>
                @endforeach
            </select>
            @error('jenis')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="keterangan" class="{{ $label }}">Keterangan <span
                    class="font-normal text-slate-400">(opsional)</span></label>
            <textarea id="keterangan" name="keterangan" rows="5" maxlength="5000" class="{{ $input }}"
                placeholder="Penjelasan tambahan untuk pembaca agenda">{{ old('keterangan', $agenda->keterangan) }}</textarea>
            @error('keterangan')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </fieldset>

    {{-- Bagian 2: sasaran --}}
    <fieldset class="space-y-5 border-t border-slate-100 pt-6">
        <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">2. Periode dan sasaran
        </legend>

        @if ($terbit)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Periode dan sasaran tetap setelah publikasi. Perubahan judul, isi, atau waktu langsung terlihat oleh
                pengguna.
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="periode" class="{{ $label }}">Periode akademik <span
                        class="text-rose-500">*</span></label>
                <select id="periode" name="periode_akademik_id" required class="{{ $input }}"
                    @disabled($terbit)>
                    <option value="">Pilih periode</option>
                    @foreach ($periode as $p)
                        <option value="{{ $p->id }}" @selected((string) old('periode_akademik_id', $agenda->periode_akademik_id) === (string) $p->id)>{{ $p->kode }}
                            ({{ $p->status }})
                        </option>
                    @endforeach
                </select>
                @error('periode_akademik_id')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="prodi" class="{{ $label }}">Sasaran program studi</label>
                <select id="prodi" name="program_studi_id" class="{{ $input }}"
                    @disabled($terbit)>
                    <option value="">Seluruh kampus</option>
                    @foreach ($prodi as $p)
                        <option value="{{ $p->id }}" @selected((string) old('program_studi_id', $agenda->program_studi_id) === (string) $p->id)>
                            {{ $p->nama }}{{ $p->aktif ? '' : ' (nonaktif)' }}
                        </option>
                    @endforeach
                </select>
                @error('program_studi_id')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($terbit)
            <input type="hidden" name="periode_akademik_id" value="{{ $agenda->periode_akademik_id }}">
            <input type="hidden" name="program_studi_id" value="{{ $agenda->program_studi_id }}">
        @endif
    </fieldset>

    {{-- Bagian 3: waktu --}}
    <fieldset class="space-y-4 border-t border-slate-100 pt-6">
        <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">3. Waktu pelaksanaan
        </legend>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="mulai" class="{{ $label }}">Mulai (WITA) <span
                        class="text-rose-500">*</span></label>
                <input type="datetime-local" id="mulai" name="mulai_lokal" min="2000-01-01T00:00"
                    max="2100-12-31T23:59" required class="{{ $input }}"
                    value="{{ old('mulai_lokal', $agenda->mulai_at?->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('Y-m-d\TH:i')) }}">
                @error('mulai_lokal')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="selesai" class="{{ $label }}">Selesai (WITA) <span
                        class="text-rose-500">*</span></label>
                <input type="datetime-local" id="selesai" name="selesai_lokal" min="2000-01-01T00:00"
                    max="2100-12-31T23:59" required class="{{ $input }}"
                    value="{{ old('selesai_lokal', $agenda->selesai_at?->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('Y-m-d\TH:i')) }}">
                @error('selesai_lokal')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600">Waktu akhir harus setelah waktu mulai. Untuk
            satu hari penuh, isi 00:00 sampai 00:00 hari berikutnya. Waktu selesai tidak termasuk durasi penayangan
            hari berikutnya.</p>
    </fieldset>

    @unless ($baru)
        <fieldset class="border-t border-slate-100 pt-6">
            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-siakad-active">4. Alasan perubahan
            </legend>
            <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                <label for="alasan" class="{{ $label }}">Alasan perubahan <span
                        class="text-rose-500">*</span></label>
                <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="1000" required
                    class="{{ $input }}" placeholder="Jelaskan mengapa agenda ini diubah">{{ old('alasan') }}</textarea>
                @error('alasan')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-600">Minimal 10 karakter. Alasan perubahan terakhir tampil kepada
                    pembaca agenda yang sudah terbit.</p>
            </div>
        </fieldset>
    @endunless

    <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
        <button type="submit"
            class="rounded-xl bg-siakad-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/40">{{ $baru ? 'Simpan draf' : 'Simpan perubahan' }}</button>
        <a href="{{ $baru ? route('kalender.index') : route('kalender.show', $agenda) }}"
            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Batal</a>
    </div>
</div>
