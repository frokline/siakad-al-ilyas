@csrf
@php
    $teks = static fn($v) => is_scalar($v) ? (string) $v : '';
    $rows = old(
        'sasaran',
        $item->sasaran->map(fn($s) => $s->only(['lingkup', 'program_studi_id', 'kelas_kuliah_id', 'role_id']))->all(),
    );
    $rows = is_array($rows) ? array_values(array_slice($rows, 0, 10)) : [];
    $rows = array_pad($rows, 10, []);

    // Jumlah baris sasaran yang tampil awal: sampai baris terakhir yang terisi, minimal satu.
    $terisi = 1;
    foreach ($rows as $n => $baris) {
        if (is_array($baris) && count(array_filter($baris, fn($v) => $v !== null && $v !== '')) > 0) {
            $terisi = $n + 1;
        }
    }

    $input =
        'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
    $kecil =
        'w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
    $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
@endphp

@if ($item->exists)
    <input type="hidden" name="versi" value="{{ $teks(old('versi', $item->versiForm())) }}">
@else
    <input type="hidden" name="form_token" value="{{ $teks(old('form_token', $token)) }}">
@endif

<div class="space-y-6" x-data="{ tampil: {{ $terisi }} }">
    {{-- Bagian 1: isi --}}
    <fieldset class="space-y-5">
        <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">1. Isi pengumuman</legend>

        <div>
            <label for="judul" class="{{ $label }}">Judul <span class="text-rose-500">*</span></label>
            <input id="judul" name="judul" required minlength="3" maxlength="200" class="{{ $input }}"
                value="{{ $teks(old('judul', $item->judul)) }}" placeholder="Contoh: Jadwal Ujian Tengah Semester">
            @error('judul')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="isi" class="{{ $label }}">Isi pengumuman <span
                    class="text-rose-500">*</span></label>
            <textarea id="isi" name="isi" rows="10" required minlength="3" maxlength="20000"
                class="{{ $input }}" placeholder="Tulis isi pengumuman di sini">{{ $teks(old('isi', $item->isi)) }}</textarea>
            @error('isi')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-500">Teks biasa; HTML tidak dijalankan. Periksa isi sebelum terbit
                karena pengumuman yang sudah terbit tidak dapat diedit.</p>
        </div>

        <div class="sm:max-w-xs">
            <label for="berakhir" class="{{ $label }}">Batas tayang (WITA) <span
                    class="font-normal text-slate-400">(opsional)</span></label>
            <input type="datetime-local" id="berakhir" name="berakhir_lokal" class="{{ $input }}"
                value="{{ $teks(old('berakhir_lokal', $item->berakhir_at?->setTimezone('Asia/Makassar')->format('Y-m-d\TH:i'))) }}">
            @error('berakhir_lokal')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-500">Kosong berarti tetap tayang sampai diarsipkan.</p>
        </div>
    </fieldset>

    {{-- Bagian 2: sasaran --}}
    <fieldset class="space-y-4 border-t border-slate-100 pt-6">
        <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">2. Sasaran pembaca</legend>
        <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600">Pilih 1&ndash;10 sasaran. Pengumuman terlihat
            jika pembaca cocok dengan <strong>salah satu</strong> sasaran.</p>

        @error('sasaran')
            <p class="text-xs text-rose-600">{{ $message }}</p>
        @enderror

        @foreach ($rows as $i => $row)
            @php
                $row = is_array($row) ? $row : [];
            @endphp
            @error('sasaran.' . $i)
                <p class="text-xs text-rose-600" x-show="tampil > {{ $i }}">Sasaran {{ $i + 1 }}:
                    {{ $message }}</p>
            @enderror
            <div x-show="tampil > {{ $i }}" @unless ($i < $terisi) x-cloak @endunless
                x-data="{
                    lingkup: @js($teks($row['lingkup'] ?? '')),
                    prodi: @js($teks($row['program_studi_id'] ?? '')),
                    kelas: @js($teks($row['kelas_kuliah_id'] ?? '')),
                    role: @js($teks($row['role_id'] ?? '')),
                    kosongkan() { this.lingkup = '';
                        this.prodi = '';
                        this.kelas = '';
                        this.role = ''; }
                }" x-init="$watch('lingkup', v => { if (v !== 'prodi') prodi = ''; if (v !== 'kelas') kelas = ''; })"
                class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-700">Sasaran {{ $i + 1 }}</h3>
                    <button type="button" @click="kosongkan()" x-show="lingkup || role"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700">Kosongkan</button>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="lingkup-{{ $i }}"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Lingkup</label>
                        <select id="lingkup-{{ $i }}" name="sasaran[{{ $i }}][lingkup]"
                            x-model="lingkup" class="{{ $kecil }}">
                            <option value="">Tidak digunakan</option>
                            @if ($admin)
                                <option value="kampus">Seluruh kampus</option>
                                <option value="prodi">Program studi</option>
                            @endif
                            <option value="kelas">Kelas kuliah</option>
                        </select>
                    </div>

                    <div>
                        <label for="role-{{ $i }}"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Batasi
                            peran</label>
                        <select id="role-{{ $i }}" name="sasaran[{{ $i }}][role_id]"
                            x-model="role" class="{{ $kecil }}">
                            <option value="">Semua peran yang sesuai lingkup</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ ucwords(str_replace('_', ' ', $role->kode)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($admin)
                        <div class="sm:col-span-2" x-show="lingkup === 'prodi'" x-cloak>
                            <label for="prodi-{{ $i }}"
                                class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Program
                                studi</label>
                            <select id="prodi-{{ $i }}"
                                name="sasaran[{{ $i }}][program_studi_id]" x-model="prodi"
                                class="{{ $kecil }}">
                                <option value="">Kosong</option>
                                @foreach ($prodi as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->nama }}{{ $p->aktif ? '' : ' (nonaktif)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="sm:col-span-2" x-show="lingkup === 'kelas'" x-cloak>
                        <label for="kelas-{{ $i }}"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Kelas
                            kuliah</label>
                        <select id="kelas-{{ $i }}" name="sasaran[{{ $i }}][kelas_kuliah_id]"
                            x-model="kelas" class="{{ $kecil }}">
                            <option value="">Kosong</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->kode }} &mdash;
                                    {{ $k->nama_mk_snapshot }}
                                    ({{ $k->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endforeach

        <button type="button" @click="tampil = Math.min(10, tampil + 1)" x-show="tampil < 10"
            class="inline-flex items-center gap-2 rounded-xl border border-dashed border-siakad-active px-4 py-2.5 text-sm font-semibold text-siakad-active transition hover:bg-emerald-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah sasaran
        </button>
    </fieldset>

    <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
        <button type="submit"
            class="rounded-xl bg-siakad-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/40">Simpan
            draf</button>
        <a href="{{ $item->exists ? route('pengumuman.show', $item) : route('pengumuman.index', ['mode' => 'kelola']) }}"
            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Batal</a>
    </div>
</div>
