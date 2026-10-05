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
        'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-siakad-active focus:outline-none focus:ring-1 focus:ring-siakad-active';
    $label = 'mb-1 block text-xs font-semibold text-slate-600';
    $kartu = 'rounded-xl border border-slate-200 bg-white p-5 shadow-sm space-y-4';
@endphp

<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
    <strong>{{ $kelas->kode }}</strong> &mdash; {{ $kelas->nama_mk_snapshot }}
    <p class="mt-1 text-xs">Setelah disimpan, pembelajaran langsung tersedia dan notifikasi dikirim kepada mahasiswa
        kelas.</p>
</div>

<section class="{{ $kartu }}">
    <h2 class="text-sm font-bold text-slate-800">Informasi utama</h2>

    <div>
        <label for="jenis" class="{{ $label }}">Jenis pembelajaran *</label>
        <select id="jenis" name="jenis" required class="{{ $input }}">
            @foreach ($pilihanJenis as $kode => $nama)
                <option value="{{ $kode }}" @selected($jenisTerpilih === $kode)>{{ $nama }}</option>
            @endforeach
        </select>
        @error('jenis')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-slate-500">Materi tidak meminta jawaban. Tugas, latihan, UTS, dan UAS dapat
            dikumpulkan mahasiswa.</p>
    </div>

    <div>
        <label for="judul" class="{{ $label }}">Judul *</label>
        <input id="judul" name="judul" type="text" maxlength="200" required
            value="{{ old('judul', $kegiatan->judul) }}" placeholder="Contoh: Tugas rangkuman Ulumul Quran"
            class="{{ $input }}">
        @error('judul')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
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
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="instruksi" class="{{ $label }}">Isi, instruksi, atau pesan</label>
        <textarea id="instruksi" name="instruksi" rows="8" maxlength="10000"
            placeholder="Tuliskan materi, soal, petunjuk, atau pesan kepada mahasiswa." class="{{ $input }}">{{ old('instruksi', $kegiatan->instruksi) }}</textarea>
        @error('instruksi')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-slate-500">Boleh dikosongkan jika informasi sudah tersedia di lampiran.</p>
    </div>

    <div>
        <label for="tautan_eksternal" class="{{ $label }}">Tautan tambahan</label>
        <input id="tautan_eksternal" name="tautan_eksternal" type="url" maxlength="2000"
            value="{{ old('tautan_eksternal', $kegiatan->tautan_eksternal) }}" placeholder="https://..."
            class="{{ $input }}">
        @error('tautan_eksternal')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-slate-500">Opsional. Untuk video, pertemuan daring, atau referensi lain.</p>
    </div>
</section>

<section class="{{ $kartu }}">
    <h2 class="text-sm font-bold text-slate-800">Lampiran pembelajaran</h2>

    <div>
        <label for="lampiran_baru" class="{{ $label }}">Pilih berkas dari komputer</label>
        <input id="lampiran_baru" name="lampiran_baru[]" type="file" multiple
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-siakad-active hover:file:bg-emerald-100">
        @error('lampiran_baru')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
        @error('lampiran_baru.*')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-slate-500">Maksimal 10 berkas: PDF, gambar, Word, Excel, PowerPoint, atau ZIP.</p>
    </div>

    @if ($kegiatan->exists && $kegiatan->relationLoaded('lampiran') && $kegiatan->lampiran->isNotEmpty())
        <div>
            <p class="mb-2 text-xs font-semibold text-slate-600">Lampiran yang sudah tersimpan</p>
            <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                @foreach ($kegiatan->lampiran as $lampiran)
                    <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <span class="min-w-0">
                            <strong
                                class="block truncate text-slate-800">{{ $lampiran->berkas?->label ?? 'Lampiran' }}</strong>
                            @if ($lampiran->berkas)
                                <span
                                    class="block truncate text-xs text-slate-500">{{ $lampiran->berkas->nama_asli }}</span>
                            @endif
                        </span>
                        <label class="flex shrink-0 items-center gap-2 text-xs font-semibold text-rose-600">
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
    <div>
        <h2 class="text-sm font-bold text-slate-800">Pengaturan pengumpulan jawaban</h2>
        <p class="mt-1 text-xs text-slate-500">Hanya untuk tugas, latihan, UTS, dan UAS.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="buka_lokal" class="{{ $label }}">Mulai dikerjakan *</label>
            <input id="buka_lokal" name="buka_lokal" type="datetime-local" step="60" class="{{ $input }}"
                value="{{ old('buka_lokal', $kegiatan->buka_at ? $kegiatan->buka_at->setTimezone($zona)->format('Y-m-d\TH:i') : '') }}">
            @error('buka_lokal')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tenggat_lokal" class="{{ $label }}">Batas pengumpulan *</label>
            <input id="tenggat_lokal" name="tenggat_lokal" type="datetime-local" step="60"
                class="{{ $input }}"
                value="{{ old('tenggat_lokal', $kegiatan->tenggat_at ? $kegiatan->tenggat_at->setTimezone($zona)->format('Y-m-d\TH:i') : '') }}">
            @error('tenggat_lokal')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="maksimal_berkas" class="{{ $label }}">Maksimal jumlah berkas jawaban *</label>
            <input id="maksimal_berkas" name="maksimal_berkas" type="number" min="1" max="10"
                step="1" value="{{ $maksimalBerkas }}" class="{{ $input }}">
            @error('maksimal_berkas')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="maksimal_mb_per_berkas" class="{{ $label }}">Maksimal ukuran tiap berkas (MB)
                *</label>
            <input id="maksimal_mb_per_berkas" name="maksimal_mb_per_berkas" type="number" min="1"
                max="50" step="1" value="{{ $maksimalMb }}" class="{{ $input }}">
            @error('maksimal_mb_per_berkas')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <p class="text-xs text-slate-500">Waktu memakai zona {{ $zona }}.</p>

    <div>
        <p class="{{ $label }}">Jenis berkas jawaban</p>
        <div class="flex flex-wrap gap-2">
            @foreach ($pilihanEkstensi as $ekstensi)
                <label
                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 has-[:checked]:border-siakad-active has-[:checked]:bg-emerald-50 has-[:checked]:text-siakad-active">
                    <input type="checkbox" name="ekstensi_jawaban[]" value="{{ $ekstensi }}"
                        class="rounded border-slate-300 text-siakad-active" @checked(in_array($ekstensi, $ekstensiTerpilih, true))>
                    {{ strtoupper($ekstensi) }}
                </label>
            @endforeach
        </div>
        @error('ekstensi_jawaban')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <p class="text-xs text-slate-500">
        Mahasiswa dapat mengganti atau menghapus jawabannya selama batas pengumpulan belum berakhir.
    </p>
</section>

@if ($kegiatan->exists)
    <section class="{{ $kartu }}">
        <label for="alasan" class="{{ $label }}">Catatan perubahan</label>
        <textarea id="alasan" name="alasan" rows="2" maxlength="1000" placeholder="Opsional"
            class="{{ $input }}">{{ old('alasan') }}</textarea>
        @error('alasan')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </section>
@endif

<div class="flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-siakad-active px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors">
        {{ $kegiatan->exists ? 'Simpan perubahan' : 'Bagikan pembelajaran' }}
    </button>
    <a href="{{ $kegiatan->exists ? route('kegiatan.show', $kegiatan) : route('kegiatan.kelas') }}"
        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
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
