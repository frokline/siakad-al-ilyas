@php
    $editing = $riwayatStudi->exists;

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

            <a href="{{ route('admin.riwayat-studi.edit', $riwayatStudi) }}">
                Muat ulang formulir
            </a>
        </div>
    @enderror

    <dl class="detail-grid">
        <div>
            <dt>Mahasiswa</dt>
            <dd>
                {{ $riwayatStudi->mahasiswa->nim }}
                — {{ $riwayatStudi->mahasiswa->user->nama }}
            </dd>
        </div>

        <div>
            <dt>Program studi</dt>
            <dd>{{ $riwayatStudi->kurikulum->programStudi->nama }}</dd>
        </div>

        <div>
            <dt>Kurikulum</dt>
            <dd>
                {{ $riwayatStudi->kurikulum->kode }}
                — {{ $riwayatStudi->kurikulum->nama }}
            </dd>
        </div>

        <div>
            <dt>Angkatan</dt>
            <dd>{{ $riwayatStudi->angkatan }}</dd>
        </div>

        <div>
            <dt>Periode mulai</dt>
            <dd>{{ $riwayatStudi->periodeMulai->kode }}</dd>
        </div>
    </dl>

    <p class="help">
        Identitas studi sudah dikunci. Perpindahan program atau kurikulum
        dicatat melalui riwayat baru setelah riwayat ini ditutup.
    </p>
@else
    <div class="field">
        <label for="mahasiswa_id">Mahasiswa</label>

        <select id="mahasiswa_id" name="mahasiswa_id" required
            aria-invalid="{{ $errors->has('mahasiswa_id') ? 'true' : 'false' }}"
            aria-describedby="mahasiswa-help mahasiswa-error">
            <option value="">Pilih mahasiswa</option>

            @foreach ($daftarMahasiswa as $mahasiswa)
                <option value="{{ $mahasiswa->id }}" @selected($value('mahasiswa_id') === (string) $mahasiswa->id)>
                    {{ $mahasiswa->nim }} — {{ $mahasiswa->user->nama }}
                </option>
            @endforeach
        </select>

        <p id="mahasiswa-help" class="help">
            Menampilkan mahasiswa dengan akun aktif, peran Mahasiswa,
            dan belum memiliki riwayat studi aktif.
        </p>

        @error('mahasiswa_id')
            <p id="mahasiswa-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="kurikulum_id">Kurikulum</label>

        <select id="kurikulum_id" name="kurikulum_id" required
            aria-invalid="{{ $errors->has('kurikulum_id') ? 'true' : 'false' }}"
            aria-describedby="kurikulum-help kurikulum-error">
            <option value="">Pilih kurikulum</option>

            @foreach ($daftarKurikulum as $kurikulum)
                <option value="{{ $kurikulum->id }}" @selected($value('kurikulum_id') === (string) $kurikulum->id)>
                    {{ $kurikulum->programStudi->nama }}
                    — {{ $kurikulum->kode }}
                    — {{ $kurikulum->nama }}
                </option>
            @endforeach
        </select>

        <p id="kurikulum-help" class="help">
            Program studi mengikuti kurikulum yang dipilih.
        </p>

        @error('kurikulum_id')
            <p id="kurikulum-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid">
        <div class="field">
            <label for="angkatan">Angkatan</label>

            <input id="angkatan" name="angkatan" type="number" min="1900" max="9999" step="1" required
                value="{{ $value('angkatan', now(config('siakad.timezone'))->year) }}"
                aria-invalid="{{ $errors->has('angkatan') ? 'true' : 'false' }}" aria-describedby="angkatan-error">

            @error('angkatan')
                <p id="angkatan-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="periode_mulai_id">Periode mulai</label>

            <select id="periode_mulai_id" name="periode_mulai_id" required
                aria-invalid="{{ $errors->has('periode_mulai_id') ? 'true' : 'false' }}"
                aria-describedby="periode-mulai-error">
                <option value="">Pilih periode</option>

                @foreach ($daftarPeriode as $periode)
                    <option value="{{ $periode->id }}" @selected($value('periode_mulai_id') === (string) $periode->id)>
                        {{ $periode->kode }}
                        — mulai {{ $periode->mulai->format('d-m-Y') }}
                    </option>
                @endforeach
            </select>

            @error('periode_mulai_id')
                <p id="periode-mulai-error" class="field-error">
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <p class="help">
        Periksa mahasiswa, kurikulum, angkatan, dan periode mulai.
        Keempat data tersebut tidak dapat diubah setelah disimpan.
    </p>
@endif

<div class="field">
    <label for="dosen_pa_id">Dosen pembimbing akademik</label>

    <select id="dosen_pa_id" name="dosen_pa_id" aria-invalid="{{ $errors->has('dosen_pa_id') ? 'true' : 'false' }}"
        aria-describedby="dosen-pa-help dosen-pa-error">
        <option value="">Belum ditentukan</option>

        @foreach ($daftarDosen as $dosen)
            <option value="{{ $dosen->id }}" @selected($value('dosen_pa_id', $riwayatStudi->dosen_pa_id) === (string) $dosen->id)>
                {{ $dosen->kode_dosen }} — {{ $dosen->user->nama }}
                {{ $riwayatStudi->dosen_pa_id === $dosen->id ? '(PA saat ini)' : '' }}
            </option>
        @endforeach
    </select>

    <p id="dosen-pa-help" class="help">
        Opsional. Penugasan PA baru membutuhkan dosen dan akun aktif
        dengan peran Dosen. Persetujuan KRS tetap dilakukan admin akademik.
    </p>

    @error('dosen_pa_id')
        <p id="dosen-pa-error" class="field-error">{{ $message }}</p>
    @enderror
</div>

@if ($editing)
    <div class="form-grid">
        <div class="field">
            <label for="status">Status riwayat</label>

            <select id="status" name="status" required
                aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" aria-describedby="status-error">
                @foreach ($statusOptions as $kode => $label)
                    <option value="{{ $kode }}" @selected($value('status', $riwayatStudi->status) === $kode)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @error('status')
                <p id="status-error" class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="periode_akhir_id">Periode akhir</label>

            <select id="periode_akhir_id" name="periode_akhir_id"
                aria-invalid="{{ $errors->has('periode_akhir_id') ? 'true' : 'false' }}"
                aria-describedby="periode-akhir-help periode-akhir-error">
                <option value="">Kosongkan jika masih aktif</option>

                @foreach ($daftarPeriode as $periode)
                    <option value="{{ $periode->id }}" @selected($value('periode_akhir_id', $riwayatStudi->periode_akhir_id) === (string) $periode->id)>
                        {{ $periode->kode }}
                        — mulai {{ $periode->mulai->format('d-m-Y') }}
                    </option>
                @endforeach
            </select>

            <p id="periode-akhir-help" class="help">
                Wajib diisi untuk status selesai studi, pindah, atau keluar.
            </p>

            @error('periode_akhir_id')
                <p id="periode-akhir-error" class="field-error">
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <div class="deactivate-panel">
        <p>
            <strong>Penutupan riwayat bersifat final.</strong>
            Setelah ditutup, data riwayat hanya dapat dilihat.
        </p>

        <label class="riwayat-confirm" for="konfirmasi_penutupan">
            <input id="konfirmasi_penutupan" name="konfirmasi_penutupan" type="checkbox" value="1"
                @checked($value('konfirmasi_penutupan') === '1') aria-describedby="konfirmasi-error">

            <span>
                Saya sudah memeriksa status dan periode akhir,
                serta menyetujui penutupan riwayat ini.
            </span>
        </label>

        <p class="help">
            Konfirmasi ini diperlukan hanya jika menutup riwayat.
        </p>

        @error('konfirmasi_penutupan')
            <p id="konfirmasi-error" class="field-error">{{ $message }}</p>
        @enderror
    </div>
@endif
