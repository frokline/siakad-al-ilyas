<dl class="detail-grid">
    <div>
        <dt>Mahasiswa</dt>
        <dd>
            <a href="{{ route('admin.mahasiswa.show', $registrasi->riwayatStudi->mahasiswa) }}">
                {{ $registrasi->riwayatStudi->mahasiswa->nim }}
            </a>
            <br>{{ $registrasi->riwayatStudi->mahasiswa->user->nama }}
        </dd>
    </div>
    <div>
        <dt>Program studi</dt>
        <dd>{{ $registrasi->riwayatStudi->kurikulum->programStudi->nama }}</dd>
    </div>
    <div>
        <dt>Periode akademik</dt>
        <dd>{{ $registrasi->periodeAkademik->kode }}</dd>
    </div>
    <div>
        <dt>Semester studi</dt>
        <dd>{{ $registrasi->semester_studi }}</dd>
    </div>
    <div>
        <dt>Rombel</dt>
        <dd><a href="{{ route('admin.rombel.show', $registrasi->rombel) }}">{{ $registrasi->rombel->kode }}</a></dd>
    </div>
    <div>
        <dt>Paket semester</dt>
        <dd>
            <a href="{{ route('admin.paket-semester.show', $registrasi->rombel->paketSemester) }}">
                {{ $registrasi->rombel->paketSemester->nama }}
            </a>
            — versi {{ $registrasi->rombel->paketSemester->versi }}
        </dd>
    </div>
    <div>
        <dt>Registrasi semester</dt>
        <dd>
            <a href="{{ route('admin.registrasi-semester.show', $registrasi) }}">
                #{{ $registrasi->id }} — {{ \App\Models\RegistrasiSemester::STATUS[$registrasi->status] }}
            </a>
        </dd>
    </div>
    <div>
        <dt>Jadwal pengisian KRS</dt>
        <dd>
            @if ($registrasi->periodeAkademik->krs_mulai && $registrasi->periodeAkademik->krs_selesai)
                {{ $registrasi->periodeAkademik->krs_mulai->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                s.d.
                {{ $registrasi->periodeAkademik->krs_selesai->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                <br><small>{{ config('siakad.timezone', 'Asia/Makassar') }}</small>
            @else
                Belum ditetapkan
            @endif
        </dd>
    </div>
</dl>
