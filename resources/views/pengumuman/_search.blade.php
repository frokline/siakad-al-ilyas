<form method="get" action="{{ $item->exists ? route('pengumuman.edit', $item) : route('pengumuman.create') }}"
    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Cari pilihan kelas</h2>
    <p class="mt-2 text-xs text-slate-500">Lakukan pencarian ini <strong>sebelum</strong> mengisi draf. Halaman dimuat
        ulang, sehingga isian yang belum disimpan akan hilang.</p>
    <div class="mt-3 flex gap-2">
        <input id="q-kelas" name="q_kelas" maxlength="100" value="{{ $qKelas }}"
            placeholder="Kode atau nama mata kuliah" aria-label="Cari kelas"
            class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20">
        <button type="submit"
            class="rounded-xl bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Cari</button>
    </div>
    <p class="mt-3 text-xs text-slate-500">Pilihan dibatasi 100 hasil ditambah sasaran yang sudah tersimpan.</p>
    @if ($lebihKelas)
        <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800" role="status">Hasil lebih dari 100
            kelas. Masukkan kode kelas yang lebih spesifik.</p>
    @endif
</form>
