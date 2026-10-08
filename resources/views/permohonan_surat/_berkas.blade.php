{{-- Parameter: $berkas (paginator), $field (nama field radio). Dipakai di create (mahasiswa) dan show (petugas). --}}
<div class="overflow-hidden rounded-xl border border-slate-200">
    <ul class="divide-y divide-slate-100">
        @forelse($berkas as $b)
            <li class="flex items-center gap-4 px-4 py-3 transition-colors hover:bg-slate-50">
                <input type="radio" id="berkas-{{ $b->id }}" name="{{ $field }}" value="{{ $b->id }}"
                    @checked((string) old($field) === (string) $b->id) aria-label="Pilih {{ $b->label }}"
                    class="h-4 w-4 shrink-0 border-slate-300 text-siakad-dark focus:ring-siakad-dark">
                <label for="berkas-{{ $b->id }}" class="min-w-0 flex-1 cursor-pointer">
                    <span class="block truncate text-sm font-semibold text-slate-800">{{ $b->label }}</span>
                    <span class="text-xs text-slate-500">{{ strtoupper($b->ekstensi) }} &middot;
                        {{ $b->ukuranLabel() }}</span>
                </label>
                <a target="_blank" rel="noopener noreferrer" href="{{ route('berkas.show', $b) }}"
                    class="shrink-0 text-xs font-semibold text-siakad-dark hover:underline">Periksa berkas
                    #{{ $b->id }}</a>
            </li>
        @empty
            <li class="px-4 py-8 text-center text-sm text-slate-500">
                Belum ada berkas sesuai pencarian. Unggah melalui
                <a href="{{ route('berkas.create') }}" target="_blank" rel="noopener noreferrer"
                    class="font-semibold text-siakad-dark hover:underline">Berkas saya</a>.
            </li>
        @endforelse
    </ul>
    {{ $berkas->links('permohonan_surat._pagination') }}
</div>
<p class="mt-2 text-xs text-slate-500">Daftar menampilkan berkas milik Anda. Berkas yang sudah dipakai modul lain akan
    ditolak ketika disimpan. Pilih kembali setelah berpindah halaman.</p>
