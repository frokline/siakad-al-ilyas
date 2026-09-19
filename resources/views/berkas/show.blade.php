@extends('layouts.berkas')
@section('title', $file->label)
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
        <div>
            <h1>{{ $file->label }}</h1>
            <p>{{ $file->nama_asli }}</p>
        </div><a href="{{ route('berkas.index') }}">Daftar berkas</a>
    </div>
    <section class="card">
        <span class="badge {{ $file->status }}">{{ $file->labelStatus() }}</span>
        <dl class="details">
            <dt>Ukuran</dt>
            <dd>{{ $file->ukuranLabel() }}</dd>
            <dt>Jenis</dt>
            <dd>{{ strtoupper($file->ekstensi) }}</dd>
            <dt>Diunggah</dt>
            <dd>{{ $file->created_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</dd>
            <dt>Pemeriksaan</dt>
            <dd>{{ $file->pemeriksaan === 'clamav' ? 'Format dan pemindaian antivirus' : 'Format dan ukuran' }}</dd>
            <dt>Keterangan</dt>
            <dd class="whitespace">{{ $file->keterangan ?? '—' }}</dd>
        </dl>
        @if ($file->pesan_status)
            <p class="notice">{{ $file->pesan_status }}</p>
        @endif
        <div class="buttons">
            @can('download', $file)
                <form action="{{ route('berkas.tautan', $file) }}" method="post">@csrf<button type="submit">Unduh</button>
                </form>
            @endcan
            @can('update', $file)
                <a class="button secondary" href="{{ route('berkas.edit', $file) }}">Edit keterangan</a>
            @endcan
            <a href="{{ route('berkas.show', $file) }}">Muat ulang</a>
        </div>
        @if ($file->status === \App\Models\Berkas::MENUNGGU)
            <p class="muted">Proses belum selesai. Muat ulang halaman beberapa saat lagi; hubungi pengelola jika status
                tidak berubah.</p>
        @elseif($file->status === \App\Models\Berkas::DITOLAK)
            <p><a href="{{ route('berkas.create') }}">Unggah kembali melalui formulir baru</a></p>
        @endif
    </section>
    @if (in_array($file->status, [\App\Models\Berkas::TERSEDIA, \App\Models\Berkas::DIHAPUS], true))
        @php($nonaktif = $file->status === \App\Models\Berkas::TERSEDIA)
        <section class="card">
            <h2>{{ $nonaktif ? 'Nonaktifkan berkas' : 'Pulihkan berkas' }}</h2>
            <p>{{ $nonaktif ? 'Berkas yang sudah dipakai di modul lain tidak dapat dinonaktifkan. Riwayat dan isi file tetap disimpan.' : 'Aktifkan kembali berkas agar dapat diunduh.' }}
            </p>
            <form action="{{ $nonaktif ? route('berkas.nonaktifkan', $file) : route('berkas.pulihkan', $file) }}"
                method="post" class="stack">
                @csrf<input type="hidden" name="versi" value="{{ $teks('versi', $file->versiForm()) }}">
                <label for="alasan">Alasan</label>
                <textarea id="alasan" name="alasan" rows="3" required minlength="10" maxlength="2000">{{ $teks('alasan') }}</textarea>
                <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya mengonfirmasi
                    tindakan ini.</label>
                <button type="submit">{{ $nonaktif ? 'Nonaktifkan' : 'Pulihkan' }}</button>
            </form>
        </section>
    @endif
    <section class="card">
        <h2>Riwayat perubahan</h2>
        <div class="table-wrap">
            <table>
                <caption class="sr-only">Audit perubahan berkas</caption>
                <thead>
                    <tr>
                        <th scope="col">Revisi / waktu</th>
                        <th scope="col">Pelaku / tindakan</th>
                        <th scope="col">Perubahan</th>
                        <th scope="col">Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audit as $log)
                        <tr>
                            <td>{{ $log->versi_entitas }}<span
                                    class="sub">{{ $log->waktu->setTimezone($zona)->format('d-m-Y H:i:s') }}</span></td>
                            <td>{{ $log->pelaku?->nama ?? 'Sistem' }}<span
                                    class="sub">{{ str_replace('_', ' ', $log->aksi) }}</span></td>
                            <td>{{ \App\Models\Berkas::STATUS[$log->sebelum['status'] ?? ''] ?? '—' }} →
                                {{ \App\Models\Berkas::STATUS[$log->sesudah['status'] ?? ''] ?? '—' }}
                                @if (($log->sebelum['label'] ?? null) !== ($log->sesudah['label'] ?? null))
                                    <span class="sub">Label: {{ $log->sebelum['label'] ?? '—' }} →
                                        {{ $log->sesudah['label'] ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="whitespace">{{ $log->alasan ?? '—' }}</td>
                        </tr>
                    @empty<tr>
                            <td colspan="4">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('berkas._pagination', ['paginator' => $audit])
    </section>
@endsection
