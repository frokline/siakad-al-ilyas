@extends('layouts.kalender_akademik')
@section('title', 'Kalender Akademik')
@section('content')
    <div class="heading">
        <div>
            <h1>Kalender akademik</h1>
            <p>Agenda kampus dan program studi · WITA</p>
        </div>
        @can('create', \App\Models\KalenderAkademik::class)
            <a class="button" href="{{ route('kalender.create') }}">Tambah agenda</a>
        @endcan
    </div>
    <form class="card kal-filter" method="get" action="{{ route('kalender.index') }}">
        <div><label for="bulan">Bulan</label><input type="month" id="bulan" name="bulan" min="2000-01"
                max="2100-12" value="{{ $filter['bulan'] }}" required></div>
        <div><label for="tampilan">Tampilan</label><select id="tampilan" name="tampilan">
                <option value="bulan" @selected($filter['tampilan'] === 'bulan')>Kalender bulanan</option>
                <option value="daftar" @selected($filter['tampilan'] === 'daftar')>Daftar agenda</option>
            </select></div>
        <div><label for="periode">Periode</label><select id="periode" name="periode_akademik_id">
                <option value="">Semua periode</option>
                @foreach ($periode as $p)
                    <option value="{{ $p->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $p->id)>{{ $p->kode }}</option>
                @endforeach
            </select>
        </div>
        <div><label for="prodi">Program studi</label><select id="prodi" name="program_studi_id">
                <option value="">Semua sasaran</option>
                @foreach ($prodi as $p)
                    <option value="{{ $p->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $p->id)>{{ $p->nama }} + kampus</option>
                @endforeach
            </select>
        </div>
        <div><label for="jenis">Jenis</label><select id="jenis" name="jenis">
                <option value="">Semua jenis</option>
                @foreach (\App\Models\KalenderAkademik::JENIS as $kode => $label)
                    <option value="{{ $kode }}" @selected(($filter['jenis'] ?? '') === $kode)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div><label for="status">Status</label><select id="status" name="status">
                <option value="">Semua yang dapat dilihat</option>
                @foreach (\App\Models\KalenderAkademik::STATUS as $kode => $label)
                    @if ($kelola || $kode !== 'draf')
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div><label for="q">Cari judul</label><input id="q" name="q" maxlength="100"
                value="{{ $filter['q'] ?? '' }}"></div>
        <div><label class="check"><input type="checkbox" name="kampus_saja" value="1" @checked(!empty($filter['kampus_saja']))>
                Hanya agenda seluruh kampus</label></div>
        <div class="actions"><button type="submit">Tampilkan</button><a href="{{ route('kalender.index') }}">Reset</a>
        </div>
    </form>
    <nav class="kal-nav" aria-label="Navigasi bulan">
        @if ($filter['bulan'] > '2000-01')
            <a
                href="{{ route('kalender.index', array_replace($filter, ['bulan' => $bulan->subMonth()->format('Y-m'), 'hari' => null, 'page' => null])) }}">←
                Bulan sebelumnya</a>
        @endif
        <strong>{{ $bulan->locale('id')->translatedFormat('F Y') }}</strong>
        @if ($filter['bulan'] < '2100-12')
            <a
                href="{{ route('kalender.index', array_replace($filter, ['bulan' => $bulan->addMonth()->format('Y-m'), 'hari' => null, 'page' => null])) }}">Bulan
                berikutnya →</a>
        @endif
    </nav>
    @if ($lebih)
        <div class="notice">Agenda terlalu banyak untuk satu kalender. Gunakan filter atau <a
                href="{{ route('kalender.index', array_replace($filter, ['tampilan' => 'daftar', 'page' => null])) }}">buka
                daftar lengkap</a>.</div>
    @elseif($filter['tampilan'] === 'bulan')
        <div class="kal-scroll" tabindex="0" role="region" aria-label="Kalender bulanan; dapat digeser pada layar kecil">
            <table class="kal-grid">
                <caption>{{ $bulan->locale('id')->translatedFormat('F Y') }} · WITA</caption>
                <thead>
                    <tr>
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                            <th scope="col">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($minggu as $pekan)
                        <tr>
                            @foreach ($pekan as $hari)
                                <td class="{{ $hari['bulan_ini'] ? '' : 'kal-luar' }}">
                                    <time
                                        datetime="{{ $hari['tanggal']->format('Y-m-d') }}">{{ $hari['tanggal']->day }}</time>
                                    @if ($hari['bulan_ini'])
                                        @foreach ($hari['agenda']->take(4) as $a)
                                            <a class="kal-item {{ $a->status === 'batal' ? 'kal-batal' : '' }}"
                                                href="{{ route('kalender.show', $a) }}">
                                                <strong>{{ $a->judul }}</strong><small>{{ \App\Models\KalenderAkademik::STATUS[$a->status] }}
                                                    · {{ \App\Models\KalenderAkademik::JENIS[$a->jenis] }}</small></a>
                                        @endforeach
                                        @if ($hari['agenda']->count() > 4)
                                            <a
                                                href="{{ route('kalender.index', array_replace($filter, ['tampilan' => 'daftar', 'hari' => $hari['tanggal']->format('Y-m-d'), 'page' => null])) }}">Lihat
                                                {{ $hari['agenda']->count() }} agenda hari ini</a>
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="muted">Agenda beberapa hari tampil pada tiap hari yang dicakupnya. Klik judul untuk jam dan detail.
            Penanda “Dibatalkan” berarti agenda tidak berlaku.</p>
    @else
        @if (!empty($filter['hari']))
            <p class="notice">Daftar untuk {{ $filter['hari'] }}. <a
                    href="{{ route('kalender.index', array_replace($filter, ['hari' => null, 'page' => null])) }}">Lihat
                    seluruh bulan</a></p>
        @endif
        <section class="card table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Agenda</th>
                        <th>Waktu WITA</th>
                        <th>Sasaran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftar as $a)
                        <tr>
                            <td><a
                                    href="{{ route('kalender.show', $a) }}">{{ $a->judul }}</a><br>{{ \App\Models\KalenderAkademik::JENIS[$a->jenis] }}
                                · {{ $a->periodeAkademik->kode }}</td>
                            <td>{{ $a->mulai_at->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('d-m-Y H:i') }}<br>sampai
                                {{ $a->selesai_at->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('d-m-Y H:i') }}
                            </td>
                            <td>{{ $a->programStudi?->nama ?? 'Seluruh kampus' }}</td>
                            <td>{{ \App\Models\KalenderAkademik::STATUS[$a->status] }}</td>
                        </tr>
                    @empty<tr>
                            <td colspan="4">Tidak ada agenda sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>{{ $daftar->links('kalender_akademik._pagination') }}
        </section>
    @endif
@endsection
