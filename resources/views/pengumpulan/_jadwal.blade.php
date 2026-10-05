@php
    $sekarang = now('UTC');
    $belumDibuka = $sekarang->lt($kegiatan->buka_at);
    $tenggatLewat = $sekarang->gte($kegiatan->tenggat_at);
    $ditutup = $kegiatan->status !== \App\Models\Kegiatan::TERBIT;
@endphp

<section class="card">
    <h2>{{ $kegiatan->judul }}</h2>

    <p>
        {{ $kegiatan->kelasKuliah->kode }}
        — {{ $kegiatan->kelasKuliah->nama_mk_snapshot }}
    </p>

    <dl class="metadata">
        <div>
            <dt>Mulai dikerjakan</dt>

            <dd>
                {{ $kegiatan->buka_at
                    ->setTimezone($zona)
                    ->format('d-m-Y H:i') }}
                ({{ $zona }})
            </dd>
        </div>

        <div>
            <dt>Batas pengumpulan</dt>

            <dd>
                {{ $kegiatan->tenggat_at
                    ->setTimezone($zona)
                    ->format('d-m-Y H:i') }}
                ({{ $zona }})
            </dd>
        </div>

        <div>
            <dt>Format berkas</dt>

            <dd>
                {{ strtoupper(
                    implode(', ', $kegiatan->ekstensi_diizinkan)
                ) }}
            </dd>
        </div>

        <div>
            <dt>Batas lampiran</dt>

            <dd>
                Maksimal {{ $kegiatan->maks_berkas }} berkas,
                {{ (int) ($kegiatan->maks_ukuran_byte / 1048576) }}
                MB per berkas.
            </dd>
        </div>
    </dl>

    @if ($belumDibuka)
        <p class="notice">
            Tugas belum dibuka. Anda dapat mulai mengerjakan pada waktu
            yang tercantum di atas.
        </p>
    @elseif ($ditutup)
        <p class="notice error">
            Tugas sudah ditutup oleh pengajar.
        </p>
    @elseif ($tenggatLewat)
        <p class="notice error">
            Batas pengumpulan telah berakhir. Jawaban tidak dapat diubah
            atau dikumpulkan lagi.
        </p>
    @else
        <p class="notice">
            Tugas masih dapat dikerjakan dan dikumpulkan sebelum batas
            waktu berakhir.
        </p>
    @endif

    @can('view', $kegiatan)
        <a href="{{ route('kegiatan.show', $kegiatan) }}">
            Baca instruksi tugas
        </a>
    @endcan
</section>