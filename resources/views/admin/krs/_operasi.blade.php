@php
    $penjelasan = [
        'ajukan' => 'Mengirim seluruh paket untuk diperiksa admin akademik. Semua kelas paket harus aktif.',
        'sahkan' => 'Mengesahkan seluruh paket dan mengaktifkan keikutsertaan mahasiswa pada semua kelasnya.',
        'kembalikan' => 'Mengembalikan pengajuan ke draf. Pengajuan ulang mengikuti jadwal pengisian KRS.',
        'revisi' => 'Membuka draf revisi. Keikutsertaan kelas berhenti sementara sampai KRS disahkan kembali.',
        'pulihkan' =>
            'Memulihkan KRS ke draf. Seluruh detail lama dipertahankan dan perlu diajukan serta disahkan kembali.',
        'batalkan' => 'Membatalkan seluruh paket dan keikutsertaan kelas. Riwayat perubahan tetap disimpan.',
    ];
    $formLama = $errors->has('versi_form');
    $akunMahasiswaValid = $registrasi->riwayatStudi->mahasiswa->user->hasRole(\App\Models\Role::MAHASISWA);
    $sumberValid =
        $registrasi->riwayatStudi->kurikulum->status === \App\Models\Kurikulum::AKTIF &&
        $registrasi->riwayatStudi->kurikulum->programStudi->aktif &&
        in_array(
            $registrasi->rombel->paketSemester->status,
            [\App\Models\PaketSemester::DITERBITKAN, \App\Models\PaketSemester::ARSIP],
            true,
        );
    $kelasAktif =
        $krs->details->isNotEmpty() &&
        $krs->details->every(fn($detail) => $detail->kelasKuliah->status === \App\Models\KelasKuliah::AKTIF);
    $kelasDapatDisusun =
        $krs->details->isNotEmpty() &&
        $krs->details->every(
            fn($detail) => in_array(
                $detail->kelasKuliah->status,
                [\App\Models\KelasKuliah::PERSIAPAN, \App\Models\KelasKuliah::AKTIF],
                true,
            ),
        );
@endphp

@if ($formLama)
    <div class="alert alert-error" role="alert">
        Ada perubahan sejak halaman ini dibuka.
        <a href="{{ route('admin.krs.show', $krs) }}">Muat KRS terbaru</a>
        sebelum mengonfirmasi tindakan kembali.
    </div>
@endif

<div class="krs-operasi-grid">
    @foreach ($operasi as $kode => $label)
        @php
            $perluAlasan = in_array($kode, ['kembalikan', 'revisi', 'pulihkan', 'batalkan'], true);
            $perluJendela = in_array($kode, ['ajukan', 'revisi', 'pulihkan'], true);
            $hambatan = null;
            if (!$periodeTerbuka) {
                $hambatan = 'Riwayat studi sudah ditutup atau periode sudah diarsipkan.';
            } elseif ($kode !== 'batalkan') {
                if ($registrasi->status !== \App\Models\RegistrasiSemester::AKTIF || !$akunMahasiswaValid) {
                    $hambatan = 'Registrasi dan akun mahasiswa harus aktif.';
                } elseif ($registrasi->periodeAkademik->status !== 'aktif') {
                    $hambatan = 'Periode akademik harus aktif.';
                } elseif ($perluJendela && !$jendelaTerbuka) {
                    $hambatan = 'Jadwal pengisian KRS belum dibuka atau sudah ditutup.';
                } elseif ($kode !== 'kembalikan' && !$sumberValid) {
                    $hambatan = 'Periksa status kurikulum, program studi, dan paket semester.';
                } elseif (in_array($kode, ['ajukan', 'sahkan'], true) && !$kelasAktif) {
                    $hambatan = 'Aktifkan seluruh kelas paket sebelum melanjutkan.';
                } elseif (in_array($kode, ['revisi', 'pulihkan'], true) && !$kelasDapatDisusun) {
                    $hambatan = 'Kelas paket sudah selesai atau diarsipkan.';
                }
            }
            $alasanLama = old('_form') === $kode && is_string(old('alasan')) ? old('alasan') : '';
        @endphp
        <form class="krs-operasi" method="POST" action="{{ route('admin.krs.' . $kode, $krs) }}">
            @csrf
            <input type="hidden" name="versi_form" value="{{ $versiForm }}">
            <input type="hidden" name="_form" value="{{ $kode }}">
            <h3>{{ $label }}</h3>
            <p class="help">{{ $penjelasan[$kode] }}</p>

            @if ($hambatan)
                <p class="krs-hambatan">{{ $hambatan }}</p>
            @endif

            <fieldset @disabled($hambatan !== null || $formLama)>
                <legend class="krs-sr-only">Konfirmasi {{ $label }}</legend>
                @if ($perluAlasan)
                    <div class="field">
                        <label for="alasan-{{ $kode }}">Alasan tindakan</label>
                        <textarea id="alasan-{{ $kode }}" name="alasan" rows="3" minlength="10" maxlength="2000" required
                            aria-describedby="alasan-help-{{ $kode }}">{{ $alasanLama }}</textarea>
                        <p class="help" id="alasan-help-{{ $kode }}">Wajib, 10–2.000 karakter. Tersimpan
                            dalam riwayat perubahan.</p>
                    </div>
                @endif

                <label class="krs-konfirmasi" for="konfirmasi-{{ $kode }}">
                    <input id="konfirmasi-{{ $kode }}" type="checkbox" name="konfirmasi" value="1"
                        required>
                    <span>Saya memahami dampak tindakan ini untuk seluruh paket KRS.</span>
                </label>
                <button class="button {{ in_array($kode, ['batalkan', 'revisi'], true) ? 'danger' : '' }}"
                    type="submit">
                    {{ $label }}
                </button>
            </fieldset>
        </form>
    @endforeach
</div>
