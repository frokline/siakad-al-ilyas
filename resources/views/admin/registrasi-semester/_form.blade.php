{{--
    Isi formulir Registrasi Semester (create & edit). Tag <form> ada di halaman pemanggil.
    Nama field TIDAK diubah: riwayat_studi_id, rombel_id, status, alasan_status, versi, konfirmasi.
--}}
@csrf

@php
    $edit = $registrasi->exists;
    $bisaUbahPenempatan = !$edit || $registrasi->penempatanDapatDiubah();

    $dasar =
        'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2';
    $kelasInput = fn(string $field) => $dasar .
        ' ' .
        ($errors->has($field)
            ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200'
            : 'border-slate-300 focus:border-siakad-dark focus:ring-siakad-dark/20');
    $label = 'mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500';
    $bantuan = 'mt-1.5 text-xs leading-5 text-slate-500';
    $galat = 'mt-1.5 text-xs font-medium text-rose-600';

    $dataRombel = $rombelPilihan
        ->mapWithKeys(
            fn($r) => [
                (string) $r->id => [
                    'kode' => $r->kode,
                    'semester' => $r->paketSemester->semester_studi,
                    'paket' => $r->paketSemester->nama,
                    'versi' => $r->paketSemester->versi,
                    'terpakai' => (int) $r->kursi_terpakai,
                    'kapasitas' => $r->kapasitas,
                    'arsip' => $r->paketSemester->status !== \App\Models\PaketSemester::DITERBITKAN,
                ],
            ],
        )
        ->all();

    $keteranganStatus = [
        \App\Models\RegistrasiSemester::TERDAFTAR => 'Mengisi kursi, belum aktif.',
        \App\Models\RegistrasiSemester::AKTIF => 'Mengisi kursi. Penempatan terkunci sejak aktivasi pertama.',
        \App\Models\RegistrasiSemester::CUTI => 'Melepas kursi. Wajib menuliskan alasan.',
        \App\Models\RegistrasiSemester::BATAL => 'Melepas kursi. Wajib menuliskan alasan.',
    ];
@endphp

@if ($edit)
    @method('PATCH')
    <input type="hidden" name="versi" value="{{ old('versi', $versi) }}">
@else
    <input type="hidden" name="riwayat_studi_id" value="{{ $riwayat->id }}">
@endif

<div class="space-y-6" x-data="{
    rombel: @js((string) old('rombel_id', $registrasi->rombel_id)),
    daftar: @js($dataRombel),
    status: @js((string) old('status', $registrasi->status ?? '')),
    alasan: @js((string) old('alasan_status', $registrasi->alasan_status ?? '')),
    rombelLama: @js((string) ($registrasi->rombel_id ?? '')),
    get terpilih() { return this.daftar[this.rombel] ?? null },
    get perluAlasan() { return ['cuti', 'batal'].includes(this.status) },
    get penuh() {
        const r = this.terpilih;
        return !!r && r.kapasitas !== null && r.terpakai >= r.kapasitas && this.rombel !== this.rombelLama;
    }
}">

    @error('riwayat_studi_id')
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-700" role="alert">
            {{ $message }}</div>
    @enderror

    {{-- Rombel --}}
    @if ($bisaUbahPenempatan)
        <div>
            <label for="rombel_id" class="{{ $label }}">Rombel <span class="text-rose-500">*</span></label>
            <select id="rombel_id" name="rombel_id" required x-model="rombel" class="{{ $kelasInput('rombel_id') }}"
                aria-describedby="rombel-help" aria-invalid="{{ $errors->has('rombel_id') ? 'true' : 'false' }}">
                <option value="">Pilih rombel</option>
                @foreach ($rombelPilihan as $pilihan)
                    <option value="{{ $pilihan->id }}">
                        {{ $pilihan->kode }} · Semester {{ $pilihan->paketSemester->semester_studi }}
                        · {{ $pilihan->paketSemester->nama }} · Versi {{ $pilihan->paketSemester->versi }}
                        · Kursi {{ $pilihan->kursi_terpakai }}/{{ $pilihan->kapasitas ?? 'tanpa batas' }}
                        @if ($pilihan->paketSemester->status !== \App\Models\PaketSemester::DITERBITKAN)
                            · Paket arsip, penempatan lama
                        @endif
                    </option>
                @endforeach
            </select>
            <p id="rombel-help" class="{{ $bantuan }}">Semester studi mengikuti paket rombel. Kapasitas diperiksa
                kembali
                saat disimpan. Setelah aktivasi pertama, rombel dan semester studi terkunci.</p>
            @error('rombel_id')
                <p class="{{ $galat }}">{{ $message }}</p>
            @enderror

            {{-- Ringkasan rombel terpilih --}}
            <div x-show="terpilih" x-cloak x-transition
                class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                <div class="grid gap-3 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Semester</p>
                        <p class="font-semibold text-slate-900"
                            x-text="terpilih ? 'Semester ' + terpilih.semester : ''"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Paket</p>
                        <p class="font-semibold text-slate-900"
                            x-text="terpilih ? terpilih.paket + ' (v' + terpilih.versi + ')' : ''"></p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Kursi</p>
                        <p class="font-semibold text-slate-900"
                            x-text="terpilih ? terpilih.terpakai + ' / ' + (terpilih.kapasitas ?? 'tanpa batas') : ''">
                        </p>
                    </div>
                </div>
                <p x-show="terpilih && terpilih.arsip" x-cloak class="mt-3 text-xs font-medium text-amber-700">
                    Paket ini sudah diarsipkan; hanya penempatan lama yang dipertahankan.
                </p>
                <p x-show="penuh" x-cloak class="mt-3 text-xs font-semibold text-rose-700">
                    Kapasitas rombel ini sudah penuh. Pilih rombel lain agar penyimpanan tidak ditolak.
                </p>
            </div>
        </div>
    @else
        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-slate-500 ring-1 ring-slate-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </span>
            <div class="text-sm">
                <p class="font-semibold text-slate-900">Rombel {{ $registrasi->rombel->kode }} · Semester
                    {{ $registrasi->semester_studi }}</p>
                <p class="text-xs text-slate-500">Penempatan telah dikunci sejak aktivasi pertama dan tidak dapat
                    diubah.</p>
            </div>
        </div>
    @endif

    @if ($edit)
        {{-- Status registrasi --}}
        <fieldset>
            <legend class="{{ $label }}">Status registrasi <span class="text-rose-500">*</span></legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($registrasi->pilihanStatus() as $kode => $labelStatus)
                    <label class="block cursor-pointer">
                        <input type="radio" name="status" value="{{ $kode }}" x-model="status" required
                            class="peer sr-only">
                        <span
                            class="block rounded-xl border border-slate-300 bg-white p-4 transition hover:border-siakad-dark/50 peer-checked:border-siakad-dark peer-checked:bg-emerald-50 peer-checked:ring-2 peer-checked:ring-siakad-dark/20 peer-focus-visible:ring-2 peer-focus-visible:ring-siakad-dark/40">
                            <span class="block text-sm font-bold text-slate-900">{{ $labelStatus }}</span>
                            <span
                                class="mt-1 block text-xs leading-5 text-slate-500">{{ $keteranganStatus[$kode] ?? '' }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="{{ $bantuan }}">Pengaktifan kembali membutuhkan kursi tersedia.</p>
            @error('status')
                <p class="{{ $galat }}">{{ $message }}</p>
            @enderror
        </fieldset>

        @if ($bisaUbahPenempatan)
            <div x-show="status === 'aktif'" x-cloak x-collapse>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-semibold">Aktivasi pertama mengunci penempatan.</p>
                    <p class="mt-1">Setelah disimpan dengan status Aktif, rombel dan semester studi tidak dapat diubah
                        lagi.</p>
                </div>
            </div>
        @endif

        {{-- Alasan: hanya untuk cuti / batal --}}
        <div x-show="perluAlasan" x-cloak x-collapse>
            <label for="alasan_status" class="{{ $label }}">Alasan status <span
                    class="text-rose-500">*</span></label>
            <textarea id="alasan_status" name="alasan_status" rows="4" minlength="10" maxlength="1000" x-model="alasan"
                :required="perluAlasan" :disabled="!perluAlasan" aria-describedby="alasan-help"
                class="{{ $kelasInput('alasan_status') }}" placeholder="Tuliskan alasan cuti atau pembatalan…"></textarea>
            <div class="mt-1.5 flex items-start justify-between gap-3">
                <p id="alasan-help" class="text-xs leading-5 text-slate-500">Wajib 10–1.000 karakter untuk cuti atau
                    batal.</p>
                <p class="shrink-0 text-xs tabular-nums"
                    :class="alasan.trim().length < 10 ? 'text-amber-600' : 'text-slate-500'"
                    x-text="alasan.length + ' / 1000'"></p>
            </div>
            @error('alasan_status')
                <p class="{{ $galat }}">{{ $message }}</p>
            @enderror
        </div>
    @else
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
            Registrasi baru berstatus <strong>Terdaftar</strong>. Aktivasi dilakukan melalui halaman edit setelah data
            diperiksa.
        </div>
    @endif

    @if ($errors->has('versi') && $edit)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="alert">
            <p class="font-semibold">{{ $errors->first('versi') }}</p>
            <a href="{{ route('admin.registrasi-semester.edit', $registrasi) }}"
                class="mt-1 inline-block font-semibold underline">Muat formulir terbaru</a>
        </div>
    @endif

    {{-- Konfirmasi --}}
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <label for="konfirmasi" class="flex cursor-pointer items-start gap-3">
            <input type="checkbox" id="konfirmasi" name="konfirmasi" value="1" required
                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark/30">
            <span class="text-sm text-slate-700">Saya telah memeriksa mahasiswa, periode, rombel, dan status
                registrasi.</span>
        </label>
        @error('konfirmasi')
            <p class="{{ $galat }}">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
        <a href="{{ $edit ? route('admin.registrasi-semester.show', $registrasi) : route('admin.registrasi-semester.index') }}"
            class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-50">Kembali</a>
        <button type="submit" @disabled($errors->has('versi'))
            class="rounded-xl bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-siakad-active disabled:cursor-not-allowed disabled:opacity-50">
            {{ $edit ? 'Simpan perubahan' : 'Simpan registrasi' }}
        </button>
    </div>
</div>
