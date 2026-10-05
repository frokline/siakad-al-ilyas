@extends('layouts.admin')

@section('title', 'Pilih Kelas')

@section('content')
    @include('kegiatan._pesan')

    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Pilih kelas</h1>
        <p class="mt-1 text-sm text-slate-500">
            Dosen hanya melihat kelas penugasan aktifnya. Pembelajaran baru memerlukan periode yang aktif.
        </p>
    </div>

    <form method="get" action="{{ route('kegiatan.kelas') }}"
        class="mb-6 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="mb-1 block text-xs font-semibold text-slate-600">Kode kelas atau mata kuliah</label>
            <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-siakad-active focus:outline-none focus:ring-1 focus:ring-siakad-active">
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="rounded-lg bg-siakad-active px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Cari</button>
            <a href="{{ route('kegiatan.index') }}"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Mata kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar as $kelas)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4 font-mono font-semibold text-slate-800">{{ $kelas->kode }}</td>
                            <td class="px-6 py-4">{{ $kelas->nama_mk_snapshot }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-slate-600">{{ $kelas->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('kegiatan.index', ['kelas' => $kelas->id]) }}"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat</a>
                                    @can('create', [\App\Models\Kegiatan::class, $kelas])
                                        <a href="{{ route('kegiatan.create', ['kelas' => $kelas->id]) }}"
                                            class="rounded-lg bg-siakad-active px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">Bagikan
                                            baru</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center italic text-slate-500">Belum ada kelas yang
                                dapat dikelola.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $daftar->links('kegiatan._pagination') }}
@endsection
