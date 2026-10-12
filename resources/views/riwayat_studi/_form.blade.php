{{--
    Bagian formulir Riwayat Studi (dipakai create & edit).
    Nama field TIDAK diubah: mahasiswa_id, kurikulum_id, angkatan, periode_mulai_id,
    dosen_pa_id, status, periode_akhir_id, konfirmasi_penutupan, version.
--}}
@php
    $editing = $riwayatStudi->exists;

    $value = static function (string $key, mixed $default = ''): string {
        $input = old($key, $default);

        return is_scalar($input) ? (string) $input : '';
    };

    $dasar =
        'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2';
    $kelasInput = fn(string $field) => $dasar .
        ' ' .
        ($errors->has($field)
            ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200'
            : 'border-slate-300 focus:border-siakad-dark focus:ring-siakad-dark/20');

    $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
    $kepala = 'rounded-t-2xl border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
    $judul = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
    $label = 'mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500';
    $bantuan = 'mt-1.5 text-xs leading-5 text-slate-500';
    $galat = 'mt-1.5 text-xs font-medium text-rose-600';
@endphp

@if ($editing)
    <input type="hidden" name="version" value="{{ $value('version', $version) }}">

    @error('version')
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="alert">
            <p class="font-semibold">{{ $message }}</p>
            <a href="{{ route('admin.riwayat-studi.edit', $riwayatStudi) }}"
                class="mt-1 inline-block font-semibold underline">Muat ulang formulir</a>
        </div>
    @enderror

    {{-- Identitas terkunci --}}
    <section class="{{ $kartu }}">
        <div class="{{ $kepala }} flex items-center justify-between">
            <h2 class="{{ $judul }}">Identitas studi</h2>
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Terkunci
            </span>
        </div>
        <dl class="grid gap-x-8 gap-y-5 p-6 sm:grid-cols-2">
            <div>
                <dt class="{{ $label }}">Mahasiswa</dt>
                <dd class="text-sm font-semibold text-slate-900">{{ $riwayatStudi->mahasiswa->nim }} —
                    {{ $riwayatStudi->mahasiswa->user->nama }}</dd>
            </div>
            <div>
                <dt class="{{ $label }}">Program studi</dt>
                <dd class="text-sm text-slate-800">{{ $riwayatStudi->kurikulum->programStudi->nama }}</dd>
            </div>
            <div>
                <dt class="{{ $label }}">Kurikulum</dt>
                <dd class="text-sm text-slate-800">{{ $riwayatStudi->kurikulum->kode }} —
                    {{ $riwayatStudi->kurikulum->nama }}</dd>
            </div>
            <div>
                <dt class="{{ $label }}">Angkatan · Periode mulai</dt>
                <dd class="text-sm text-slate-800">{{ $riwayatStudi->angkatan }} ·
                    {{ $riwayatStudi->periodeMulai->kode }}</dd>
            </div>
        </dl>
        <p class="border-t border-slate-100 px-6 py-4 text-xs leading-5 text-slate-500">
            Identitas studi sudah dikunci. Perpindahan program atau kurikulum dicatat melalui riwayat baru setelah
            riwayat ini ditutup.
        </p>
    </section>
@else
    {{-- Data studi baru --}}
    <section class="{{ $kartu }}">
        <div class="{{ $kepala }}">
            <h2 class="{{ $judul }}">Data studi</h2>
        </div>
        <div class="grid gap-5 p-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="mahasiswa_id" class="{{ $label }}">Mahasiswa <span
                        class="text-rose-500">*</span></label>
                <select id="mahasiswa_id" name="mahasiswa_id" required class="{{ $kelasInput('mahasiswa_id') }}"
                    aria-invalid="{{ $errors->has('mahasiswa_id') ? 'true' : 'false' }}">
                    <option value="">Pilih mahasiswa</option>
                    @foreach ($daftarMahasiswa as $mahasiswa)
                        <option value="{{ $mahasiswa->id }}" @selected($value('mahasiswa_id') === (string) $mahasiswa->id)>
                            {{ $mahasiswa->nim }} — {{ $mahasiswa->user->nama }}
                        </option>
                    @endforeach
                </select>
                <p class="{{ $bantuan }}">Hanya mahasiswa dengan akun aktif, peran Mahasiswa, dan belum memiliki
                    riwayat studi aktif.</p>
                @error('mahasiswa_id')
                    <p class="{{ $galat }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="kurikulum_id" class="{{ $label }}">Kurikulum <span
                        class="text-rose-500">*</span></label>
                <select id="kurikulum_id" name="kurikulum_id" required class="{{ $kelasInput('kurikulum_id') }}"
                    aria-invalid="{{ $errors->has('kurikulum_id') ? 'true' : 'false' }}">
                    <option value="">Pilih kurikulum</option>
                    @foreach ($daftarKurikulum as $kurikulum)
                        <option value="{{ $kurikulum->id }}" @selected($value('kurikulum_id') === (string) $kurikulum->id)>
                            {{ $kurikulum->programStudi->nama }} — {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
                        </option>
                    @endforeach
                </select>
                <p class="{{ $bantuan }}">Program studi mengikuti kurikulum yang dipilih.</p>
                @error('kurikulum_id')
                    <p class="{{ $galat }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="angkatan" class="{{ $label }}">Angkatan <span class="text-rose-500">*</span></label>
                <input id="angkatan" name="angkatan" type="number" min="1900" max="9999" step="1"
                    required value="{{ $value('angkatan', now(config('siakad.timezone'))->year) }}"
                    class="{{ $kelasInput('angkatan') }}"
                    aria-invalid="{{ $errors->has('angkatan') ? 'true' : 'false' }}">
                @error('angkatan')
                    <p class="{{ $galat }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="periode_mulai_id" class="{{ $label }}">Periode mulai <span
                        class="text-rose-500">*</span></label>
                <select id="periode_mulai_id" name="periode_mulai_id" required
                    class="{{ $kelasInput('periode_mulai_id') }}"
                    aria-invalid="{{ $errors->has('periode_mulai_id') ? 'true' : 'false' }}">
                    <option value="">Pilih periode</option>
                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode->id }}" @selected($value('periode_mulai_id') === (string) $periode->id)>
                            {{ $periode->kode }} — mulai {{ $periode->mulai->format('d-m-Y') }}
                        </option>
                    @endforeach
                </select>
                @error('periode_mulai_id')
                    <p class="{{ $galat }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <p class="border-t border-slate-100 px-6 py-4 text-xs leading-5 text-slate-500">
            Periksa mahasiswa, kurikulum, angkatan, dan periode mulai. Keempat data tersebut tidak dapat diubah setelah
            disimpan.
        </p>
    </section>
@endif

{{-- Pembimbing akademik --}}
<section class="{{ $kartu }}">
    <div class="{{ $kepala }}">
        <h2 class="{{ $judul }}">Dosen pembimbing akademik</h2>
    </div>
    <div class="p-6">
        <label for="dosen_pa_id" class="{{ $label }}">Dosen PA</label>
        <select id="dosen_pa_id" name="dosen_pa_id" class="{{ $kelasInput('dosen_pa_id') }}"
            aria-invalid="{{ $errors->has('dosen_pa_id') ? 'true' : 'false' }}">
            <option value="">Belum ditentukan</option>
            @foreach ($daftarDosen as $dosen)
                <option value="{{ $dosen->id }}" @selected($value('dosen_pa_id', $riwayatStudi->dosen_pa_id) === (string) $dosen->id)>
                    {{ $dosen->kode_dosen }} — {{ $dosen->user->nama }}
                    {{ $riwayatStudi->dosen_pa_id === $dosen->id ? '(PA saat ini)' : '' }}
                </option>
            @endforeach
        </select>
        <p class="{{ $bantuan }}">Opsional. Penugasan PA baru membutuhkan dosen dan akun aktif dengan peran Dosen.
            Persetujuan KRS tetap dilakukan admin akademik.</p>
        @error('dosen_pa_id')
            <p class="{{ $galat }}">{{ $message }}</p>
        @enderror
    </div>
</section>

@if ($editing)
    {{-- Status & penutupan --}}
    <section class="{{ $kartu }}" x-data="{
        status: @js($value('status', $riwayatStudi->status)),
        akhir: @js($value('periode_akhir_id', $riwayatStudi->periode_akhir_id)),
        get menutup() { return this.status !== @js(\App\Models\RiwayatStudi::AKTIF); }
    }" x-effect="if (!menutup) { akhir = '' }">
        <div class="{{ $kepala }}">
            <h2 class="{{ $judul }}">Status & penutupan</h2>
        </div>
        <div class="grid gap-5 p-6 sm:grid-cols-2">
            <div>
                <label for="status" class="{{ $label }}">Status riwayat <span
                        class="text-rose-500">*</span></label>
                <select id="status" name="status" required x-model="status" class="{{ $kelasInput('status') }}"
                    aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}">
                    @foreach ($statusOptions as $kode => $labelStatus)
                        <option value="{{ $kode }}">{{ $labelStatus }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="{{ $galat }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="periode_akhir_id" class="{{ $label }}">Periode akhir
                    <span class="text-rose-500" x-show="menutup" x-cloak>*</span></label>
                <select id="periode_akhir_id" name="periode_akhir_id" x-model="akhir" :required="menutup"
                    :class="menutup ? '' : 'opacity-60'" class="{{ $kelasInput('periode_akhir_id') }}"
                    aria-invalid="{{ $errors->has('periode_akhir_id') ? 'true' : 'false' }}">
                    <option value="">Kosongkan jika masih aktif</option>
                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->kode }} — mulai
                            {{ $periode->mulai->format('d-m-Y') }}</option>
                    @endforeach
                </select>
                <p class="{{ $bantuan }}">Wajib diisi untuk status selesai studi, pindah, atau keluar.</p>
                @error('periode_akhir_id')
                    <p class="{{ $galat }}">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Peringatan penutupan: hanya muncul bila status bukan Aktif --}}
        <div x-show="menutup" x-cloak x-collapse>
            <div class="mx-6 mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4">
                <p class="text-sm font-bold text-rose-800">Penutupan riwayat bersifat final.</p>
                <p class="mt-1 text-sm text-rose-700">Setelah ditutup, data riwayat hanya dapat dilihat.</p>

                <label for="konfirmasi_penutupan" class="mt-3 flex cursor-pointer items-start gap-3">
                    <input id="konfirmasi_penutupan" name="konfirmasi_penutupan" type="checkbox" value="1"
                        :required="menutup" :disabled="!menutup" @checked($value('konfirmasi_penutupan') === '1')
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-300">
                    <span class="text-sm text-rose-900">Saya sudah memeriksa status dan periode akhir, serta menyetujui
                        penutupan riwayat ini.</span>
                </label>
                @error('konfirmasi_penutupan')
                    <p class="{{ $galat }}">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>
@endif
