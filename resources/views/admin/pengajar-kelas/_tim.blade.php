@php
    $koordinatorTim = $kelas->pengajarKelas->first(fn($row) => $row->isKoordinatorAktif());
@endphp
<div class="panel-body">
    @if ($koordinatorTim)
        <p><strong>Koordinator saat ini:</strong> {{ $koordinatorTim->dosen->user->nama }}
            ({{ $koordinatorTim->dosen->kode_dosen }})</p>
    @else
        <p class="pengajar-note">Belum ada koordinator aktif. Tetapkan satu dosen sebagai koordinator untuk kelas ini.
        </p>
    @endif
    <p class="help">Penugasan lama tetap ditampilkan. Gunakan penugasan tersebut untuk mengaktifkan kembali dosen yang
        sama.</p>
</div>
<div class="table-wrap">
    <table class="pengajar-table">
        <thead>
            <tr>
                <th scope="col">Dosen</th>
                <th scope="col">Peran</th>
                <th scope="col">Penugasan</th>
                <th scope="col">Profil dan akun</th>
                <th scope="col">Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kelas->pengajarKelas->sortBy('id') as $anggota)
                @php
                    $profilSiap =
                        $anggota->dosen->status === \App\Models\Dosen::AKTIF &&
                        $anggota->dosen->user->isAktif() &&
                        $anggota->dosen->user->roles->contains('kode', \App\Models\Role::DOSEN);
                @endphp
                <tr>
                    <td>
                        <a
                            href="{{ route('admin.dosen.show', $anggota->dosen) }}">{{ $anggota->dosen->kode_dosen }}</a><br>
                        {{ $anggota->dosen->user->nama }}
                    </td>
                    <td><span class="badge"
                            data-pengajar-peran="{{ $anggota->peran }}">{{ \App\Models\PengajarKelas::PERAN[$anggota->peran] }}</span>
                    </td>
                    <td><span class="badge"
                            data-pengajar-aktif="{{ $anggota->aktif ? '1' : '0' }}">{{ $anggota->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td>{{ $profilSiap ? 'Siap' : 'Periksa status dosen, akun, dan role' }}</td>
                    <td>
                        <a class="button small secondary"
                            href="{{ route('admin.pengajar-kelas.show', $anggota) }}">Detail #{{ $anggota->id }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada dosen yang ditugaskan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
