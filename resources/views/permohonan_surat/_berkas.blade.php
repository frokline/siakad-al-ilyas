<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Pilih</th>
                <th>Berkas</th>
                <th>Jenis / ukuran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($berkas as $b)
                <tr>
                    <td><input type="radio" id="berkas-{{ $b->id }}" name="{{ $field }}"
                            value="{{ $b->id }}" @checked((string) old($field) === (string) $b->id)
                            aria-label="Pilih {{ $b->label }}"></td>
                    <td><label for="berkas-{{ $b->id }}">{{ $b->label }}</label><a target="_blank"
                            rel="noopener noreferrer" href="{{ route('berkas.show', $b) }}">Periksa berkas
                            #{{ $b->id }}</a></td>
                    <td>{{ strtoupper($b->ekstensi) }} · {{ $b->ukuranLabel() }}</td>
                </tr>
            @empty<tr>
                    <td colspan="3">Belum ada berkas sesuai pencarian. Unggah melalui Berkas saya.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $berkas->links('permohonan_surat._pagination') }}
<p class="muted">Daftar menampilkan berkas milik Anda. Berkas yang sudah dipakai modul lain akan ditolak ketika
    disimpan. Pilih kembali setelah berpindah halaman.</p>
