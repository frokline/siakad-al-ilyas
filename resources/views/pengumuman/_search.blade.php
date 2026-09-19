<form class="card" method="get"
    action="{{ $item->exists ? route('pengumuman.edit', $item) : route('pengumuman.create') }}">
    <label for="q-kelas">Cari pilihan kelas terlebih dahulu</label><input id="q-kelas" name="q_kelas" maxlength="100"
        value="{{ $qKelas }}"><button type="submit">Cari kelas</button>
    <p class="muted">Pencarian memuat ulang halaman. Lakukan sebelum mengisi draf. Pilihan dibatasi 100 hasil ditambah
        sasaran tersimpan; persempit pencarian bila kelas belum muncul.</p>
    @if ($lebihKelas)
        <p role="status">Hasil lebih dari 100 kelas. Masukkan kode kelas yang lebih spesifik.</p>
    @endif
</form>
