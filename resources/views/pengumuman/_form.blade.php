@csrf
@php
    $teks = static fn($v) => is_scalar($v) ? (string) $v : '';
    $rows = old(
        'sasaran',
        $item->sasaran->map(fn($s) => $s->only(['lingkup', 'program_studi_id', 'kelas_kuliah_id', 'role_id']))->all(),
    );
    $rows = is_array($rows) ? array_values(array_slice($rows, 0, 10)) : [];
    $rows = array_pad($rows, 10, []);
@endphp
@if ($item->exists)
    <input type="hidden" name="versi" value="{{ $teks(old('versi', $item->versiForm())) }}">
@else<input type="hidden" name="form_token" value="{{ $teks(old('form_token', $token)) }}">
@endif
<label for="judul">Judul</label><input id="judul" name="judul" required minlength="3" maxlength="200"
    value="{{ $teks(old('judul', $item->judul)) }}">
<label for="isi">Isi pengumuman</label>
<textarea id="isi" name="isi" rows="10" required minlength="3" maxlength="20000">{{ $teks(old('isi', $item->isi)) }}</textarea>
<p class="muted">Teks biasa; HTML tidak dijalankan. Periksa isi sebelum terbit karena publikasi tidak dapat diedit.</p>
<label for="berakhir">Batas tayang (WITA, opsional)</label><input type="datetime-local" id="berakhir"
    name="berakhir_lokal"
    value="{{ $teks(old('berakhir_lokal', $item->berakhir_at?->setTimezone('Asia/Makassar')->format('Y-m-d\TH:i'))) }}">
<p>Kosong berarti tetap tayang sampai diarsipkan. Pilih 1–10 sasaran; pengumuman terlihat jika pembaca cocok dengan
    salah satu baris.</p>
@foreach ($rows as $i => $row)
    @php($row = is_array($row) ? $row : [])
    <fieldset class="peng-sasaran">
        <legend>Sasaran {{ $i + 1 }}</legend>
        <label for="lingkup-{{ $i }}">Lingkup</label><select id="lingkup-{{ $i }}"
            name="sasaran[{{ $i }}][lingkup]">
            <option value="">Tidak digunakan</option>
            @if ($admin)
                <option value="kampus" @selected($teks($row['lingkup'] ?? '') === 'kampus')>Seluruh kampus</option>
                <option value="prodi" @selected($teks($row['lingkup'] ?? '') === 'prodi')>Program studi</option>
            @endif
            <option value="kelas" @selected($teks($row['lingkup'] ?? '') === 'kelas')>Kelas kuliah</option>
        </select>
        @if ($admin)
            <label for="prodi-{{ $i }}">Prodi — hanya untuk lingkup prodi</label><select
                id="prodi-{{ $i }}" name="sasaran[{{ $i }}][program_studi_id]">
                <option value="">Kosong</option>
                @foreach ($prodi as $p)
                    <option value="{{ $p->id }}" @selected($teks($row['program_studi_id'] ?? '') === (string) $p->id)>
                        {{ $p->nama }}{{ $p->aktif ? '' : ' (nonaktif)' }}</option>
                @endforeach
            </select>
        @endif
        <label for="kelas-{{ $i }}">Kelas — hanya untuk lingkup kelas</label><select
            id="kelas-{{ $i }}" name="sasaran[{{ $i }}][kelas_kuliah_id]">
            <option value="">Kosong</option>
            @foreach ($kelas as $k)
                <option value="{{ $k->id }}" @selected($teks($row['kelas_kuliah_id'] ?? '') === (string) $k->id)>{{ $k->kode }} —
                    {{ $k->nama_mk_snapshot }} ({{ $k->status }})</option>
            @endforeach
        </select>
        <label for="role-{{ $i }}">Batasi peran</label><select id="role-{{ $i }}"
            name="sasaran[{{ $i }}][role_id]">
            <option value="">Semua peran yang sesuai lingkup</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected($teks($row['role_id'] ?? '') === (string) $role->id)>
                    {{ str_replace('_', ' ', $role->kode) }}</option>
            @endforeach
        </select>
    </fieldset>
@endforeach
<button type="submit">Simpan draf</button><a
    href="{{ $item->exists ? route('pengumuman.show', $item) : route('pengumuman.index', ['mode' => 'kelola']) }}">Batal</a>
