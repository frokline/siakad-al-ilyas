@extends('layouts.presensi')
@section('title', 'Koreksi presensi')
@section('content')
    @php
        $teks = static function (string $key, string $fallback = ''): string {
            if (!session()->hasOldInput($key)) {
                return $fallback;
            }
            return is_string(old($key)) ? old($key) : '';
        };
    @endphp
    <div class="heading">
        <h1>Koreksi presensi</h1><a href="{{ route('presensi.show', $sesi) }}">Kembali ke pertemuan</a>
    </div>
    <section class="card">
        <h2>{{ $baris->peserta_snapshot['nama'] }}</h2>
        <p>{{ $baris->peserta_snapshot['nim'] }} · Pertemuan {{ $sesi->nomor }} · Revisi {{ $baris->revisi }}</p>
        <p>Status tersimpan saat ini: <strong>{{ $baris->labelStatus() }}</strong></p>
        <form action="{{ route('presensi.koreksi', ['pertemuan' => $sesi, 'presensi' => $baris]) }}" method="post"
            class="stack">
            @csrf @method('PATCH')
            <input type="hidden" name="versi_presensi" value="{{ $teks('versi_presensi', $baris->versiForm()) }}">
            <label for="status">Status yang benar</label>
            <select id="status" name="status" required>
                @foreach (\App\Models\Presensi::PILIHAN as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($teks('status', $baris->status) === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            <label for="catatan">Catatan presensi</label>
            <textarea id="catatan" name="catatan" maxlength="1000" rows="3">{{ $teks('catatan', $baris->catatan ?? '') }}</textarea>
            <label for="alasan">Alasan koreksi</label>
            <textarea id="alasan" name="alasan" required minlength="10" maxlength="2000" rows="3">{{ $teks('alasan') }}</textarea>
            <p class="muted">Alasan masuk ke riwayat audit. Identitas peserta dan keanggotaan kelas tetap.</p>
            <div class="buttons"><button type="submit">Simpan koreksi</button>
                <a href="{{ route('presensi.edit', ['pertemuan' => $sesi, 'presensi' => $baris]) }}">Muat ulang versi
                    terbaru</a>
            </div>
        </form>
    </section>
@endsection
