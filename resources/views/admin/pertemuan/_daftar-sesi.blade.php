<div class="overflow-x-auto rounded-b-xl">
    <table class="w-full text-left text-sm text-slate-600 border-collapse">
        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th scope="col" class="px-6 py-4 font-semibold w-16">No.</th>
                <th scope="col" class="px-6 py-4 font-semibold">Rencana</th>
                <th scope="col" class="px-6 py-4 font-semibold">Jenis / Topik</th>
                <th scope="col" class="px-6 py-4 font-semibold">Penanggung Jawab</th>
                <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($kelas->pertemuan->sortBy('nomor') as $row)
                <tr
                    class="hover:bg-slate-50/80 transition-colors @if (isset($sesi) && $sesi->exists && $sesi->id === $row->id) bg-amber-50/40 @endif">
                    <td class="px-6 py-4 font-bold text-center text-slate-800">{{ $row->nomor }}</td>
                    <td class="px-6 py-4">
                        <strong
                            class="font-bold text-slate-800 block text-xs">{{ $row->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y') }}</strong>
                        <span class="text-xs font-mono text-slate-500 block mt-0.5">
                            {{ $row->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}–{{ $row->selesai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-800">
                            {{ \App\Models\Pertemuan::JENIS[$row->jenis] ?? $row->jenis }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $row->topik }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-700">{{ $row->pengajar_snapshot['nama'] ?? '—' }}</div>
                        <div class="text-xs font-mono text-slate-400 mt-0.5">
                            {{ $row->pengajar_snapshot['kode_dosen'] ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                            $statusBadgeClass = match ($row->status) {
                                'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'berlangsung' => 'bg-blue-50 text-blue-700 border-blue-100',
                                'terjadwal' => 'bg-amber-50 text-amber-700 border-amber-100',
                                default => 'bg-rose-50 text-rose-700 border-rose-100',
                            };
                            $dotClass = match ($row->status) {
                                'selesai' => 'bg-emerald-500',
                                'berlangsung' => 'bg-blue-500',
                                'terjadwal' => 'bg-amber-500',
                                default => 'bg-rose-500',
                            };
                        @endphp
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                            {{ \App\Models\Pertemuan::STATUS[$row->status] ?? $row->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.pertemuan.show', $row) }}"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        Belum ada daftar pertemuan untuk kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
