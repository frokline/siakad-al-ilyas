@php
    $kolom = [
        'hari' => 'Hari',
        'jam_mulai' => 'Jam mulai',
        'jam_selesai' => 'Jam selesai',
        'berlaku_mulai' => 'Tanggal mulai',
        'berlaku_selesai' => 'Tanggal selesai',
        'metode' => 'Metode',
        'lokasi' => 'Lokasi',
        'aktif' => 'Status pola',
        'tautan_tersedia' => 'Keberadaan tautan',
        'dinonaktifkan_at' => 'Dinonaktifkan (UTC)',
    ];
    $tampil = static function (array $data, string $key): string {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return '—';
        }
        $value = $data[$key];
        if (!is_scalar($value)) {
            return '—';
        }
        return match ($key) {
            'hari' => \App\Models\JadwalKuliah::HARI[(int) $value] ?? '—',
            'metode' => \App\Models\JadwalKuliah::METODE[(string) $value] ?? '—',
            'aktif' => $value ? 'Aktif' : 'Nonaktif',
            'tautan_tersedia' => $value ? 'Tersimpan' : 'Tidak ada',
            default => (string) $value,
        };
    };
@endphp
@forelse($audits as $audit)
    @php
        $sebelum = is_array($audit->sebelum) ? $audit->sebelum : [];
        $sesudah = is_array($audit->sesudah) ? $audit->sesudah : [];
    @endphp
    <details class="jadwal-audit" @if ($loop->first) open @endif>
        <summary>
            <strong>Revisi {{ $audit->versi_entitas }} ·
                {{ \App\Models\JadwalKuliah::AKSI_AUDIT[$audit->aksi] ?? $audit->aksi }}</strong>
            <span>{{ $audit->waktu->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }} ·
                {{ $audit->pelaku?->nama ?? 'Akun tidak tersedia' }}</span>
        </summary>
        <div class="jadwal-audit-body">
            <p class="detail-multiline">{{ $audit->alasan }}</p>
            @if ($sesudah['tautan_diubah'] ?? false)
                <p class="jadwal-note">Tautan pertemuan diubah. Nilainya tidak dicantumkan dalam audit.</p>
            @endif
            <div class="table-wrap">
                <table class="jadwal-table">
                    <caption class="jadwal-sr-only">Keadaan sebelum dan sesudah revisi {{ $audit->versi_entitas }}
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">Data</th>
                            <th scope="col">Sebelum</th>
                            <th scope="col">Sesudah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kolom as $key => $label)
                            <tr @if (($sebelum[$key] ?? null) !== ($sesudah[$key] ?? null)) class="jadwal-selected" @endif>
                                <th scope="row">{{ $label }}</th>
                                <td>{{ $tampil($sebelum, $key) }}</td>
                                <td>{{ $tampil($sesudah, $key) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>
@empty
    <p class="help">Belum ada audit jadwal.</p>
@endforelse
<x-pagination :paginator="$audits" />
