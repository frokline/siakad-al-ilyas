@extends('layouts.admin')

@section('title', 'Permohonan Surat')

@section('content')
    @php
        $zona = (string) config('siakad.timezone', 'Asia/Makassar');
        $tgl = fn($waktu) => $waktu ? $waktu->setTimezone($zona)->format('d-m-Y H:i') : '—';
        $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
        $kepalaKartu = 'border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
        $judulKartu = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
        // Kolom Mahasiswa hanya berguna bila daftar memuat permohonan milik orang lain (petugas).
        $tampilPemohon = !$daftar->every(fn($baris) => (int) $baris->pemohon_id === (int) auth()->id());
        $jumlahKolom = $tampilPemohon ? 6 : 5;
    @endphp

    @include('permohonan_surat._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs text-slate-500">Layanan &rsaquo; Permohonan surat</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Permohonan surat</h1>
            <p class="mt-1 text-sm text-slate-500">Ajukan surat dan pantau prosesnya sampai surat terbit.</p>
        </div>

        @can('create', \App\Models\PermohonanSurat::class)
            <a href="{{ route('surat.create') }}"
                class="inline-flex items-center justify-center self-start rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
                <span class="mr-1.5" aria-hidden="true">+</span> Ajukan surat
            </a>
        @endcan
    </div>

    <form method="get" action="{{ route('surat.index') }}"
        class="{{ $kartu }} mb-6 grid grid-cols-1 items-end gap-4 p-5 sm:grid-cols-12">
        <div class="sm:col-span-5">
            <label for="q" class="{{ $label }}">Nomor pengajuan / nomor surat</label>
            <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                class="{{ $input }}">
        </div>
        <div class="sm:col-span-4">
            <label for="status" class="{{ $label }}">Status</label>
            <select id="status" name="status" class="{{ $input }}">
                <option value="">Semua</option>
                @foreach (\App\Models\PermohonanSurat::STATUS as $kode => $teks)
                    <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $teks }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 sm:col-span-3">
            <button type="submit"
                class="flex-1 rounded-xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-700">Tampilkan</button>
            <a href="{{ route('surat.index') }}"
                class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <section class="{{ $kartu }} mb-8 w-full overflow-hidden">
        <div class="{{ $kepalaKartu }} flex items-center justify-between">
            <h2 class="{{ $judulKartu }}">Daftar permohonan</h2>
            <span
                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">{{ number_format($daftar->total(), 0, ',', '.') }}
                data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4">Pengajuan</th>
                        @if ($tampilPemohon)
                            <th scope="col" class="px-6 py-4">Mahasiswa</th>
                        @endif
                        <th scope="col" class="px-6 py-4">Layanan</th>
                        <th scope="col" class="px-6 py-4">Diajukan</th>
                        <th scope="col" class="px-6 py-4 text-center">Status</th>
                        <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftar as $p)
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <span
                                    class="block font-mono text-xs font-bold text-slate-800">{{ $p->nomor_pengajuan }}</span>
                                @if ($p->nomor_surat)
                                    <span class="mt-0.5 block text-xs text-emerald-700">No. surat:
                                        {{ $p->nomor_surat }}</span>
                                @endif
                            </td>
                            @if ($tampilPemohon)
                                <td class="px-6 py-4 text-slate-700">
                                    <span class="block font-medium">{{ $p->akademik_snapshot['nama'] ?? '—' }}</span>
                                    <span
                                        class="font-mono text-xs text-slate-500">{{ $p->akademik_snapshot['nim'] ?? '—' }}</span>
                                </td>
                            @endif
                            <td class="px-6 py-4 font-medium text-slate-700">{{ $p->jenis_snapshot['nama'] ?? '—' }}</td>
                            <td class="px-6 py-4 text-xs text-slate-700">{{ $tgl($p->diajukan_at) }}</td>
                            <td class="px-6 py-4 text-center">
                                @include('permohonan_surat._lencana', ['status' => $p->status])
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('surat.show', $p) }}"
                                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 hover:text-siakad-dark">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $jumlahKolom }}" class="px-6 py-14 text-center text-slate-500">
                                <p class="text-sm font-bold text-slate-700">Belum ada permohonan sesuai filter.</p>
                                @can('create', \App\Models\PermohonanSurat::class)
                                    <p class="mt-1 text-xs">Butuh surat? Ajukan melalui tombol <strong>Ajukan surat</strong> di
                                        atas.</p>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $daftar->links('permohonan_surat._pagination') }}
    </section>
@endsection
