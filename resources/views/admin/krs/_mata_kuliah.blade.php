@php
    $untukCetak = $untukCetak ?? false;

    // Logika pewarnaan dipindah ke atas agar tidak memicu bug Blade Compiler di dalam loop
    $warnaKelas = [
        'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'persiapan' => 'bg-amber-50 text-amber-700 border-amber-100',
        'selesai' => 'bg-blue-50 text-blue-700 border-blue-100',
    ];

    $warnaIkut = [
        'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'draf' => 'bg-slate-100 text-slate-600 border-slate-200',
        'batal' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
@endphp

<div class="overflow-x-auto {{ !$untukCetak ? 'border-t border-slate-200' : '' }}">
    <table class="w-full text-left text-sm text-slate-600 border-collapse">
        @if ($untukCetak)
            <caption class="text-left font-bold text-slate-800 mb-2 uppercase tracking-wide">Mata Kuliah dalam Paket
                Semester</caption>
        @endif
        <thead
            class="{{ $untukCetak ? 'border-y border-slate-800 text-slate-800' : 'border-b border-slate-200 bg-slate-50 text-slate-500' }} text-xs uppercase">
            <tr>
                <th scope="col" class="px-6 py-4 font-semibold w-16">No.</th>
                <th scope="col" class="px-6 py-4 font-semibold">Kode Kelas</th>
                <th scope="col" class="px-6 py-4 font-semibold">Mata Kuliah</th>
                <th scope="col" class="px-6 py-4 font-semibold text-center">SKS</th>
                @unless ($untukCetak)
                    <th scope="col" class="px-6 py-4 font-semibold text-center">Status Kelas</th>
                    <th scope="col" class="px-6 py-4 font-semibold text-center">Keikutsertaan</th>
                @endunless
            </tr>
        </thead>
        <tbody class="divide-y {{ $untukCetak ? 'divide-slate-300' : 'divide-slate-100' }}">
            @forelse ($krs->details->sortBy('id') as $detail)
                <tr class="{{ !$untukCetak ? 'hover:bg-slate-50/80 transition-colors' : '' }}">
                    <td class="px-6 py-4 text-center text-xs font-medium">{{ $loop->iteration }}</td>
                    <td
                        class="px-6 py-4 font-mono font-bold {{ !$untukCetak ? 'text-siakad-dark' : 'text-slate-800' }} text-xs">
                        @if ($untukCetak)
                            {{ $detail->kelasKuliah->kode }}
                        @else
                            <a href="{{ route('admin.kelas-kuliah.show', $detail->kelasKuliah) }}"
                                class="hover:underline">
                                {{ $detail->kelasKuliah->kode }}
                            </a>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-medium {{ $untukCetak ? 'text-slate-800' : 'text-slate-700' }}">
                        {{ $detail->kelasKuliah->nama_mk_snapshot }}</td>
                    <td
                        class="px-6 py-4 text-center font-bold {{ $untukCetak ? 'text-slate-800' : 'text-slate-700' }}">
                        {{ str_replace('.', ',', $detail->kelasKuliah->sks_snapshot) }}</td>
                    @unless ($untukCetak)
                        <td class="px-6 py-4 text-center">
                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $warnaKelas[$detail->kelasKuliah->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ \App\Models\KelasKuliah::STATUS[$detail->kelasKuliah->status] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="inline-flex items-center rounded border px-2 py-1 text-xs font-bold {{ $warnaIkut[$detail->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ \App\Models\DetailKrs::STATUS[$detail->status] }}
                            </span>
                        </td>
                    @endunless
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $untukCetak ? 4 : 6 }}" class="px-6 py-8 text-center text-slate-500 italic">
                        Detail mata kuliah belum tersedia.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot
            class="{{ $untukCetak ? 'border-y border-slate-800 text-slate-800' : 'bg-slate-50/50 border-t border-slate-200 text-slate-700' }}">
            <tr>
                <th scope="row" colspan="3"
                    class="px-6 py-4 text-right font-bold text-sm uppercase tracking-wider">
                    Total {{ $krs->details->count() }} mata kuliah
                </th>
                <td class="px-6 py-4 text-center font-black text-base">
                    {{ str_replace('.', ',', $krs->totalSks()) }}
                </td>
                @unless ($untukCetak)
                    <td colspan="2" class="px-6 py-4 text-xs font-medium text-slate-500">
                        Seluruh paket semester
                    </td>
                @endunless
            </tr>
        </tfoot>
    </table>
</div>
