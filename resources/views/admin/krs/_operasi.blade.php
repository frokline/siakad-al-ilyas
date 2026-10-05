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
    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700 flex items-center justify-between"
        role="alert">
        <span>Ada perubahan sejak halaman ini dibuka.</span>
        <a href="{{ route('admin.krs.show', $krs) }}" class="font-semibold underline hover:text-rose-900">Muat KRS
            terbaru</a>
        <span>sebelum mengonfirmasi tindakan kembali.</span>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach ($operasi as $kode => $label)
        @php
            $perluAlasan = in_array($kode, ['kembalikan', 'revisi', 'pulihkan', 'batalkan'], true);
            $perluJendela = in_array($kode, ['ajukan', 'revisi', 'pulihkan'], true);
            $hambatan = null;
            if (!$periodeTerbuka) {
                $hambatan = 'Riwayat studi ditutup atau periode diarsipkan.';
            } elseif ($kode !== 'batalkan') {
                if ($registrasi->status !== \App\Models\RegistrasiSemester::AKTIF || !$akunMahasiswaValid) {
                    $hambatan = 'Registrasi & akun mhs harus aktif.';
                } elseif ($registrasi->periodeAkademik->status !== 'aktif') {
                    $hambatan = 'Periode akademik harus aktif.';
                } elseif ($perluJendela && !$jendelaTerbuka) {
                    $hambatan = 'Jadwal KRS belum dibuka/telah ditutup.';
                } elseif ($kode !== 'kembalikan' && !$sumberValid) {
                    $hambatan = 'Cek kurikulum, prodi, dan paket.';
                } elseif (in_array($kode, ['ajukan', 'sahkan'], true) && !$kelasAktif) {
                    $hambatan = 'Aktifkan semua kelas paket dahulu.';
                } elseif (in_array($kode, ['revisi', 'pulihkan'], true) && !$kelasDapatDisusun) {
                    $hambatan = 'Kelas paket sudah selesai/diarsipkan.';
                }
            }
            $alasanLama = old('_form') === $kode && is_string(old('alasan')) ? old('alasan') : '';
            $isDanger = in_array($kode, ['batalkan', 'revisi'], true);
        @endphp

        <form method="POST" action="{{ route('admin.krs.' . $kode, $krs) }}"
            class="flex flex-col h-full rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden {{ $isDanger ? 'border-t-4 border-t-rose-500' : 'border-t-4 border-t-siakad-dark' }}">
            @csrf
            <input type="hidden" name="versi_form" value="{{ $versiForm }}">
            <input type="hidden" name="_form" value="{{ $kode }}">

            <div class="p-5 flex-grow flex flex-col">
                <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                    {{ $label }}
                </h3>
                <p class="text-xs text-slate-500 mb-4 flex-grow">{{ $penjelasan[$kode] }}</p>

                @if ($hambatan)
                    <div
                        class="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs font-semibold text-amber-800 flex items-start gap-2">
                        <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ $hambatan }}
                    </div>
                @endif

                <fieldset @disabled($hambatan !== null || $formLama) class="space-y-4">
                    <legend class="sr-only">Konfirmasi {{ $label }}</legend>

                    @if ($perluAlasan)
                        <div>
                            <label for="alasan-{{ $kode }}"
                                class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Alasan
                                Tindakan <span class="text-rose-500">*</span></label>
                            <textarea id="alasan-{{ $kode }}" name="alasan" rows="2" minlength="10" maxlength="2000" required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white"
                                aria-describedby="alasan-help-{{ $kode }}">{{ $alasanLama }}</textarea>
                            <p class="mt-1 text-[10px] text-slate-500" id="alasan-help-{{ $kode }}">Wajib, min
                                10 karakter.</p>
                        </div>
                    @endif

                    <label class="flex items-start gap-2.5 cursor-pointer" for="konfirmasi-{{ $kode }}">
                        <input id="konfirmasi-{{ $kode }}" type="checkbox" name="konfirmasi" value="1"
                            required
                            class="mt-0.5 h-4 w-4 rounded border-slate-300 {{ $isDanger ? 'text-rose-600 focus:ring-rose-600' : 'text-siakad-dark focus:ring-siakad-dark' }}">
                        <span class="text-xs font-medium text-slate-700 leading-snug">Saya memahami dampak tindakan ini
                            untuk seluruh paket KRS.</span>
                    </label>

                    <button type="submit"
                        class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $isDanger ? 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-600 disabled:bg-rose-300' : 'bg-siakad-dark hover:bg-emerald-800 focus:ring-siakad-dark disabled:bg-slate-300' }}">
                        Proses {{ $label }}
                    </button>
                </fieldset>
            </div>
        </form>
    @endforeach
</div>
