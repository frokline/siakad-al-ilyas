@php
    $koordinatorTim = $kelas->pengajarKelas->first(fn($row) => $row->isKoordinatorAktif());
@endphp
<div class="bg-slate-50/50 p-6 border-b border-slate-200">
    @if ($koordinatorTim)
        <div class="flex items-center gap-2 text-sm text-slate-700">
            <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Koordinator saat ini: <strong class="text-slate-900">{{ $koordinatorTim->dosen->user->nama }}</strong>
                (<span class="font-mono font-bold text-xs">{{ $koordinatorTim->dosen->kode_dosen }}</span>)</span>
        </div>
    @else
        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
            Belum ada koordinator aktif. Tetapkan satu dosen sebagai koordinator untuk kelas ini.
        </div>
    @endif
    <p class="text-xs text-slate-500 mt-2">Penugasan lama tetap ditampilkan. Gunakan penugasan tersebut untuk
        mengaktifkan kembali dosen yang sama.</p>
</div>

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-slate-600 border-collapse">
        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th scope="col" class="px-6 py-4 font-semibold">Dosen</th>
                <th scope="col" class="px-6 py-4 font-semibold">Peran</th>
                <th scope="col" class="px-6 py-4 font-semibold">Penugasan</th>
                <th scope="col" class="px-6 py-4 font-semibold">Profil dan Akun</th>
                <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($kelas->pengajarKelas->sortBy('id') as $anggota)
                @php
                    $profilSiap =
                        $anggota->dosen->status === \App\Models\Dosen::AKTIF &&
                        $anggota->dosen->user->isAktif() &&
                        $anggota->dosen->user->roles->contains('kode', \App\Models\Role::DOSEN);
                @endphp
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.dosen.show', $anggota->dosen) }}"
                            class="font-mono font-bold text-siakad-dark hover:underline text-xs block">{{ $anggota->dosen->kode_dosen }}</a>
                        <strong class="font-bold text-slate-800 block mt-0.5">{{ $anggota->dosen->user->nama }}</strong>
                    </td>
                    <td class="px-6 py-4">
                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $anggota->peran === 'koordinator' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                            {{ \App\Models\PengajarKelas::PERAN[$anggota->peran] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $anggota->aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100' }}">
                            <span
                                class="h-1.5 w-1.5 rounded-full {{ $anggota->aktif ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $anggota->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if ($profilSiap)
                            <span
                                class="text-emerald-700 font-semibold text-xs inline-flex items-center gap-1">Siap</span>
                        @else
                            <span class="text-rose-600 text-xs font-medium">Periksa status dosen, akun, dan role</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                            href="{{ route('admin.pengajar-kelas.show', $anggota) }}">Detail #{{ $anggota->id }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        Belum ada dosen yang ditugaskan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
