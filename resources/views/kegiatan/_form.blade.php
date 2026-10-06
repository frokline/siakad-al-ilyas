@php
    $jenisTerpilih = old('jenis', $kegiatan->jenis ?? \App\Models\Kegiatan::TUGAS);
    $ekstensiTerpilih = (array) old(
        'ekstensi_jawaban',
        $kegiatan->ekstensi_diizinkan ?? \App\Models\Kegiatan::EKSTENSI_JAWABAN,
    );
    $maksimalBerkas = old('maksimal_berkas', $kegiatan->maks_berkas ?? 5);
    $maksimalMb = old(
        'maksimal_mb_per_berkas',
        $kegiatan->maks_ukuran_byte ? (int) ceil($kegiatan->maks_ukuran_byte / 1048576) : 20,
    );
    $dihapusLama = array_map('strval', (array) old('lampiran_dihapus', []));

    $input =
        'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm transition focus:border-siakad-active focus:outline-none focus:ring-4 focus:ring-emerald-100';
    $label = 'mb-2 block text-xs font-bold tracking-wide text-slate-700';
    $kartu = 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5';
@endphp

<div
    class="overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-emerald-50 shadow-sm">
    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-start gap-4">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-siakad-active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.75 5.75A2.25 2.25 0 0 1 7 3.5h10a2.25 2.25 0 0 1 2.25 2.25v12.5A2.25 2.25 0 0 1 17 20.5H7a2.25 2.25 0 0 1-2.25-2.25V5.75Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 8h8M8 12h8M8 16h5" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-700">Kelas pembelajaran</p>
                <h2 class="mt-1 truncate text-base font-bold text-slate-900 sm:text-lg">
                    {{ $kelas->kode }} &mdash; {{ $kelas->nama_mk_snapshot }}
                </h2>
                <p class="mt-1 text-xs leading-5 text-slate-600">Setelah disimpan, pembelajaran langsung tersedia dan
                    notifikasi dikirim kepada mahasiswa kelas.</p>
            </div>
        </div>
    </div>
</div>

<section class="{{ $kartu }}">
    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
        <div
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
            01</div>
        <div>
            <h2 class="text-base font-bold text-slate-900">Informasi utama</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Atur identitas dan isi utama pembelajaran.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div>
            <label for="jenis" class="{{ $label }}">Jenis pembelajaran *</label>
            <select id="jenis" name="jenis" required class="{{ $input }}">
                @foreach ($pilihanJenis as $kode => $nama)
                    <option value="{{ $kode }}" @selected($jenisTerpilih === $kode)>{{ $nama }}</option>
                @endforeach
            </select>
            @error('jenis')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs leading-5 text-slate-500">Materi tidak meminta jawaban. Tugas, latihan, UTS, dan UAS
                dapat dikumpulkan mahasiswa.</p>
        </div>

        <div>
            <label for="judul" class="{{ $label }}">Judul *</label>
            <input id="judul" name="judul" type="text" maxlength="200" required
                value="{{ old('judul', $kegiatan->judul) }}" placeholder="Contoh: Tugas rangkuman Ulumul Quran"
                class="{{ $input }}">
            @error('judul')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="pertemuan_id" class="{{ $label }}">Pertemuan terkait</label>
            <select id="pertemuan_id" name="pertemuan_id" class="{{ $input }}">
                <option value="">Pembelajaran umum kelas</option>
                @foreach ($pertemuan as $sesi)
                    <option value="{{ $sesi->id }}" @selected((string) old('pertemuan_id', $kegiatan->pertemuan_id) === (string) $sesi->id)>
                        Pertemuan {{ $sesi->nomor }} &mdash; {{ $sesi->topik }}
                    </option>
                @endforeach
            </select>
            @error('pertemuan_id')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lg:col-span-2">
            <label for="instruksi" class="{{ $label }}">Isi, instruksi, atau pesan</label>
            <textarea id="instruksi" name="instruksi" rows="8" maxlength="10000"
                placeholder="Tuliskan materi, soal, petunjuk, atau pesan kepada mahasiswa." class="{{ $input }}">{{ old('instruksi', $kegiatan->instruksi) }}</textarea>
            @error('instruksi')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs leading-5 text-slate-500">Boleh dikosongkan jika informasi sudah tersedia di
                lampiran.</p>
        </div>

        <div class="lg:col-span-2">
            <label for="tautan_eksternal" class="{{ $label }}">Tautan tambahan</label>
            <input id="tautan_eksternal" name="tautan_eksternal" type="url" maxlength="2000"
                value="{{ old('tautan_eksternal', $kegiatan->tautan_eksternal) }}" placeholder="https://..."
                class="{{ $input }}">
            @error('tautan_eksternal')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs leading-5 text-slate-500">Opsional. Untuk video, pertemuan daring, atau referensi
                lain.</p>
        </div>
    </div>
</section>

<section class="{{ $kartu }}">
    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
        <div
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
            02</div>
        <div>
            <h2 class="text-base font-bold text-slate-900">Lampiran pembelajaran</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Tambahkan file pendukung dan kelola lampiran yang sudah
                tersimpan.</p>
        </div>
    </div>

    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-5">
        <label for="lampiran_baru" class="{{ $label }}">Pilih berkas dari komputer</label>
        <input id="lampiran_baru" name="lampiran_baru[]" type="file" multiple
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-siakad-active hover:file:bg-emerald-100">
        @error('lampiran_baru')
            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
        @error('lampiran_baru.*')
            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs leading-5 text-slate-500">Maksimal 10 berkas: PDF, gambar, Word, Excel, PowerPoint, atau
            ZIP.</p>
    </div>

    @if ($kegiatan->exists && $kegiatan->relationLoaded('lampiran') && $kegiatan->lampiran->isNotEmpty())
        <div>
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-600">Lampiran yang sudah tersimpan</p>
                <span
                    class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">{{ $kegiatan->lampiran->count() }}
                    berkas</span>
            </div>
            <ul class="overflow-hidden rounded-xl border border-slate-200 bg-white divide-y divide-slate-100">
                @foreach ($kegiatan->lampiran as $lampiran)
                    <li class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="min-w-0">
                            <strong
                                class="block truncate text-sm font-semibold text-slate-800">{{ $lampiran->berkas?->label ?? 'Lampiran' }}</strong>
                            @if ($lampiran->berkas)
                                <span
                                    class="mt-0.5 block truncate text-xs text-slate-500">{{ $lampiran->berkas->nama_asli }}</span>
                            @endif
                        </span>
                        <label
                            class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600">
                            <input type="checkbox" name="lampiran_dihapus[]" value="{{ $lampiran->id }}"
                                class="rounded border-slate-300 text-rose-600" @checked(in_array((string) $lampiran->id, $dihapusLama, true))>
                            Hapus
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</section>

<section id="pengaturan-pengumpulan" class="{{ $kartu }}">
    <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
        <div
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
            03</div>
        <div>
            <h2 class="text-base font-bold text-slate-900">Pengaturan pengumpulan jawaban</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Hanya untuk tugas, latihan, UTS, dan UAS.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div>
            <label for="buka_lokal" class="{{ $label }}">Mulai dikerjakan *</label>
            <input id="buka_lokal" name="buka_lokal" type="datetime-local" step="60"
                class="{{ $input }}"
                value="{{ old('buka_lokal', $kegiatan->buka_at ? $kegiatan->buka_at->setTimezone($zona)->format('Y-m-d\TH:i') : '') }}">
            @error('buka_lokal')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tenggat_lokal" class="{{ $label }}">Batas pengumpulan *</label>
            <input id="tenggat_lokal" name="tenggat_lokal" type="datetime-local" step="60"
                class="{{ $input }}"
                value="{{ old('tenggat_lokal', $kegiatan->tenggat_at ? $kegiatan->tenggat_at->setTimezone($zona)->format('Y-m-d\TH:i') : '') }}">
            @error('tenggat_lokal')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="maksimal_berkas" class="{{ $label }}">Maksimal jumlah berkas jawaban *</label>
            <input id="maksimal_berkas" name="maksimal_berkas" type="number" min="1" max="10"
                step="1" value="{{ $maksimalBerkas }}" class="{{ $input }}">
            @error('maksimal_berkas')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="maksimal_mb_per_berkas" class="{{ $label }}">Maksimal ukuran tiap berkas (MB)
                *</label>
            <input id="maksimal_mb_per_berkas" name="maksimal_mb_per_berkas" type="number" min="1"
                max="50" step="1" value="{{ $maksimalMb }}" class="{{ $input }}">
            @error('maksimal_mb_per_berkas')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
        Waktu memakai zona <strong class="font-semibold text-slate-800">{{ $zona }}</strong>.
    </div>

    <div>
        <p class="{{ $label }}">Jenis berkas jawaban</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($pilihanEkstensi as $ekstensi)
                <label
                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 has-[:checked]:border-siakad-active has-[:checked]:bg-emerald-50 has-[:checked]:text-siakad-active">
                    <input type="checkbox" name="ekstensi_jawaban[]" value="{{ $ekstensi }}"
                        class="rounded border-slate-300 text-siakad-active" @checked(in_array($ekstensi, $ekstensiTerpilih, true))>
                    {{ strtoupper($ekstensi) }}
                </label>
            @endforeach
        </div>
        @error('ekstensi_jawaban')
            <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <p class="text-xs leading-5 text-slate-500">
        Mahasiswa dapat mengganti atau menghapus jawabannya selama batas pengumpulan belum berakhir.
    </p>
</section>

@if ($kegiatan->exists)
    <section class="{{ $kartu }}">
        <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
            <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
                04</div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Catatan perubahan</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">Tambahkan alasan atau catatan untuk perubahan
                    pembelajaran.</p>
            </div>
        </div>

        <div>
            <label for="alasan" class="{{ $label }}">Catatan perubahan</label>
            <textarea id="alasan" name="alasan" rows="3" maxlength="1000" placeholder="Opsional"
                class="{{ $input }}">{{ old('alasan') }}</textarea>
            @error('alasan')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </section>
@endif

<div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ $kegiatan->exists ? route('kegiatan.show', $kegiatan) : route('kegiatan.kelas') }}"
        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
        Batal
    </a>

    <button type="submit"
        class="inline-flex items-center justify-center rounded-xl bg-siakad-active px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-100">
        {{ $kegiatan->exists ? 'Simpan perubahan' : 'Bagikan pembelajaran' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenis = document.getElementById('jenis');
        const pengaturan = document.getElementById('pengaturan-pengumpulan');
        if (!jenis || !pengaturan) {
            return;
        }

        const wajib = ['buka_lokal', 'tenggat_lokal', 'maksimal_berkas', 'maksimal_mb_per_berkas'];

        const perbarui = function() {
            const materi = jenis.value === 'materi';
            pengaturan.hidden = materi;
            pengaturan.querySelectorAll('input, select, textarea').forEach(function(el) {
                el.disabled = materi;
                el.required = !materi && wajib.includes(el.name);
            });
        };

        jenis.addEventListener('change', perbarui);
        perbarui();
    });
</script>
