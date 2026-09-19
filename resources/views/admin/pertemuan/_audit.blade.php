@php
    $kolom = [
        'nomor' => 'Nomor',
        'jenis' => 'Jenis',
        'topik' => 'Topik',
        'rencana' => 'Rencana',
        'realisasi' => 'Realisasi',
        'mulai_rencana' => 'Mulai rencana (UTC)',
        'selesai_rencana' => 'Selesai rencana (UTC)',
        'mulai_aktual' => 'Mulai aktual (UTC)',
        'selesai_aktual' => 'Selesai aktual (UTC)',
        'metode' => 'Metode',
        'lokasi' => 'Lokasi',
        'status' => 'Status',
        'tautan_tersedia' => 'Keberadaan tautan',
        'jadwal_kuliah_id' => 'Pola sumber',
        'pengajar_kelas_id' => 'Penanggung jawab',
    ];
    $nilai = static function (array $data, string $key): string {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return '—';
        }
        $value = $data[$key];
        if (!is_scalar($value)) {
            return '—';
        }
        return match ($key) {
            'jenis' => \App\Models\Pertemuan::JENIS[(string) $value] ?? (string) $value,
            'status' => \App\Models\Pertemuan::STATUS[(string) $value] ?? (string) $value,
            'metode' => \App\Models\JadwalKuliah::METODE[(string) $value] ?? (string) $value,
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
    <details class="pertemuan-audit" @if ($loop->first) open @endif>
        <summary><strong>Revisi {{ $audit->versi_entitas }} ·
                {{ \App\Models\Pertemuan::AKSI[$audit->aksi] ?? $audit->aksi }}</strong><span>{{ $audit->waktu->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
                · {{ $audit->pelaku?->nama ?? 'Akun tidak tersedia' }}</span></summary>
        <div class="pertemuan-audit-body">
            <p class="detail-multiline">{{ $audit->alasan }}</p>
            @if ($sesudah['tautan_diubah'] ?? false)
                <p class="pertemuan-note">Tautan diubah. Nilainya tidak dicantumkan dalam audit.</p>
            @endif
            <div class="table-wrap">
                <table class="pertemuan-table">
                    <caption class="pertemuan-sr-only">Perubahan revisi {{ $audit->versi_entitas }}</caption>
                    <thead>
                        <tr>
                            <th scope="col">Data</th>
                            <th scope="col">Sebelum</th>
                            <th scope="col">Sesudah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kolom as $key => $label)
                            <tr @if (($sebelum[$key] ?? null) !== ($sesudah[$key] ?? null)) class="pertemuan-selected" @endif>
                                <th scope="row">{{ $label }}</th>
                                <td>{{ $nilai($sebelum, $key) }}</td>
                                <td>{{ $nilai($sesudah, $key) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>
@empty
    <p class="help">Belum ada audit pertemuan.</p>
@endforelse
<x-pagination :paginator="$audits" />
