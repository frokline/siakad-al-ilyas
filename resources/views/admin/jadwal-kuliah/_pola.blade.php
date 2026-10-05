<div class="overflow-x-auto rounded-b-xl">
    <table class="w-full text-left text-sm text-slate-600 border-collapse">
        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th scope="col" class="px-6 py-4 font-semibold">Hari / Jam</th>
                <th scope="col" class="px-6 py-4 font-semibold">Berlaku</th>
                <th scope="col" class="px-6 py-4 font-semibold">Metode / Lokasi</th>
                <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($kelas->jadwalKuliah->sortBy(fn ($row) => sprintf('%d-%s-%020d', $row->hari, $row->jam_mulai, $row->id)) as $pola)
                <tr
                    class="hover:bg-slate-50/80 transition-colors group @if (isset($jadwal) && $jadwal->exists && $jadwal->id === $pola->id) bg-amber-50/40 @endif">
                    <td class="px-6 py-4">
                        <strong
                            class="font-bold text-slate-800 block">{{ \App\Models\JadwalKuliah::HARI[$pola->hari] }}</strong>
                        <span
                            class="text-xs text-slate-500 font-mono mt-0.5 block">{{ substr($pola->jam_mulai, 0, 5) }}–{{ substr($pola->jam_selesai, 0, 5) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700">
                        {{ $pola->berlaku_mulai->format('d-m-Y') }} <br>
                        <span class="text-xs text-slate-400 mt-0.5 inline-block">s.d.
                            {{ $pola->berlaku_selesai->format('d-m-Y') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-700">{{ \App\Models\JadwalKuliah::METODE[$pola->metode] }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">{{ $pola->lokasi ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($pola->aktif)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-bold text-slate-500">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.jadwal-kuliah.show', $pola) }}"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                            Detail #{{ $pola->id }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        Belum ada pola jadwal yang tersusun untuk kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
