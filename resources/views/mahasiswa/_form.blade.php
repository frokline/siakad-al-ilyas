@php
    $mengedit = $mahasiswa->exists;
@endphp

<div class="space-y-6">
    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
        <strong class="font-bold text-slate-800 text-base block">{{ $akun->nama }}</strong>
        <span class="text-xs text-slate-500 block mt-0.5">{{ $akun->email }}</span>
        <p class="text-xs text-slate-400 mt-2">
            Nama, email, dan status akun dikelola melalui menu Pengguna.
        </p>
    </div>

    @error('user_id')
        <div class="rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" role="alert">
            {{ $message }}
        </div>
    @enderror

    @include('mahasiswa._identitas_fields')

    <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
        <a class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
            href="{{ $mengedit ? route('admin.mahasiswa.show', $mahasiswa) : route('admin.mahasiswa.index') }}">
            Batal
        </a>
        <button type="submit" @disabled($errors->has('versi'))
            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 disabled:opacity-50">
            {{ $mengedit ? 'Simpan Perubahan' : 'Simpan Mahasiswa' }}
        </button>
    </div>
</div>
