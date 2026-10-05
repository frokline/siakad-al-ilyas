@csrf

{{-- Input Hidden Version untuk Optimistic Locking pada Mode Edit --}}
@if (isset($periodeAkademik) && $periodeAkademik->exists && !empty($version))
    <input type="hidden" name="version" value="{{ $version }}">
@endif

<div class="space-y-6">

    <!-- Pesan Error Global -->
    @if ($errors->any())
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
            <p class="font-bold mb-1">Periksa kembali formulir Anda:</p>
            <ul class="list-disc pl-5 space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Informasi Kode Otomatis (Hanya Tampil Pada Mode Edit) --}}
    @if (isset($periodeAkademik) && $periodeAkademik->exists)
        <div class="rounded-lg bg-slate-50 border border-slate-200 p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Kode Periode
                    Akademik</span>
                <span class="font-mono font-bold text-slate-800 text-base">{{ $periodeAkademik->kode }}</span>
            </div>
            <span class="text-xs text-slate-500 italic">Dihasilkan otomatis oleh sistem</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Tahun Mulai Akademik -->
        <div>
            <label for="tahun_mulai" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Tahun Awal Akademik <span class="text-rose-500">*</span>
            </label>
            <input type="number" id="tahun_mulai" name="tahun_mulai"
                value="{{ old('tahun_mulai', $periodeAkademik->tahun_mulai ?? date('Y')) }}" required min="1900"
                max="9998" placeholder="Contoh: 2026"
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('tahun_mulai') border-rose-500 bg-rose-50/50 @enderror">
            <p class="mt-1 text-[11px] text-slate-500">Tahun ajaran akan terbentuk otomatis (misal: 2026/2027).</p>
            @error('tahun_mulai')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Semester -->
        <div>
            <label for="jenis" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Jenis Semester <span class="text-rose-500">*</span>
            </label>
            <select id="jenis" name="jenis" required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('jenis') border-rose-500 bg-rose-50/50 @enderror">
                @foreach ($jenisOptions as $kodeJenis => $labelJenis)
                    <option value="{{ $kodeJenis }}" @selected(old('jenis', $periodeAkademik->jenis ?? 'ganjil') === $kodeJenis)>
                        {{ $labelJenis }}
                    </option>
                @endforeach
            </select>
            @error('jenis')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Tanggal Mulai Perkuliahan -->
        <div>
            <label for="mulai" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Tanggal Mulai Perkuliahan <span class="text-rose-500">*</span>
            </label>
            <input type="date" id="mulai" name="mulai"
                value="{{ old('mulai', isset($periodeAkademik->mulai) ? $periodeAkademik->mulai->format('Y-m-d') : '') }}"
                required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('mulai') border-rose-500 bg-rose-50/50 @enderror">
            @error('mulai')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Selesai Perkuliahan -->
        <div>
            <label for="selesai" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
                Tanggal Selesai Perkuliahan <span class="text-rose-500">*</span>
            </label>
            <input type="date" id="selesai" name="selesai"
                value="{{ old('selesai', isset($periodeAkademik->selesai) ? $periodeAkademik->selesai->format('Y-m-d') : '') }}"
                required
                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('selesai') border-rose-500 bg-rose-50/50 @enderror">
            @error('selesai')
                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    <!-- Status Periode -->
    <div>
        <label for="status" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">
            Status Periode <span class="text-rose-500">*</span>
        </label>
        <select id="status" name="status" required
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('status') border-rose-500 bg-rose-50/50 @enderror">
            @foreach ($statusOptions as $kodeStatus => $labelStatus)
                <option value="{{ $kodeStatus }}" @selected(old('status', $periodeAkademik->status ?? 'persiapan') === $kodeStatus)>
                    {{ $labelStatus }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-500">Menentukan apakah periode sedang dalam tahap persiapan, aktif, atau
            diarsipkan.</p>
        @error('status')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Jadwal Pengisian KRS Mahasiswa -->
    <div class="border-t border-slate-200 pt-6">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jadwal Pengisian KRS Mahasiswa
            (Opsional)</h3>
        <p class="text-xs text-slate-500 mb-4">Atur rentang tanggal dan waktu pengisian Kartu Rencana Studi oleh
            mahasiswa.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Awal KRS -->
            <div>
                <label for="krs_mulai" class="block text-xs font-bold text-slate-600 mb-1.5">
                    Awal Pengisian KRS
                </label>
                @php
                    $krsMulaiValue = old('krs_mulai');
                    if (!$krsMulaiValue && isset($periodeAkademik->krs_mulai)) {
                        $krsMulaiValue = $periodeAkademik->krs_mulai
                            ->setTimezone(config('siakad.timezone'))
                            ->format('Y-m-d\TH:i');
                    }
                @endphp
                <input type="datetime-local" id="krs_mulai" name="krs_mulai" value="{{ $krsMulaiValue }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('krs_mulai') border-rose-500 bg-rose-50/50 @enderror">
                @error('krs_mulai')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Akhir KRS -->
            <div>
                <label for="krs_selesai" class="block text-xs font-bold text-slate-600 mb-1.5">
                    Batas Akhir Pengisian KRS
                </label>
                @php
                    $krsSelesaiValue = old('krs_selesai');
                    if (!$krsSelesaiValue && isset($periodeAkademik->krs_selesai)) {
                        $krsSelesaiValue = $periodeAkademik->krs_selesai
                            ->setTimezone(config('siakad.timezone'))
                            ->format('Y-m-d\TH:i');
                    }
                @endphp
                <input type="datetime-local" id="krs_selesai" name="krs_selesai" value="{{ $krsSelesaiValue }}"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('krs_selesai') border-rose-500 bg-rose-50/50 @enderror">
                @error('krs_selesai')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

</div>
