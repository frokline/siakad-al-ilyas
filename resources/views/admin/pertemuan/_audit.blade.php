@php
    $zona = config('siakad.timezone', 'Asia/Makassar');
    $kolom = [
        'nomor' => 'Nomor',
        'jenis' => 'Jenis',
        'topik' => 'Topik',
        'rencana' => 'Rencana',
        'realisasi' => 'Realisasi',
        'mulai_rencana' => 'Mulai rencana',
        'selesai_rencana' => 'Selesai rencana',
        'mulai_aktual' => 'Mulai aktual',
        'selesai_aktual' => 'Selesai aktual',
        'metode' => 'Metode',
        'lokasi' => 'Lokasi',
        'status' => 'Status',
        'tautan_tersedia' => 'Keberadaan tautan',
        'jadwal_kuliah_id' => 'Pola sumber (ID)',
        'pengajar_kelas_id' => 'Penanggung jawab',
    ];
    $kolomWaktu = ['mulai_rencana', 'selesai_rencana', 'mulai_aktual', 'selesai_aktual'];
    $nilai = static function (array $data, string $key) use ($zona, $kolomWaktu): string {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return '—';
        }
        $value = $data[$key];
        if (!is_scalar($value)) {
            return '—';
        }
        if (in_array($key, $kolomWaktu, true)) {
            // Audit menyimpan waktu dalam UTC; tampilkan dalam zona waktu aplikasi seperti halaman lain.
            try {
                return \Carbon\CarbonImmutable::parse((string) $value, 'UTC')
                    ->setTimezone($zona)
                    ->format('d-m-Y H:i:s');
            } catch (\Throwable) {
                return (string) $value;
            }
        }
        if ($key === 'pengajar_kelas_id') {
            $nama = is_array($data['pengajar_snapshot'] ?? null) ? $data['pengajar_snapshot']['nama'] ?? null : null;
            return is_string($nama) && $nama !== '' ? $nama : '#' . $value;
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

<div class="space-y-4">
    @forelse($audits as $audit)
        <?php
        $sebelum = is_array($audit->sebelum) ? $audit->sebelum : [];
        $sesudah = is_array($audit->sesudah) ? $audit->sesudah : [];
        ?>
        <details class="group rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden"
            @if ($loop->first) open @endif>
            <summary
                class="flex cursor-pointer items-center justify-between bg-slate-50 px-5 py-4 hover:bg-slate-100 transition-colors">
                <div class="flex flex-col gap-1">
                    <strong class="text-sm font-bold text-slate-800">
                        Revisi {{ $audit->versi_entitas }} &middot;
                        {{ \App\Models\Pertemuan::AKSI[$audit->aksi] ?? $audit->aksi }}
                    </strong>
                    <span class="text-xs text-slate-500">
                        {{ $audit->waktu->setTimezone($zona)->format('d-m-Y H:i:s') }}
                        &middot; {{ $audit->pelaku?->nama ?? 'Akun tidak tersedia' }}
                    </span>
                </div>
                <span class="text-slate-400 transition-transform duration-200 group-open:rotate-180">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </summary>

            <div class="border-t border-slate-200 p-5">
                <div class="mb-4">
                    <span class="font-bold text-slate-700 text-sm block mb-1">Alasan:</span>
                    <p
                        class="text-slate-600 text-sm whitespace-pre-line bg-slate-50 p-3 rounded-lg border border-slate-100">
                        {{ $audit->alasan }}</p>
                </div>

                @if ($sesudah['tautan_diubah'] ?? false)
                    <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 p-3 text-xs text-blue-700">
                        Tautan pertemuan diubah. Nilainya tidak dicantumkan dalam log audit demi keamanan.
                    </div>
                @endif

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left text-sm text-slate-600 border-collapse">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-semibold">Data</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Sebelum</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Sesudah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($kolom as $key => $label)
                                <?php $berubah = ($sebelum[$key] ?? null) !== ($sesudah[$key] ?? null); ?>
                                <tr class="{{ $berubah ? 'bg-amber-50/30' : '' }}">
                                    <th scope="row" class="px-4 py-3 font-medium text-slate-700 whitespace-nowrap">
                                        {{ $label }}</th>
                                    <td class="px-4 py-3 {{ $berubah ? 'line-through text-slate-400' : '' }}">
                                        {{ $nilai($sebelum, $key) }}
                                    </td>
                                    <td class="px-4 py-3 {{ $berubah ? 'font-semibold text-emerald-700' : '' }}">
                                        {{ $nilai($sesudah, $key) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @empty
        <div
            class="text-center py-8 text-slate-500 text-sm border border-dashed border-slate-300 rounded-xl bg-slate-50">
            Belum ada log audit riwayat pertemuan.
        </div>
    @endforelse
</div>
<div class="mt-6">
    <x-pagination :paginator="$audits" />
</div>
