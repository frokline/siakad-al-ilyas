@extends('layouts.presensi')
@section('title', 'Presensi pertemuan ' . $sesi->nomor)
@section('content')
    @php
        $teks = static function (string $form, string $key, string $fallback = ''): string {
            if (old('form_presensi') !== $form || !session()->hasOldInput($key)) {
                return $fallback;
            }
            return is_string(old($key)) ? old($key) : '';
        };
    @endphp
    <div class="heading">
        <div>
            <h1>Pertemuan {{ $sesi->nomor }}</h1>
            <p>{{ $sesi->kelasKuliah->kode }} · {{ $sesi->kelasKuliah->nama_mk_snapshot }}</p>
        </div>
        <a class="button secondary" href="{{ route('presensi.index') }}">Daftar pertemuan</a>
    </div>
    <section class="card">
        <h2>{{ $sesi->topik }}</h2>
        <p>Rencana:
            {{ $sesi->mulai_rencana->setTimezone($zona)->format('d-m-Y H:i') }}–{{ $sesi->selesai_rencana->setTimezone($zona)->format('H:i') }}
            ({{ $zona }})</p>
        <p>Status pertemuan: <strong>{{ ucfirst($sesi->status) }}</strong></p>
        @if ($sesi->mulai_aktual)
            <p>Mulai aktual: {{ $sesi->mulai_aktual->setTimezone($zona)->format('d-m-Y H:i:s') }}</p>
        @endif
        @if ($sesi->selesai_aktual)
            <p>Selesai aktual: {{ $sesi->selesai_aktual->setTimezone($zona)->format('d-m-Y H:i:s') }}</p>
        @endif
        <a href="{{ route('presensi.show', $sesi) }}">Muat ulang data terbaru</a>
    </section>

    @if ($sesi->status === \App\Models\Pertemuan::TERJADWAL && $bolehJalankan)
        <section class="card">
            <h2>Mulai pertemuan</h2>
            <p>Pertemuan dapat dimulai dalam rentang waktu rencana.</p>
            <form action="{{ route('presensi.mulai', $sesi) }}" method="post" class="stack">
                @csrf
                <input type="hidden" name="form_presensi" value="mulai">
                <input type="hidden" name="versi_pertemuan" value="{{ $teks('mulai', 'versi_pertemuan', $versiSesi) }}">
                <label for="alasan-mulai">Catatan mulai</label>
                <textarea id="alasan-mulai" name="alasan" required minlength="10" maxlength="2000" rows="2">{{ $teks('mulai', 'alasan') }}</textarea>
                <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya siap memulai
                    pertemuan ini.</label>
                <button type="submit">Mulai pertemuan</button>
            </form>
        </section>
    @endif

    @if ($daftar === null)
        <section class="card">
            <h2>Daftar peserta belum disiapkan</h2>
            @if ($bolehCatat)
                <p>Peserta diambil dari KRS yang disahkan, dengan registrasi semester dan riwayat studi aktif saat tombol
                    ini ditekan. Pastikan pengesahan KRS sudah selesai.</p>
                <form action="{{ route('presensi.siapkan', $sesi) }}" method="post" class="stack">
                    @csrf<input type="hidden" name="form_presensi" value="siapkan">
                    <input type="hidden" name="versi_pertemuan"
                        value="{{ $teks('siapkan', 'versi_pertemuan', $versiSiapkan) }}">
                    <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya sudah
                        memeriksa kesiapan daftar peserta.</label>
                    <button type="submit">Siapkan daftar peserta</button>
                </form>
            @else
                <p>Penyiapan daftar tersedia saat pertemuan berlangsung dalam kelas dan periode aktif.</p>
            @endif
        </section>
    @else
        <section class="card">
            <h2>Daftar presensi: {{ ucfirst($daftar->status) }}</h2>
            <p>{{ $daftar->jumlah_peserta }} peserta · Dibuka oleh {{ $daftar->pembuka?->nama ?? '—' }} pada
                {{ $daftar->dibuka_at->setTimezone($zona)->format('d-m-Y H:i:s') }}.</p>
            @if ($daftar->ditutup_at)
                <p>Ditutup oleh {{ $daftar->penutup?->nama ?? '—' }} pada
                    {{ $daftar->ditutup_at->setTimezone($zona)->format('d-m-Y H:i:s') }}.</p>
            @endif
            <p class="muted">Nama dan NIM menggunakan data saat daftar disiapkan. Perubahan KRS sesudahnya tidak menghapus
                riwayat ini.</p>
            <div class="stats">
                @foreach (\App\Models\Presensi::STATUS as $status => $label)
                    <div class="stat"><strong>{{ $ringkasan[$status] }}</strong><span>{{ $label }}</span></div>
                @endforeach
            </div>
            <p>Hadir: {{ $ringkasan['hadir'] }}/{{ $daftar->jumlah_peserta }} peserta
                ({{ number_format((100 * $ringkasan['hadir']) / $daftar->jumlah_peserta, 1, ',', '.') }}%). Belum dicatat
                tetap dihitung dalam jumlah peserta.</p>
        </section>
        <form method="get" action="{{ route('presensi.show', $sesi) }}" class="card filters">
            <label>Status peserta
                <select name="status">
                    <option value="">Semua peserta</option>
                    @foreach (\App\Models\Presensi::STATUS as $status => $label)
                        <option value="{{ $status }}" @selected(($filter['status'] ?? '') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit">Terapkan</button><a href="{{ route('presensi.show', $sesi) }}">Reset</a>
        </form>
        <div class="card table-wrap">
            <table>
                <caption class="sr-only">Peserta pertemuan {{ $sesi->nomor }}</caption>
                <thead>
                    <tr>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Status / catatan</th>
                        <th scope="col">Pencatatan</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baris as $row)
                        <tr id="peserta-{{ $row->id }}">
                            <td><strong>{{ $row->peserta_snapshot['nama'] }}</strong><span
                                    class="sub">{{ $row->peserta_snapshot['nim'] }}</span></td>
                            <td><span class="badge {{ $row->status }}">{{ $row->labelStatus() }}</span><span
                                    class="sub whitespace">{{ $row->catatan ?? '—' }}</span></td>
                            <td>{{ $row->pencatat?->nama ?? '—' }}<span
                                    class="sub">{{ $row->dicatat_at?->setTimezone($zona)->format('d-m-Y H:i:s') ?? 'Belum ada pencatatan' }}</span>
                            </td>
                            <td>
                                @if ($bolehCatat && $row->status === \App\Models\Presensi::BELUM)
                                    <form
                                        action="{{ route('presensi.catat', ['pertemuan' => $sesi, 'presensi' => $row]) }}"
                                        method="post" class="row-form">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="form_presensi" value="baris-{{ $row->id }}">
                                        <input type="hidden" name="versi_presensi"
                                            value="{{ $teks('baris-' . $row->id, 'versi_presensi', $row->versiForm()) }}">
                                        <input type="hidden" name="halaman" value="{{ $baris->currentPage() }}">
                                        <input type="hidden" name="filter_status" value="{{ $filter['status'] ?? '' }}">
                                        <label class="sr-only" for="catatan-{{ $row->id }}">Catatan untuk
                                            {{ $row->peserta_snapshot['nama'] }}</label>
                                        <input id="catatan-{{ $row->id }}" name="catatan" maxlength="1000"
                                            placeholder="Catatan opsional"
                                            value="{{ $teks('baris-' . $row->id, 'catatan') }}">
                                        <div class="buttons">
                                            @foreach (\App\Models\Presensi::PILIHAN as $status => $label)
                                                <button type="submit" name="status" value="{{ $status }}"
                                                    class="pilih {{ $status }}">{{ $label }}</button>
                                            @endforeach
                                        </div>
                                    </form>
                                @elseif($bolehKoreksi && $row->status !== \App\Models\Presensi::BELUM)
                                    <a
                                        href="{{ route('presensi.edit', ['pertemuan' => $sesi, 'presensi' => $row]) }}">Koreksi</a>
                                @endif
                                <a class="sub"
                                    href="{{ route('presensi.audit', ['pertemuan' => $sesi, 'presensi' => $row]) }}">Riwayat
                                    pencatatan</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Tidak ada peserta pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('presensi._pagination', ['paginator' => $baris])

        @if ($bolehCatat && $daftar->status === \App\Models\PresensiPertemuan::TERBUKA)
            <section class="card">
                <h2>Tutup presensi</h2>
                @if ($ringkasan[\App\Models\Presensi::BELUM] > 0)
                    <p>{{ $ringkasan[\App\Models\Presensi::BELUM] }} peserta belum dicatat. Periksa seluruh halaman dan
                        filter.</p>
                @else
                    <form action="{{ route('presensi.tutup', $sesi) }}" method="post" class="stack">
                        @csrf<input type="hidden" name="form_presensi" value="tutup">
                        <input type="hidden" name="versi_daftar"
                            value="{{ $teks('tutup', 'versi_daftar', $versiTutup) }}">
                        <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Seluruh
                            peserta sudah saya periksa. Koreksi setelah ditutup dilakukan oleh admin akademik.</label>
                        <button type="submit">Tutup presensi</button>
                    </form>
                @endif
            </section>
        @endif
        @if (
            $bolehJalankan &&
                $sesi->status === \App\Models\Pertemuan::BERLANGSUNG &&
                $daftar->status === \App\Models\PresensiPertemuan::DITUTUP)
            <section class="card">
                <h2>Selesaikan pertemuan</h2>
                <form action="{{ route('presensi.selesai', $sesi) }}" method="post" class="stack">
                    @csrf<input type="hidden" name="form_presensi" value="selesai">
                    <input type="hidden" name="versi_pertemuan"
                        value="{{ $teks('selesai', 'versi_pertemuan', $versiSesi) }}">
                    <label for="realisasi">Realisasi perkuliahan</label>
                    <textarea id="realisasi" name="realisasi" rows="4" required minlength="10" maxlength="20000">{{ $teks('selesai', 'realisasi') }}</textarea>
                    <label for="alasan-selesai">Catatan penyelesaian</label>
                    <textarea id="alasan-selesai" name="alasan" rows="2" required minlength="10" maxlength="2000">{{ $teks('selesai', 'alasan') }}</textarea>
                    <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Pertemuan
                        ini sudah selesai dilaksanakan.</label>
                    <button type="submit">Selesaikan pertemuan</button>
                </form>
            </section>
        @endif
    @endif
@endsection
